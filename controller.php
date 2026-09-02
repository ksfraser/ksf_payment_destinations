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

// $page_security = 'SA_ksf_payment_destinations';
$page_security = 'SA_OPEN';
$path_to_root = '../..';
include_once $path_to_root . '/includes/session.inc';
include_once $path_to_root . '/includes/ui.inc';

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
            meta_redirect('controller.php');
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
global $page_nested;
$page_nested = -1;
page(_('Payment Destinations'), true, false, '', $js);

echo '<div class="ksf-pd-container">';

// Load rows for the table
$listQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder("$tableName pd");
$listQb->select([
    'pd.payment_term',
    'pt.terms as payment_term_name',
    'ba.bank_account_name',
    'ba.account_code as bank_account_code'
]);
$listQb->join($tbPref . 'payment_terms pt', 'pt.terms_indicator = pd.payment_term');
$listQb->join($tbPref . 'bank_accounts ba', 'ba.id = pd.bank_account');
$listQb->orderBy('pt.terms', 'ASC');

$result = db_query($listQb->toSql(), 'load mappings');
$rows = [];
while ($row = db_fetch_assoc($result)) {
    $rows[] = $row;
}

// Load edit row if editing
$editRow = null;
if ($action === 'edit' && $id) {
    $editQb = new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder($tableName);
    $editQb->select()->where('payment_term', $id);
    $editRow = db_fetch(db_query($editQb->toFaSql(), 'load edit row'));
}

// Render via SummaryView (composes table + form components)
(new \ksfraser\PaymentDestinations\UI\SummaryView($rows, $editRow))->render();

echo '</div>';
end_page();
