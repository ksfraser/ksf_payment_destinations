# FR-PD-001-003 — Payment Redirect

## Functional Requirement: db_prewrite Hook Intercepts Invoices

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `hooks.php:53-110` (db_prewrite)  
**Related BR:** BR-PD-001-payment-routing.md, BR-PD-002-cash-sale-redirect.md

## Description

The `db_prewrite` hook on `hooks_ksf_payment_destinations` intercepts sales invoice transactions before they are committed to the database, and redirects the payment to the mapped bank account.

## Trigger

- FA calls `hook_db_prewrite(&$cart, $trans_type)` before writing any sales transaction.
- The hook checks `$trans_type === ST_SALESINVOICE`.

## Logic

1. Load `ksf_payment_destinations_model`.
2. Set `payment_term` from `$cart->payment_terms['terms_indicator']`.
3. Call `model->select_row()` to look up the mapping.
4. If found:
   - Save original `$cart->pos['pos_account']`.
   - Set `$cart->pos['pos_account']` to the mapped `bank_account`.
   - If `$cart->payment_terms['cash_sale']` is falsy, set it to `1`.
5. If not found (exception `KSF_FIELD_NOT_SET`):
   - If the missing field is `bank_account`, return `true` (no redirect, allow original behavior).
   - Otherwise, display error.
6. Return `true` to FA to continue processing.

## Acceptance Criteria

1. When a mapped payment term is used on a direct invoice, the payment is posted to the mapped bank account.
2. When an unmapped payment term is used, the payment uses the default POS account.
3. Non-cash mapped payments generate a payment record at invoice time (cash_sale flag set).
4. Only `ST_SALESINVOICE` transactions are affected.
5. Other transaction types (`ST_SALESORDER`, `ST_CUSTDELIVERY`, etc.) pass through unmodified.
