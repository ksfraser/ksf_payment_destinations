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
    public function render(): void
    {
        $selectedTerm = $_POST['payment_term'] ?? '';
        $selectedBank = $_POST['bank_account'] ?? '';

        echo '<form method="post" action="controller.php">';
        start_table(TABLESTYLE2, 'width=40%');
        $th = [_('Payment Term'), _('Bank Account')];
        table_header($th);
        start_row();
        echo '<td>' . sale_payment_list('payment_term', $selectedTerm, false, true) . '</td>';
        echo '<td>' . bank_accounts_list('bank_account', $selectedBank, false, false) . '</td>';
        end_row();
        end_table(1);
        submit_center('Add', _('Add Mapping'));
        end_form();
    }

    public function toHtml(): string
    {
        ob_start();
        $this->render();
        return (string) ob_get_clean();
    }
}
