<?php
/**
 * SummaryTableComponent — renders the payment destinations mapping table.
 *
 * Renders:
 *   - FA-styled table with Payment Term + Bank Account columns
 *   - Edit / Delete per-row FA button cells (POST-based like legacy)
 *
 * @package Ksfraser\PaymentDestinations\UI
 */

namespace ksfraser\PaymentDestinations\UI;

class SummaryTableComponent
{
    /** @var array<int, array{id?: int, payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> */
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function render(): void
    {
        start_table(TABLESTYLE2, 'width=60%');
        $th = [_('ID'), _('Payment Term'), _('Bank Account'), '', ''];
        table_header($th);
        $k = 0;
        foreach ($this->rows as $row) {
            alt_table_row_color($k);
            $id = $row['id'] ?? $row['payment_term'];
            label_cell($id);
            label_cell(htmlspecialchars($row['payment_term_name']));
            label_cell(htmlspecialchars($row['bank_account_name']) . ' (' . htmlspecialchars($row['bank_account_code']) . ')');
            edit_button_cell('Edit' . $id, _('Edit'));
            delete_button_cell('Delete' . $id, _('Delete'));
            end_row();
        }
        end_table();
    }

    public function toHtml(): string
    {
        ob_start();
        $this->render();
        return (string) ob_get_clean();
    }
}
