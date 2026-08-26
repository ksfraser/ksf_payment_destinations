# BR-PD-002 — Cash Sale Redirect

## Business Rule: Non-Cash Payment Redirect to Cash-Like Behavior

**Status:** Active  
**Owner:** ksf_payment_destinations module  
**Related Code:** `hooks.php:90-95` (cash_sale flag)

## Description

When a mapped non-cash payment term is detected on a direct invoice, the module must force the invoice to behave as a cash sale. This means setting `$cart->payment_terms['cash_sale'] = 1`, which instructs FrontAccounting to generate a payment record at the time the invoice is posted.

## Rationale

FA only generates payment records for cash sales at invoice time. Non-cash payments normally create accounts receivable entries requiring a separate payment allocation step. By forcing the cash-sale flag, the payment is immediately allocated against the mapped bank account, enabling automatic bank reconciliation.

## Rules

1. The redirect applies only when `$cart->payment_terms['cash_sale']` is falsy (i.e. the original term is non-cash).
2. Setting the flag must happen **after** the bank account has been remapped to the correct destination.
3. The redirect applies only to `ST_SALESINVOICE` transactions.
4. If the original payment term is already cash (`cash_sale` is truthy), no modification is needed (the bank account remapping still applies).

## Scope

- Affects only the in-memory cart object during `db_prewrite`.
- The original `payment_terms` configuration in the database is not modified.
- The redirect is session-scoped; it does not persist beyond the current transaction.
