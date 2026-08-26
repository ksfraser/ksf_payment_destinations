# FR-PD-002-001 — Insert Data

## Functional Requirement: Insert Payment Destination Mapping

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:111-128` (insert_data)  
**Related UC:** UC-PD-002-add-payment-mapping.md

## Description

When an admin submits the "Map the accounts" form, the controller calls `model->insert_data($_POST)` to persist the new payment-term-to-bank-account mapping.

## Logic

1. Receive `$arr` with keys `bank_account` (int ID) and `payment_term` (int ID).
2. Load `fa_bank_accounts` wrapper, set `id` to `$arr['bank_account']`, call `getById()`.
3. Populate `$arr['bank_account_name']` from `$ba->get('bank_account_name')`.
4. Load `fa_payment_terms` wrapper, set `terms_indicator` to `$arr['payment_term']`, call `getById()`.
5. Populate `$arr['payment_term_name']` from `$pt->get('terms')`.
6. Call `parent::insert_data($arr)` to execute the INSERT via `table_interface`.

## Acceptance Criteria

1. Both display names (`payment_term_name`, `bank_account_name`) are resolved and stored at insert time.
2. The record is persisted to `{TB_PREF}ksf_payment_destinations`.
3. Duplicate `payment_term` values are rejected (PK constraint).
4. If the bank account or payment term does not exist, the parent method throws an exception.
