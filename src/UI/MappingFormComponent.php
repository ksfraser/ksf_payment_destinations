<?php
/**
 * MappingFormComponent — renders the Add/Edit mapping form.
 *
 * Renders:
 *   - sale_payment_list combo
 *   - bank_accounts_list combo
 *   - Save/Cancel submit
 *
 * @package Ksfraser\PaymentDestinations\UI
 */

namespace ksfraser\PaymentDestinations\UI;

class MappingFormComponent
{
    private ?array $editRow;
    private string $submitLabel;
    private string $submitName;

    public function __construct(?array $editRow = null)
    {
        $this->editRow = $editRow;
        $this->submitLabel = $editRow ? _('Update Mapping') : _('Add Mapping');
        $this->submitName = $editRow ? 'Update' : 'Add';
    }

    public function render(): void
    {
        $selectedTerm = $this->editRow['payment_term'] ?? ($_POST['payment_term'] ?? '');
        $selectedBank = $this->editRow['bank_account'] ?? ($_POST['bank_account'] ?? '');

        echo '<form method="post" action="controller.php">';
        echo '<input type="hidden" name="action" value="save">';
        if ($this->editRow) {
            echo '<input type="hidden" name="payment_term" value="' . (int) $this->editRow['payment_term'] . '">';
        }
        start_table(TABLESTYLE2, 'width=40%');
        $th = [_('Payment Term'), _('Bank Account')];
        table_header($th);
        start_row();
        echo '<td>' . sale_payment_list('payment_term', $selectedTerm, false, true) . '</td>';
        echo '<td>' . bank_accounts_list('bank_account', $selectedBank, false, false) . '</td>';
        end_row();
        end_table(1);
        submit_center($this->submitName, $this->submitLabel);
        echo ' <a href="controller.php">' . _('Cancel') . '</a>';
        end_form();
    }

    public function toHtml(): string
    {
        ob_start();
        $this->render();
        return (string) ob_get_clean();
    }
}
