# UT-PD-001-001-001 — Add Mapping

## Unit Test: Insert Payment Destination Mapping

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:111-128` (insert_data)  
**Related FR:** FR-PD-002-001-insert-data.md  
**Test File:** `Tests/Unit/PaymentDestinationModelTest.php`

## Test Case: Happy Path — Insert New Mapping

### Setup
- Mock `fa_bank_accounts` to return `bank_account_name = "Dream Holdings"` for `id = 3`.
- Mock `fa_payment_terms` to return `terms = "Dream CC"` for `terms_indicator = 5`.
- Mock parent `insert_data` to capture the passed array.

### Input
```php
['bank_account' => 3, 'payment_term' => 5]
```

### Expected Output
```php
['bank_account' => 3, 'bank_account_name' => 'Dream Holdings',
 'payment_term' => 5, 'payment_term_name' => 'Dream CC']
```

### Assertions
1. `bank_account_name` is resolved and included in the data array.
2. `payment_term_name` is resolved and included in the data array.
3. Parent `insert_data` is called once with the enriched array.

---

## Test Case: Bank Account Not Found

### Setup
- Mock `fa_bank_accounts` to throw on `getById()`.

### Input
```php
['bank_account' => 999, 'payment_term' => 5]
```

### Expected Output
- Exception thrown from `fa_bank_accounts`.

### Assertions
1. Exception is thrown before `parent::insert_data` is called.
