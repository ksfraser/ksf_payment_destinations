<?php
/**
 * ksf_payment_destinations UI entry point — refactored to PSR-4 + FA native UI.
 *
 * Uses:
 *   - QueryBuilder for SELECT/DELETE statements
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

// -- Detect action from POST --
$editTerm = null;
$delTerm  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'Delete') === 0) {
            $delTerm = (int) substr($key, 6);
            break;
        }
        if (strpos($key, 'Edit') === 0) {
            $editTerm = (int) substr($key, 4);
            break;
        }
    }
}

// -- Handle actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($delTerm > 0) {
        $qb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
        $qb->delete()->where('payment_term', $delTerm);
        db_query($qb->toFaSql(), 'delete mapping');
    }

    if (isset($_POST['payment_term']) && isset($_POST['bank_account'])) {
        $paymentTerm = (int) $_POST['payment_term'];
        $bankAccount = (int) $_POST['bank_account'];
        if ($paymentTerm > 0 && $bankAccount > 0) {
            $checkQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
            $checkQb->select()->where('payment_term', $paymentTerm);
            $existing = db_fetch(db_query($checkQb->toFaSql(), 'check existing'));

            if ($existing) {
                db_query(
                    "UPDATE $tableName
                     SET bank_account = $bankAccount,
                         bank_account_name = (SELECT bank_account_name FROM " . TB_PREF . "bank_accounts WHERE id = $bankAccount)
                     WHERE payment_term = $paymentTerm",
                    'update mapping'
                );
            } else {
                db_query(
                    "INSERT INTO $tableName (payment_term, payment_term_name, bank_account, bank_account_name)
                     SELECT $paymentTerm, pt.terms, $bankAccount, ba.bank_account_name
                     FROM " . TB_PREF . "payment_terms pt, " . TB_PREF . "bank_accounts ba
                     WHERE pt.terms_indicator = $paymentTerm AND ba.id = $bankAccount",
                    'insert mapping'
                );
            }
        }
    }

    meta_redirect($_SERVER['PHP_SELF']);
    exit;
}

// -- Load edit row if editing --
$editRow = null;
if ($editTerm > 0) {
    $editQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
    $editQb->select()->where('payment_term', $editTerm);
    $editRow = db_fetch(db_query($editQb->toFaSql(), 'load edit row'));
}

// -- Render page --
$js = '';
page(_('Payment Destinations'), true, false, '', $js);

echo '<div class="ksf-pd-container">';
echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';

// Listing via QueryBuilder (no WHERE, so no param substitution needed)
$listQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder("$tableName pd");
$listQb->select([
    'pd.payment_term',
    'pt.terms as payment_term_name',
    'ba.bank_account_name',
    'ba.bank_account_code'
]);
$listQb->join(TB_PREF . 'payment_terms pt', 'pt.terms_indicator = pd.payment_term');
$listQb->join(TB_PREF . 'bank_accounts ba', 'ba.id = pd.bank_account');
$listQb->orderBy('pt.terms', 'ASC');

$result = db_query($listQb->toSql(), 'load mappings');

start_form();
start_table(TABLESTYLE2, 'width=60%');
$th = [_('Payment Term'), _('Bank Account'), '', ''];
table_header($th);
$k = 0;
while ($row = db_fetch_assoc($result)) {
    alt_table_row_color($k);
    label_cell($row['payment_term_name']);
    label_cell($row['bank_account_name'] . ' (' . $row['bank_account_code'] . ')');
    edit_button_cell('Edit' . $row['payment_term'], _('Edit'));
    delete_button_cell('Delete' . $row['payment_term'], _('Delete'));
    end_row();
}
end_table();
end_form();

// Add or Edit form
echo '<h3>' . ($editRow ? _('Edit Mapping') : _('Add New Mapping')) . '</h3>';
echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
start_table(TABLESTYLE2, 'width=40%');
$th = [_('Payment Term'), _('Bank Account')];
table_header($th);
start_row();
$selectedTerm = $editRow ? $editRow['payment_term'] : ($_POST['payment_term'] ?? '');
echo '<td>' . sale_payment_list('payment_term', $selectedTerm, false, true) . '</td>';
$selectedBank = $editRow ? $editRow['bank_account'] : ($_POST['bank_account'] ?? '');
echo '<td>' . bank_accounts_list('bank_account', $selectedBank, false, false) . '</td>';
end_row();
end_table(1);
submit_center($editRow ? 'Update' : 'Add', _($editRow ? 'Update Mapping' : 'Map the accounts'));
end_form();

echo '</div>';

end_page();
