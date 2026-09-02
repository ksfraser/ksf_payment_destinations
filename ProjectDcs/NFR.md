# Non-Functional Requirements — ksf_payment_destinations

**Module:** ksf_payment_destinations
**Source:** Migrated from FA_PaymentDestinations/ProjectDocs/Requirements.md §3

---

## NFR List

| ID | Requirement |
|----|-------------|
| **NFR-01** | PHP 7.3 compatible — no typed properties, no nullsafe operator, no named arguments |
| **NFR-02** | FA 2.4.19 compatible — class-based hooks pattern, `0_` table prefix |
| **NFR-03** | Idempotent operations — re-running install.sql uses `IF NOT EXISTS` |
| **NFR-04** | Audit trail — `payment_term_name` and `bank_account_name` stored denormalized for historical readability |
| **NFR-05** | Decoupled from ksf_FA_Square — no direct imports or dependencies between modules |
| **NFR-06** | TDD workflow — all new code backed by PHPUnit tests with 100% coverage target |
| **NFR-07** | PHPDoc standards — `@param`, `@return`, `@throws`, `@since` required on all public methods |
| **NFR-08** | No secrets or keys in code or config |

---

## Implementation Notes

### NFR-01 (PHP 7.3 compatibility)
- No `?->` nullsafe operator (use `?->` replacement: `($obj?->prop)` is 8.0+)
- No named arguments
- No `mixed` type (use `object` or no type)
- All PSR-4 `src/` classes must pass `php -l` with no errors on PHP 7.3

### NFR-03 (Idempotent install)
- `sql/install.sql` uses `CREATE TABLE IF NOT EXISTS`
- `activate_extension()` calls `update_databases()` which is idempotent

### NFR-04 (Denormalized names)
- `payment_term_name` and `bank_account_name` stored in `0_ksf_payment_destinations` for audit trail
- Names resolved at insert time via `fa_payment_terms` and `fa_bank_accounts` JOINs
- If source records change, historical mappings retain the name that was current at insert time

### NFR-05 (Decoupling)
- `hooks.php` does not `include_once` or `require` any ksf_FA_Square files
- Capability/hook communication via FA's standard hook interface only
- `hasCapability('payment_redirect')` returns false for non-existent capabilities

### NFR-06 (TDD)
- All new `src/` classes require a corresponding `Tests/Unit/<ClassName>Test.php`
- Run `composer run test` (lint + phpunit) before every commit
- Legacy code changes require regression test updates
- UAT browser tests tracked in `ProjectDcs/UAT-PD-00X-*.md`
