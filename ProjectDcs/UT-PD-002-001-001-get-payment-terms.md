# UT-PD-002-001-001 — Get Payment Terms

## Unit Test: Retrieve Payment Terms List

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:77-89` (getPaymentTerms)  
**Related FR:** FR-PD-002-002-get-payment-terms.md

## Test Case: Returns Active Terms

### Setup
- Mock FA's `get_payment_terms(false)` to return a result set with 3 rows.

### Expected Output
- `model->getPaymentTerms()` returns an array of 3 elements.

### Assertions
1. Return value is an array.
2. Array count equals 3.
3. `get_payment_terms` was called with `$show_inactive = false`.

---

## Test Case: Returns Empty Array When No Terms Exist

### Setup
- Mock FA's `get_payment_terms(false)` to return an empty result set.

### Expected Output
- `model->getPaymentTerms()` returns an empty array `[]`.

### Assertions
1. Return value is an array.
2. Array count equals 0.
