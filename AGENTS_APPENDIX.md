# AGENTS_APPENDIX.md — ksf_payment_destinations

## Module Code
PD

## Namespace Mapping
- Non-FA business logic: `ksfraser\PaymentDestinations\`
- FA-specific code: `ksfraser\FrontAccounting\PaymentDestinations\`

## Replacement of Deprecated Base Classes (per AGENTS.md + ksf_bank_import ARCHITECTURE_MIGRATION.md)
See `ProjectDcs/Architecture.md`:
- `generic_fa_interface_model` / `table_interface` → `Repository` (`TransactionRepository`) + `QueryBuilder`
- `generic_fa_interface_controller` → `Service` (`PaymentDestinationService`) + `Controller` (thin layer, DI)
- `hooks_ksf_payment_destinations` (bare functions) → `HooksPaymentDestinations` (namespaced, uses `Ksfraser\Traits\HookQueryProviderTrait` for inter-module queries via `ksf_get_value`/`ksf_get_values`/`ksf_set_value`)
- Traits (`Ksfraser\Traits\ValidatableTrait`) replace inline validation
- `ModulesDAO` (`ksf_ModulesDAO`) provides cross-platform abstraction for new persistence

## Directory Mapping
- Controller: `src/Controller/PaymentDestinationController.php`
- Model: `src/Model/PaymentDestinationModel.php`
- View: `src/View/PaymentDestinationView.php`
- Hooks: `hooks.php` (FA convention, kept top-level)
- Entry point: `ksf_payment_destinations.php`

## Requirement References
Every class/method docblock must include `@BABOK Related:` referencing the file in `ProjectDcs/`.

## Composer Dependencies
Any module using Composer packages must include `ComposerDependencies` from `ksf_FA_Common`:

```php
// In hooks.php before any namespaced class is used:
$composerDepsPath = dirname(__DIR__) . '/ksf_FA_Common/src/Utils/ComposerDependencies.php';
if (file_exists($composerDepsPath)) {
    require_once $composerDepsPath;
    \ksfraser\FrontAccounting\Common\Utils\ComposerDependencies::ensure(__DIR__);
}
```

This runs `composer install` if `vendor/autoload.php` is missing, then FA's normal autoloader takes over.
**Required for:** `ksf_modules_common`, `ksf_traits` packages, all PSR-4 namespaced code.

## Inter-Module Communication (ksf_traits)
`HooksPaymentDestinations` uses `Ksfraser\Traits\HookQueryProviderTrait` (`ksf_traits`) for standard inter-module queries:

- `hook_invoke_first('ksf_get_value', 'payment_destinations.routing', ...)` → returns routing array
- `hook_invoke_all('ksf_get_values', $keys, ...)` → batch lookup

See: `src/Hooks/HooksPaymentDestinations.php`

## Traceability Rule
When a function is rewritten/moved, the original file must retain a commented stub referencing new location and `@BABOK` ID.
