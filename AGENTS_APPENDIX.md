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
- `hooks_ksf_payment_destinations` (bare functions) → `HooksPaymentDestinations` (namespaced, inter-module: `getModuleConstants`, `getModuleCapabilities`, `hasCapability`, `respondToCapabilityRequest`)
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

## Traceability Rule
When a function is rewritten/moved, the original file must retain a commented stub referencing new location and `@BABOK` ID.
