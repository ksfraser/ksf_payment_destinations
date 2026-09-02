<?php
/**
 * SummaryTableComponent — renders the payment destinations mapping table.
 *
 * Renders:
 *   - FA-styled table with Payment Term + Bank Account columns
 *   - Edit / Delete per-row links (GET-based, not form POST)
 *
 * @package Ksfraser\PaymentDestinations\UI
 */

namespace ksfraser\PaymentDestinations\UI;

class SummaryTableComponent
{
    /** @var array<int, array{payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> */
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function render(): void
    {
        start_table(TABLESTYLE2, 'width=60%');
        $th = [_('Payment Term'), _('Bank Account'), '', ''];
        table_header($th);
        $k = 0;
        foreach ($this->rows as $row) {
            alt_table_row_color($k);
            label_cell(htmlspecialchars($row['payment_term_name']));
            label_cell(htmlspecialchars($row['bank_account_name']) . ' (' . htmlspecialchars($row['bank_account_code']) . ')');
            echo '<td><a href="controller.php?action=edit&term=' . (int) $row['payment_term'] . '">' . _('Edit') . '</a></td>';
            echo '<td><a href="controller.php?action=delete&term=' . (int) $row['payment_term'] . '" onclick="return confirm(\'' . _('Are you sure?') . '\');">' . _('Delete') . '</a></td>';
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
