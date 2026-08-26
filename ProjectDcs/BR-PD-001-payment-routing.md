# BR-PD-001 — Payment Routing

## Business Rule: Payment Routing to Destination Bank Account

**Status:** Active  
**Owner:** ksf_payment_destinations module  
**Related Code:** `hooks.php:53-110` (db_prewrite), `class.ksf_payment_destinations_model.php:90-99` (getBankAccountFromTerm)

## Description

When a sales invoice (`ST_SALESINVOICE`) is submitted, the system must route the payment to the bank account configured for that invoice's payment term type. Each payment term (e.g. credit card processor, Dream, cheque) is mapped to a specific destination bank account (e.g. cash drawer, Dream holdings account).

## Rationale

The business tracks cash flow across multiple credit card processors and payment methods. Without this routing, all payments would default to the POS default bank account, making it impossible to reconcile which processor received which funds.

## Rules

1. The mapping table (`pref_ksf_payment_destinations`) is the single source of truth for payment-term-to-bank-account routing.
2. Each payment term may map to exactly one bank account (1:1).
3. Multiple payment terms may share the same bank account (N:1).
4. The primary key is `payment_term` (the FA `terms_indicator` value).
5. The bank account must exist in FA's `bank_accounts` table.
6. The payment term must exist in FA's `payment_terms` table.

## Scope

- Applies only to direct invoices (`ST_SALESINVOICE`).
- Does not affect sales orders (`ST_SALESORDER`), deliveries (`ST_CUSTDELIVERY`), or other transaction types.
- If no mapping exists for a given payment term, the system falls through with no modification to the cart (original POS account is used).

## Exception Handling

- If `payment_term` is not set on the model when `getBankAccountFromTerm()` is called, throw `Exception("Payment Term not set")`.
- If no mapping row is found for the term, the exception with code `KSF_FIELD_NOT_SET` is caught and the hook returns `true` without modifying the cart.
