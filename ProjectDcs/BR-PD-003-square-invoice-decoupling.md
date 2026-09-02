# BR-PD-003 — Square-Invoice Decoupling

## Business Rule: Do Not Interfere with Square-Invoice Payment Terms

**Status:** Active
**Owner:** ksf_payment_destinations module
**Related Code:** `hooks.php` (db_prewrite — no special-casing for square_invoice* terms)

## Stakeholders

| Stakeholder | Role | Interest |
|-------------|------|----------|
| Square Integration (ksf_FA_Square) | Owns square_invoice* payment terms | Must not be overridden by this module's db_prewrite |

## Description

This module SHALL NOT handle Square-Invoice payment terms (`square_invoice`, `square_invoice_email`, `square_invoice_card`). Those terms are owned exclusively by the ksf_FA_Square module, which intercepts the same `ST_SALESINVOICE` transactions via its own `db_prewrite` hook.

FA runs hooks in alphabetical order by module name. Since `FA_PaymentDestinations` < `FA_Square` alphabetically, this module's hook fires SECOND — after ksf_FA_Square has already processed Square-Invoice terms. This ensures no conflict.

## Rationale

Square-Invoice transactions require API-level interaction (handshake, payment processing, import matching) that is outside this module's scope. Running two hooks on the same transaction requires clear ownership. Square terms should never be in `0_ksf_payment_destinations`; if they accidentally are, this module finds no mapping and exits normally.

## Rules

1. This module does not import or reference ksf_FA_Square.
2. Square-Invoice terms (`square_invoice*`) are handled exclusively by ksf_FA_Square's `db_prewrite`.
3. If ksf_FA_Square sets `cash_sale=0` for a Square term, this module's hook must not override it.
4. This module's hook execution order is AFTER ksf_FA_Square (alphabetical module name ordering).
5. If no mapping exists for any payment term, this module takes no action and returns `true`.

## Scope

- Applies only to `square_invoice`, `square_invoice_email`, `square_invoice_card` payment terms.
- Other modules may register their own `db_prewrite` handlers; this module remains decoupled from all others.
- This module participates only in the routing of non-Square payment terms.

## Exception Handling

- If ksf_FA_Square is not installed, Square-Invoice terms are handled by FA's default behavior.
- If this module's `db_prewrite` finds no mapping for any term, it returns `true` without error.
- Database errors are caught and display an error notification; the hook returns `true` to avoid blocking the transaction.
