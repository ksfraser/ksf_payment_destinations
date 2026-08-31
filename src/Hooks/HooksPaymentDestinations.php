<?php
namespace ksfraser\FrontAccounting\PaymentDestinations\Hooks;

use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;
use Ksfraser\Traits\HookQueryProviderTrait;

/**
 * Inter-module communication via HookQueryProviderTrait (ksf_get_value / ksf_get_values).
 *
 * Advertised keys:
 *   payment_destinations.routing  -> returns ['bank_account' => int] for current cart
 *
 * @BABOK Related: FR-PD-001-003-payment-redirect.md, BR-PD-001-payment-routing.md
 * @uses HookQueryProviderTrait (ksf_traits package)
 */
class HooksPaymentDestinations
{
    use HookQueryProviderTrait;

    protected PaymentDestinationServiceInterface $service;

    public function __construct(PaymentDestinationServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function _getAdvertisedValues(): array
    {
        return [
            'payment_destinations.routing' => $this->getRoutingValue(),
        ];
    }

    /**
     * Runtime routing lookup — reads current cart from FA global scope.
     * Returns bank_account for the payment term, or null if no mapping exists.
     */
    protected function getRoutingValue(): ?array
    {
        global $cart;

        if (!isset($cart->payment_terms['terms_indicator'])) {
            return null;
        }

        $term = (int) $cart->payment_terms['terms_indicator'];
        $bankAccount = $this->service->getBankAccountFromTerm($term);

        if ($bankAccount === 0) {
            return null;
        }

        return [
            'bank_account' => $bankAccount,
            'payment_term' => $term,
            'cash_sale'    => true,
        ];
    }

    public function getService(): PaymentDestinationServiceInterface
    {
        return $this->service;
    }

    /**
     * Legacy direct routing method — retained for backwards compatibility during transition.
     * Use hook_invoke_first('ksf_get_value', 'payment_destinations.routing') instead.
     *
     * @BABOK Related: FR-PD-001-003-payment-redirect.md
     */
    public function getRoutingForCart($cart): ?array
    {
        if (!isset($cart->payment_terms['terms_indicator'])) {
            return null;
        }
        $term = (int) $cart->payment_terms['terms_indicator'];
        $bankAccount = $this->service->getBankAccountFromTerm($term);
        return $bankAccount > 0
            ? ['bank_account' => $bankAccount, 'payment_term' => $term, 'cash_sale' => true]
            : null;
    }
}
