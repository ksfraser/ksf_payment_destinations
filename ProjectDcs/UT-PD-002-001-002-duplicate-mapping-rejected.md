# UT-PD-002-001-002 — Duplicate Mapping Rejection

## Unit Test: Duplicate payment_term Primary Key is Rejected

**Status:** Active
**Module:** ksf_payment_destinations
**Related Code:** `class.ksf_payment_destinations_model.php:111-128` (insert_data)
**Related FR:** FR-PD-002-001-insert-data.md
**Related UC:** UC-PD-002-add-payment-mapping.md
**Test File:** `Tests/Unit/PaymentDestinationRepositoryTest.php`

## Test Case: Duplicate payment_term Insert Throws or Returns False

### Setup
- Mock repository to simulate DB-level PK constraint violation.
- First insert for `payment_term = 5` succeeds.
- Second insert for `payment_term = 5` should be rejected.

### Input
```php
$repo->insert(['payment_term' => 5, 'bank_account' => 3]);
$repo->insert(['payment_term' => 5, 'bank_account' => 7]); // duplicate PK
```

### Expected Output
- First insert returns `true`.
- Second insert returns `false` or throws `Exception` (PDO integrity constraint).

### Assertions
1. First insert returns `true`.
2. Second insert returns `false` or throws.

---

## Test Case: Same payment_term Different bank_account is Still Duplicate

### Input
```php
$repo->insert(['payment_term' => 5, 'bank_account' => 3]);
$repo->insert(['payment_term' => 5, 'bank_account' => 99]);
```

### Expected Output
- Second insert rejected (PK is `payment_term`, not composite).

### Assertions
1. Only one row exists for `payment_term = 5` after both inserts.
