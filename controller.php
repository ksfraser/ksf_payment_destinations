<?php
/**
 * ksf_payment_destinations controller — routes actions via GET/POST params.
 *
 * Actions:
 *   list   - summary table (default)
 *   add    - add new mapping form
 *   edit   - edit existing mapping
 *   delete - delete mapping, redirect to list
 *
 * @BABOK Related: UC-PD-001-configure-destinations.md, UC-PD-002-add-payment-mapping.md
 */

chdir(__DIR__);

header('Cache-Control: no-store, must-revalidate');
header('Pragma: no-cache');

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

$page_security = 'SA_ksf_payment_destinations';
$path_to_root = '../..';
include_once $path_to_root . '/includes/session.inc';

// Resolve TB_PREF — may be literal '&TB_PREF&' when included outside FA context; default to '0_'
$tbPref = defined('TB_PREF') ? str_replace('&TB_PREF&', '0_', TB_PREF) : '0_';
$tableName = $tbPref . 'ksf_payment_destinations';

// Auto-create table if missing
$result = db_query("SHOW TABLES LIKE '$tableName'", 'check table');
if (!db_fetch_row($result)) {
    db_query(
        "CREATE TABLE IF NOT EXISTS $tableName (
            payment_term INT(11) NOT NULL DEFAULT 0,
            payment_term_name VARCHAR(100) NOT NULL DEFAULT '',
            bank_account INT(11) NOT NULL DEFAULT 0,
            bank_account_name VARCHAR(100) NOT NULL DEFAULT '',
            PRIMARY KEY (payment_term)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'create table'
    );
}

// -- Routing --
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$id = $_GET['term'] ?? null;

// -- POST actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'delete':
            if ($id) {
                $qb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
                $qb->delete()->where('payment_term', $id);
                db_query($qb->toFaSql(), 'delete mapping');
            }
            meta_redirect('controller.php?action=list');
            exit;

        case 'save':
            $paymentTerm = (int) ($_POST['payment_term'] ?? 0);
            $bankAccount = (int) ($_POST['bank_account'] ?? 0);
            if ($paymentTerm > 0 && $bankAccount > 0) {
                $checkQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
                $checkQb->select()->where('payment_term', $paymentTerm);
                $existing = db_fetch(db_query($checkQb->toFaSql(), 'check existing'));

                if ($existing) {
                    db_query(
                        "UPDATE $tableName
                         SET bank_account = $bankAccount,
                             bank_account_name = (SELECT bank_account_name FROM " . $tbPref . "bank_accounts WHERE id = $bankAccount)
                         WHERE payment_term = $paymentTerm",
                        'update mapping'
                    );
                } else {
                    db_query(
                        "INSERT INTO $tableName (payment_term, payment_term_name, bank_account, bank_account_name)
                         SELECT $paymentTerm, pt.terms, $bankAccount, ba.bank_account_name
                         FROM " . $tbPref . "payment_terms pt, " . $tbPref . "bank_accounts ba
                         WHERE pt.terms_indicator = $paymentTerm AND ba.id = $bankAccount",
                        'insert mapping'
                    );
                }
            }
            meta_redirect('controller.php?action=list');
            exit;
    }
}

// -- Render page --
$js = '';
page(_('Payment Destinations'), true, false, '', $js);

echo "\n<!-- PD-CONTROLLER-V1 -->\n";

echo '<div class="ksf-pd-container">';

// -- Render summary table (always shown) --
echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';

$listQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder("$tableName pd");
$listQb->select([
    'pd.payment_term',
    'pt.terms as payment_term_name',
    'ba.bank_account_name',
    'ba.bank_account_code'
]);
$listQb->join($tbPref . 'payment_terms pt', 'pt.terms_indicator = pd.payment_term');
$listQb->join($tbPref . 'bank_accounts ba', 'ba.id = pd.bank_account');
$listQb->orderBy('pt.terms', 'ASC');

$result = db_query($listQb->toSql(), 'load mappings');

start_form(false, false, 'delete-form');
start_table(TABLESTYLE2, 'width=60%');
$th = [_('Payment Term'), _('Bank Account'), '', ''];
table_header($th);
$k = 0;
while ($row = db_fetch_assoc($result)) {
    alt_table_row_color($k);
    label_cell($row['payment_term_name']);
    label_cell($row['bank_account_name'] . ' (' . $row['bank_account_code'] . ')');
    echo '<td><a href="controller.php?action=edit&term=' . $row['payment_term'] . '">' . _('Edit') . '</a></td>';
    echo '<td><a href="controller.php?action=delete&term=' . $row['payment_term'] . '" onclick="return confirm(\'' . _('Are you sure?') . '\');">' . _('Delete') . '</a></td>';
    end_row();
}
end_table();
end_form();

// -- Render add/edit form below summary table --
$editRow = null;
if ($action === 'edit' && $id) {
    $editQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
    $editQb->select()->where('payment_term', $id);
    $editRow = db_fetch(db_query($editQb->toFaSql(), 'load edit row'));
}

$selectedTerm = $editRow['payment_term'] ?? ($_POST['payment_term'] ?? '');
$selectedBank = $editRow['bank_account'] ?? ($_POST['bank_account'] ?? '');

echo '<h3>' . ($editRow ? _('Edit Mapping') : _('Add New Mapping')) . '</h3>';
echo '<form method="post" action="controller.php">';
echo '<input type="hidden" name="action" value="save">';
if ($editRow) {
    echo '<input type="hidden" name="payment_term" value="' . $editRow['payment_term'] . '">';
}
start_table(TABLESTYLE2, 'width=40%');
$th = [_('Payment Term'), _('Bank Account')];
table_header($th);
start_row();
echo '<td>' . sale_payment_list('payment_term', $selectedTerm, false, true) . '</td>';
echo '<td>' . bank_accounts_list('bank_account', $selectedBank, false, false) . '</td>';
end_row();
end_table(1);
submit_center($editRow ? 'Update' : 'Add', _($editRow ? 'Update Mapping' : 'Add Mapping'));
end_form();

echo '</div>';
end_page();
