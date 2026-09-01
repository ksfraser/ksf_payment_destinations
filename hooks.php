<?php
define ('SS_ksf_payment_destinations', 111<<8);

/**
 * Hooks adds menus and intercepts FA transactions.
 * db_prewrite: routes invoice payments to mapped bank accounts.
 *
 * @BABOK Related: FR-PD-001-003-payment-redirect.md
 */
class hooks_ksf_payment_destinations extends hooks {
    var $module_name = 'ksf_payment_destinations';

    /***************************************************************************************//**
     * Register menu items under GL and orders app.
     * @BABOK Related: UC-PD-001-configure-destinations.md, UC-PD-002-add-payment-mapping.md
     * ***********************************************************************************/
    function install_options($app) {
        global $Ajax;
        switch($app->id) {
            case 'GL':
                $app->add_rapp_function(2, _('ksf_payment_destinations'),
                     'modules/ksf_payment_destinations/ksf_payment_destinations.php', 'SA_ksf_payment_destinations');
                $Ajax->addReplace($app->id, 'rsvr_function', 'rapp_function');
                break;
            case 'orders':
                $app->add_rapp_function(2, _('ksf_payment_destinations'),
                     'modules/ksf_payment_destinations/ksf_payment_destinations.php', 'SA_ksf_payment_destinations');
                break;
        }
    }

    /***************************************************************************************//**
     * Register security area and section.
     * @BABOK Related: UC-PD-001-configure-destinations.md
     * ***********************************************************************************/
    function install_access()
    {
        $security_sections[SS_ksf_payment_destinations] = _("ksf_payment_destinations");
        $security_areas['SA_ksf_payment_destinations'] = array(SS_ksf_payment_destinations|101, _("ksf_payment_destinations"));
        return array($security_areas, $security_sections);
    }

    /***************************************************************************************//**
     * Intercept ST_SALESINVOICE, route payment to mapped bank account.
     *
     * NEW PSR-4 implementation:
     *   PaymentDestinationService (DI wired: Repository + QueryBuilder)
     *
     * LEGACY (commented — for reference during transition):
     *   require_once 'class.ksf_payment_destinations_model.php';
     *   $pay = new ksf_payment_destinations_model(ksf_payment_destinations_PREFS, $this);
     *   $pay->set_var("payment_term", $cart->payment_terms['terms_indicator']);
     *   $pay->select_row();
     *   $cart->pos['pos_account'] = $pay->get("bank_account");
     *   if (!$cart->payment_terms['cash_sale']) { $cart->payment_terms['cash_sale'] = 1; }
     *
     * @BABOK Related: BR-PD-001-payment-routing.md, BR-PD-002-cash-sale-redirect.md, FR-PD-001-003-payment-redirect.md
     * ***********************************************************************************/
    function db_prewrite(&$cart, $trans_type)
    {
        if ($trans_type !== ST_SALESINVOICE) {
            return false;
        }

        $term = (int) ($cart->payment_terms['terms_indicator'] ?? 0);
        if ($term <= 0) {
            return true;
        }

        // -- PSR-4 implementation (requires vendor/autoload.php) --
        $autoload = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;

            $tableName = TB_PREF . 'ksf_payment_destinations';
            $qb        = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
            $repo      = new \ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository($qb, $tableName);
            $service   = new \ksfraser\PaymentDestinations\Service\PaymentDestinationService($repo);

            $bankAccount = $service->getBankAccountFromTerm($term);
            if ($bankAccount > 0) {
                $cart->pos['pos_account'] = $bankAccount;
                if (!($cart->payment_terms['cash_sale'] ?? false)) {
                    $cart->payment_terms['cash_sale'] = 1;
                }
            }
            return true;
        }

        // -- LEGACY implementation (no Composer required) --
        if (!class_exists('ksf_payment_destinations_model', false)) {
            require_once __DIR__ . '/class.ksf_payment_destinations_model.php';
        }
        $pay = new ksf_payment_destinations_model(ksf_payment_destinations_PREFS, $this);
        $pay->set_var("payment_term", $term);
        try {
            $pay->select_row();
            $cart->pos['pos_account'] = $pay->get("bank_account");
        } catch (Exception $e) {
            if (KSF_FIELD_NOT_SET == $e->getCode()) {
                if (strpos($e->getMessage(), 'bank_account') !== false) {
                    return true;
                }
            } else {
                display_error(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());
            }
        }
        if (!($cart->payment_terms['cash_sale'] ?? false)) {
            $cart->payment_terms['cash_sale'] = 1;
        }
        return true;
    }
}
