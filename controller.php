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

$page_security = 'SA_ksf_payment_destinations';
$path_to_root = '../..';
include_once $path_to_root . '/includes/session.inc';
include_once $path_to_root . '/includes/ui.inc';

// Resolve TB_PREF
$tbPref = defined('TB_PREF') ? str_replace('&TB_PREF&', '0_', TB_PREF) : '0_';
$tableName = $tbPref . 'ksf_payment_destinations';

// -- Repository setup --
$repo = new \ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository($tableName, $tbPref);

// DEBUG
echo "<!-- REPO-CREATED -->";

// Auto-create table if missing
if (!$repo->tableExists()) {
    $repo->createTable();
}

// DEBUG
echo "<!-- TABLE-CHECK-DONE -->";

// -- Detect POST actions --
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

// -- Handle POST actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($delTerm > 0) {
        $repo->deleteByTerm($delTerm);
        echo "<script>location.href='controller.php';</script>";
        exit;
    }

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

// DEBUG
echo "<!-- ROWS-LOADED: " . count($rows) . " -->";

$editRow = null;
if ($editTerm > 0) {
    $editRow = $repo->findByPaymentTerm($editTerm);
}

// -- Render page --
$js = '';
global $page_nested;
$page_nested = -1;
page(_('Payment Destinations'), true, false, '', $js);

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
    edit_button_cell('Edit' . $row['payment_term'], _('Edit'));
    delete_button_cell('Delete' . $row['payment_term'], _('Delete'));
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
echo '<td>' . sale_payment_list('payment_term', $selectedTerm, false, true) . '</td>';
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