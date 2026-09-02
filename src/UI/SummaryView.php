<?php
/**
 * SummaryView — composes SummaryTableComponent + MappingFormComponent.
 *
 * Renders the full payment destinations page:
 *   1. Page header + heading
 *   2. Summary table with Edit/Delete (POST-based)
 *   3. Add form below
 *
 * @package Ksfraser\PaymentDestinations\UI
 */

namespace ksfraser\PaymentDestinations\UI;

class SummaryView
{
    /** @var array<int, array{id?: int, payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> */
    private array $rows;

    /** @param array<int, array{id?: int, payment_term: int, payment_term_name: string, bank_account_name: string, bank_account_code: string}> $rows */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function render(): void
    {
        echo '<h3>' . _('Payment Term → Bank Account Mappings') . '</h3>';
        echo '<div id="ksf_pd_table">';
        (new SummaryTableComponent($this->rows))->render();
        echo '</div>';

        echo '<h3>' . _('Add New Mapping') . '</h3>';
        echo '<div id="ksf_pd_form">';
        (new MappingFormComponent())->render();
        echo '</div>';
    }

    public function toHtml(): string
    {
        ob_start();
        $this->render();
        return (string) ob_get_clean();
    }
}
