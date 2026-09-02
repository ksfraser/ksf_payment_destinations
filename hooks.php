<?php
define ('SS_ksf_payment_destinations', 111<<8);
define ('SA_ksf_payment_destinations', 111<<8);

/**
 * Hooks adds menus and intercepts FA transactions.
 * db_prewrite: routes invoice payments to mapped bank accounts.
 *
 * @BABOK Related: FR-PD-001-003-payment-redirect.md
 */
class hooks_ksf_payment_destinations extends hooks {
    var $module_name = 'ksf_payment_destinations';
    var $version     = '2.4.3-1';

    /**
     * Standard inter-module communication — constants.
     * @BABOK Related: FR-PD-004-001-inter-module-communication.md
     */
    public function getModuleConstants(&$data, $opts = null)
    {
        $constants = [
            'KSF_PAYMENT_DESTINATIONS_MODULE' => $this->module_name,
        ];
        $data['constants'] = $constants;
        return $constants;
    }

    /**
     * Standard inter-module communication — capabilities.
     * @BABOK Related: FR-PD-004-001-inter-module-communication.md
     */
    public function getModuleCapabilities(&$data, $opts = null)
    {
        $capabilities = [
            'payment_redirect' => [
                'description' => 'Redirect non-cash payments to per-type GL accounts via db_prewrite',
                'methods'    => ['db_prewrite'],
            ],
        ];
        $data['capabilities'] = $capabilities;
        return $capabilities;
    }

    /**
     * Standard inter-module communication — capability query.
     * @BABOK Related: FR-PD-004-001-inter-module-communication.md
     */
    public function hasCapability(&$data, $opts = null)
    {
        $capability = $opts['capability'] ?? $data['capability'] ?? null;
        if ($capability === null) {
            $data['has_capability'] = false;
            return false;
        }
        $has = in_array($capability, ['payment_redirect']);
        $data['has_capability'] = $has;
        return $has;
    }

    /**
     * Standard inter-module communication — dispatch.
     * @BABOK Related: FR-PD-004-001-inter-module-communication.md
     */
    public function respondToCapabilityRequest(&$data, $opts = null)
    {
        $request = $opts['request'] ?? $data['request'] ?? 'capabilities';
        switch ($request) {
            case 'capabilities':
                return $this->getModuleCapabilities($data, $opts);
            case 'constants':
                return $this->getModuleConstants($data, $opts);
            case (strpos($request, 'has:') === 0):
                return $this->hasCapability($data, ['capability' => substr($request, 4)]);
            default:
                return null;
        }
    }

    /***************************************************************************************//**
     * Register menu items under GL and orders app.
     * @BABOK Related: UC-PD-001-configure-destinations.md, UC-PD-002-add-payment-mapping.md
     * ***********************************************************************************/
    function install_options($app) {
        global $path_to_root;
        $module_dir = basename(__DIR__);
        $path = $path_to_root . '/modules/' . $module_dir . '/ksf_payment_destinations.php';
        switch($app->id) {
            case 'GL':
                $app->add_rapp_function(2, _('ksf_payment_destinations'), $path, 'SA_ksf_payment_destinations');
                break;
            case 'orders':
                $app->add_lapp_function(0, _('ksf_payment_destinations'), $path, 'SA_ksf_payment_destinations');
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
        $security_areas['SA_ksf_payment_destinations'] = array(SS_ksf_payment_destinations|1, _("ksf_payment_destinations"));
        return array($security_areas, $security_sections);
    }

    /***************************************************************************************//**
     * Install database schema on module activation.
     * @BABOK Related: FR-PD-001-002-table-definition.md
     * ***********************************************************************************/
    function activate_extension($company, $check_only=true)
    {
	    /*
        if (file_exists(dirname(__FILE__) . '/sql/install.sql')) {
            $updates = array('install.sql' => array($this->module_name));
	    return $this->update_databases($company, $updates, $check_only);
	}
	     */
        return true;
    }

    /***************************************************************************************/    /**
     * Intercept ST_SALESINVOICE, route payment to mapped bank account.
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
        return true;
    }
}
