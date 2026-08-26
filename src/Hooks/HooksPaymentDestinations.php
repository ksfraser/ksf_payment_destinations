<?php
namespace ksfraser\FrontAccounting\PaymentDestinations\Hooks;

use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;

class HooksPaymentDestinations
{
    protected PaymentDestinationServiceInterface $service;

    public function __construct(PaymentDestinationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getModuleConstants(): array
    {
        return ['PREFS' => 'ksf_payment_destinations_prefs'];
    }

    public function getModuleCapabilities(): array
    {
        return ['routing' => true, 'mapping' => true];
    }

    public function hasCapability(string $cap): bool
    {
        return in_array($cap, array_keys($this->getModuleCapabilities()));
    }

    public function respondToCapabilityRequest(string $cap, array $context): ?array
    {
        if ($cap === 'routing' && isset($context['cart'])) {
            $term = $context['cart']->payment_terms['terms_indicator'] ?? 0;
            $bankAccount = $this->service->getBankAccountFromTerm((int)$term);
            return ['bank_account' => $bankAccount];
        }
        return null;
    }
}
