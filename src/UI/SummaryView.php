<?php
/**
 * SummaryView — composes SummaryTableComponent + MappingFormComponent.
 *
 * Renders the full payment destinations page:
 *   1. Page header + heading
 *   2. Summary table (always shown)
 *   3. Add/Edit form (below table)
 *
 * @package Ksfraser\PaymentDestinations\UI
 */

namespace ksfraser\PaymentDestinations\UI;

class SummaryView
{
    /** @var array<int, array{payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> */
    private array $rows;

    private ?array $editRow;

    /** @param array<int, array{payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> $rows */
    public function __construct(array $rows, ?array $editRow = null)
    {
        $this->rows = $rows;
        $this->editRow = $editRow;
    }

    public function render(): void
    {
        echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';
        echo '<div id="ksf_pd_table">';
        (new SummaryTableComponent($this->rows))->render();
        echo '</div>';

        echo '<h3>' . ($this->editRow ? _('Edit Mapping') : _('Add New Mapping')) . '</h3>';
        echo '<div id="ksf_pd_form">';
        (new MappingFormComponent($this->editRow))->render();
        echo '</div>';
    }

    public function toHtml(): string
    {
        ob_start();
        $this->render();
        return (string) ob_get_clean();
    }
}
