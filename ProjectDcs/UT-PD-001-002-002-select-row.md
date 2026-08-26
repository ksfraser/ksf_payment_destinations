# UT-PD-001-002-002 — Select Row

## Unit Test: Select Row by Primary Key

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** inherited `select_row()` from `generic_fa_interface_model`  
**Related FR:** FR-PD-002-003-bank-account-lookup.md

## Test Case: Select Existing Row

### Setup
- Mock the database layer to return a row for `payment_term = 5`.
- Set `$model->payment_term = 5`.

### Expected Output
- `$model->select_row()` succeeds.
- `$model->get('bank_account')` returns the mapped bank account ID.

### Assertions
1. `select_row()` does not throw.
2. `get('bank_account')` returns the correct value from the mocked row.

---

## Test Case: Select Non-Existent Row

### Setup
- Mock the database layer to return empty result for `payment_term = 999`.

### Expected Output
- `$model->select_row()` throws an exception.

### Assertions
1. Exception is thrown with appropriate message/code.

---

## Test Case: Payment Term Not Set

### Setup
- Do not set `$model->payment_term`.

### Expected Output
- `$model->getBankAccountFromTerm()` throws `Exception("Payment Term not set")`.

### Assertions
1. Exception message equals `"Payment Term not set"`.
