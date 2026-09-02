<!-- Repo-specific appendix to the shared AGENTS.md. Generic conventions live in AGENTS_ARCH.md (hardlinked). -->

# AGENTS.local.md — ksf_payment_destinations

## CRITICAL: Primary Dev Tree

**`~/Documents/FA_PaymentDestinations/` is an ABORTED branch.** Do not develop there.
That directory is a bind mount (via `~/Documents/ksf_Infrastructure/`) that was used
for documentation only — it has no `src/`, no `Tests/`, no PSR-4 refactoring, and no
`@BABOK` traceability annotations. All development happens in
`~/Documents/ksf_payment_destinations/` which is the primary working dev tree.

If you find yourself editing files in `FA_PaymentDestinations/` — STOP and switch
to `ksf_payment_destinations/` immediately.

## Module Overview

ksf_payment_destinations is a FrontAccounting module that intercepts
`ST_SALESINVOICE` transactions via the `db_prewrite` hook. When a sales
invoice is posted, it checks the customer's `payment_terms` against a
mapping table (`0_ksf_payment_destinations`). If a mapping exists, it
rewrites `$cart->pos['pos_account']` to redirect the GL posting to the
correct bank account. It also forces `$cart->payment_terms['cash_sale'] = 1`
so FA records it as a cash transaction, suppressing the normal payment
entry form.

## Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Hooks class | `hooks_ksf_payment_destinations` | `hooks_ksf_payment_destinations` |
| Security section | `SS_ksf_payment_destinations` | `SS_ksf_payment_destinations` (111 << 8) |
| Security area | `SA_ksf_payment_destinations` | `SA_ksf_payment_destinations` |
| SQL tables | `0_ksf_<tablename>` | `0_ksf_payment_destinations` |
| Module code | PD | FR-PD-001-001, UC-PD-001, UAT-PD-001 |
| Config format | gzip compressed, `Key: Value` | `_init/config` |

## Key Files

| File | Purpose |
|------|---------|
| `hooks.php` | FA hooks class — db_prewrite, menu, security, install |
| `class.ksf_payment_destinations.php` | MVC controller (CRUD orchestration) |
| `class.ksf_payment_destinations_model.php` | Model — reads/writes `0_ksf_payment_destinations` |
| `class.ksf_payment_destinations_view.php` | View — UI forms and table rendering |
| `ksf_payment_destinations.php` | Entry point page (menu target) |
| `ksf_payment_destinations.inc.php` | Constants (PREFS, HELP) |
| `sql/install.sql` | CREATE TABLE DDL |

## SQL Table

```sql
CREATE TABLE IF NOT EXISTS `0_ksf_payment_destinations` (
  `payment_term`       int(11) NOT NULL DEFAULT 0,
  `payment_term_name`  varchar(200) NOT NULL DEFAULT '',
  `bank_account`      int(11) NOT NULL DEFAULT 0,
  `bank_account_name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`payment_term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Use `0_` prefix (NOT `@TB_PREF@` or `{TB_PREF}`).

## PSR-4 Architecture

| Element | PSR-4 Namespace | File |
|---------|----------------|------|
| Non-FA business logic | `ksfraser\PaymentDestinations\` | `src/` |
| FA-specific code | `ksfraser\FrontAccounting\PaymentDestinations\` | `src/FrontAccounting/` |
| Controller | `ksfraser\PaymentDestinations\Controller\` | `src/Controller/PaymentDestinationController.php` |
| Repository | `ksfraser\PaymentDestinations\Repository\` | `src/Repository/PaymentDestinationRepository.php` |
| Service | `ksfraser\PaymentDestinations\Service\` | `src/Service/PaymentDestinationService.php` |
| Hooks trait | `ksfraser\PaymentDestinations\Hooks\` | `src/Hooks/HooksPaymentDestinations.php` |
| QueryBuilder | `ksfraser\PaymentDestinations\QueryBuilder\` | `src/QueryBuilder/QueryBuilder.php` |
| ValidatableTrait | `ksfraser\PaymentDestinations\Traits\` | `src/Traits/ValidatableTrait.php` |

## Inter-Module Communication

Two mechanisms coexist:

1. **PSR-4 DI** (primary): `HooksPaymentDestinations` uses `Ksfraser\Traits\HookQueryProviderTrait`
   for inter-module queries via `hook_invoke_first('ksf_get_value', ...)` / `hook_invoke_all('ksf_get_values', ...)`

2. **4 Standard Capability Methods** (FA convention, preserved): `getModuleConstants`,
   `getModuleCapabilities`, `hasCapability`, `respondToCapabilityRequest` — allow other
   FA modules to query this module's capabilities without DI coupling.

Both mechanisms can coexist. The capability methods use FA's hook interface; the DI approach
uses Composer autoloading.

## Decoupling from ksf_FA_Square

This module is **fully decoupled** from `ksf_FA_Square`. Neither module imports the other.
The interaction is implicit via hook execution order:

- **ksf_FA_Square** `db_prewrite` fires FIRST for Square-Invoice payment terms
  (`square_invoice`, `square_invoice_email`, `square_invoice_card`) and sets `cash_sale = 0`.
- **ksf_payment_destinations** `db_prewrite` fires SECOND and handles non-Square redirections.

## Documentation Naming

All requirements live under `ProjectDcs/`. Naming conventions:

| Type | Pattern | Example |
|------|---------|---------|
| Business Requirement | `BR-PD-<SEQ>-<short-name>.md` | `BR-PD-001-payment-routing.md` |
| Functional Requirement | `FR-PD-<SEQ>-<SEQ2>-<short-name>.md` | `FR-PD-001-001-mapping-ui.md` |
| Use Case | `UC-PD-<SEQ>-<short-name>.md` | `UC-PD-001-configure-destinations.md` |
| Unit Test spec | `UT-PD-<SEQ>-<SEQ2>-<SEQ3>-<short-name>.md` | `UT-PD-001-001-001-add-mapping.md` |
| UAT Case | `UAT-PD-<SEQ>-<short-name>.md` | `UAT-PD-001-admin-add-mapping.md` |
| RTM | `RTM.md` | Traceability matrix (auto-generated from @BABOK tags) |
