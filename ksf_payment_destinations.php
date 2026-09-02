<?php
/**
 * ksf_payment_destinations UI entry point — refactored to PSR-4 + FA native UI.
 *
 * Uses:
 *   - PSR-4 PaymentDestinationService (even if stubs for now)
 *   - FA native combo_input() for DDLs (payment_terms, bank_accounts)
 *   - Manual table render for summary (edit/delete wired to $_POST)
 *
 * @BABOK Related: UC-PD-001-configure-destinations.md, UC-PD-002-add-payment-mapping.md
 */

chdir(__DIR__);

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

$page_security = 'SA_ksf_payment_destinations';
$path_to_root = '../..';
include_once $path_to_root . '/includes/session.inc';
add_access_extensions();
set_ext_domain('modules/ksf_payment_destinations');
include_once $path_to_root . '/includes/ui.inc';
include_once $path_to_root . '/includes/data_checks.inc';

$tableName = TB_PREF . 'ksf_payment_destinations';

// -- Handle actions (add / delete) --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'Delete') === 0) {
            $id = (int) substr($key, 6);
            if ($id > 0) {
                db_query("DELETE FROM $tableName WHERE id = $id", 'delete mapping');
            }
        }
    }

    // Add new mapping
    if (isset($_POST['payment_term']) && isset($_POST['bank_account'])) {
        $paymentTerm = (int) $_POST['payment_term'];
        $bankAccount = (int) $_POST['bank_account'];
        if ($paymentTerm > 0 && $bankAccount > 0) {
            db_query(
                "INSERT INTO $tableName (payment_term, bank_account) VALUES ($paymentTerm, $bankAccount)",
                'insert mapping'
            );
        }
    }

    meta_redirect($_SERVER['PHP_SELF']);
    exit;
}

// -- Render page --
$js = '';
page(_('Payment Destinations'), true, false, '', $js);

echo '<div class="ksf-pd-container">';
echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';

$result = db_query(
    "SELECT pd.id, pt.terms as payment_term_name, ba.bank_account_name, ba.bank_account_code
     FROM $tableName pd
     LEFT JOIN " . TB_PREF . "payment_terms pt ON pt.terms_indicator = pd.payment_term
     LEFT JOIN " . TB_PREF . "bank_accounts ba ON ba.id = pd.bank_account
     ORDER BY pt.terms",
    'load mappings'
);

start_form();
start_table(TABLESTYLE2, 'width=60%');
$th = [_('ID'), _('Payment Term'), _('Bank Account'), '', ''];
table_header($th);
$k = 0;
while ($row = db_fetch_assoc($result)) {
    alt_table_row_color($k);
    label_cell($row['id']);
    label_cell($row['payment_term_name']);
    label_cell($row['bank_account_name'] . ' (' . $row['bank_account_code'] . ')');
    edit_button_cell('Edit' . $row['id'], _('Edit'));
    delete_button_cell('Delete' . $row['id'], _('Delete'));
    end_row();
}
end_table();
end_form();

// Add form
echo '<h3>' . _('Add New Mapping') . '</h3>';
echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
start_table(TABLESTYLE2, 'width=40%');
$th = [_('Payment Term'), _('Bank Account')];
table_header($th);
start_row();
echo '<td>' . sale_payment_list('payment_term', '', false, true) . '</td>';
echo '<td>' . bank_accounts_list('bank_account', null, false, false) . '</td>';
end_row();
end_table(1);
submit_center('add', _('Map the accounts'));
end_form();

echo '</div>';

end_page();
