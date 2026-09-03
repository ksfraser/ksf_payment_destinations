<?php
/**
 * ksf_payment_destinations controller — routes actions via GET/POST params.
 *
 * Actions:
 *   list   - summary table (default)
 *   edit   - edit existing mapping (POST Edit{id})
 *   add    - add new mapping (POST with payment_term + bank_account)
 *   delete - delete mapping (POST Delete{id})
 *
 * Uses PaymentDestinationRepository for all DB operations.
 *
 * @BABOK Related: UC-PD-001-configure-destinations.md, UC-PD-002-add-payment-mapping.md
 */

chdir(__DIR__);

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// $page_security = 'SA_ksf_payment_destinations';
$page_security = 'SA_OPEN';
$path_to_root = '../..';
include_once $path_to_root . '/includes/session.inc';
include_once $path_to_root . '/includes/ui.inc';

// Resolve TB_PREF
$tbPref = defined('TB_PREF') ? str_replace('&TB_PREF&', '0_', TB_PREF) : '0_';
$tableName = $tbPref . 'ksf_payment_destinations';

// -- Repository setup --
$repo = new \ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository($tableName, $tbPref);

// Auto-create table if missing
if (!$repo->tableExists()) {
    $repo->createTable();
}

// -- Routing --
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$id = $_GET['term'] ?? null;

// -- Handle actions --
if ($action === 'delete' && $id) {
    $repo->deleteByTerm((int) $id);
    echo "<script>location.href='controller.php';</script>";
    exit;
}

// -- Handle POST actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['payment_term']) && isset($_POST['bank_account'])) {
        $paymentTerm = (int) $_POST['payment_term'];
        $bankAccount = (int) $_POST['bank_account'];
        if ($paymentTerm > 0 && $bankAccount > 0) {
            $repo->upsert($paymentTerm, $bankAccount);
        }
        echo "<script>location.href='controller.php';</script>";
        exit;
    }
}

// -- Load data for rendering --
$rows = $repo->findAll();

$editRow = null;
if ($action === 'edit' && $id) {
    $editRow = $repo->findByPaymentTerm((int) $id);
}

// -- Render page --
$js = '';
global $page_nested;
$page_nested = -1;
page(_('Payment Destinations'), false, false, '', $js);

echo '<div class="ksf-pd-container">';
echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';

// Summary table
start_form();
start_table(TABLESTYLE2, 'width=60%');
$th = [_('Payment Term'), _('Bank Account'), '', ''];
table_header($th);
$k = 0;
foreach ($rows as $row) {
    alt_table_row_color($k);
    label_cell(htmlspecialchars($row['payment_term_name']));
    label_cell(htmlspecialchars($row['bank_account_name']) . ' (' . htmlspecialchars($row['bank_account_code']) . ')');
    echo '<td><a href="controller.php?action=edit&term=' . (int) $row['payment_term'] . '">' . _('Edit') . '</a></td>';
    echo '<td><a href="controller.php?action=delete&term=' . (int) $row['payment_term'] . '" onclick="return confirm(\'' . _('Are you sure?') . '\');">' . _('Delete') . '</a></td>';
    end_row();
}
end_table();
end_form();

// Add/Edit form
echo '<h3>' . ($editRow ? _('Edit Mapping') : _('Add New Mapping')) . '</h3>';
echo '<form method="post" action="controller.php">';
start_table(TABLESTYLE2, 'width=40%');
$th = [_('Payment Term'), _('Bank Account')];
table_header($th);
start_row();
$selectedTerm = $editRow ? $editRow['payment_term'] : ($_POST['payment_term'] ?? '');
echo '<td>' . sale_payment_list('payment_term', null, $selectedTerm, false, false) . '</td>';
$selectedBank = $editRow ? $editRow['bank_account'] : ($_POST['bank_account'] ?? '');
echo '<td>' . bank_accounts_list('bank_account', $selectedBank, false, false) . '</td>';
end_row();
end_table(1);
submit_center($editRow ? 'Update' : 'Add', _($editRow ? 'Update Mapping' : 'Map the accounts'));
if ($editRow) {
    echo ' <a href="controller.php">' . _('Cancel') . '</a>';
}
end_form();

echo '</div>';
end_page();