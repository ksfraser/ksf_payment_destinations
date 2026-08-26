# UT-PD-001-003-001 — Payment Redirect

## Unit Test: db_prewrite Hook Payment Redirect

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `hooks.php:53-110` (db_prewrite)  
**Related FR:** FR-PD-001-003-payment-redirect.md, BR-PD-001-payment-routing.md, BR-PD-002-cash-sale-redirect.md

## Test Case: Mapped Term — Invoice Redirected

### Setup
- Mock `ksf_payment_destinations_model` to return `bank_account = 3` for `payment_term = 5`.
- Create `$cart` mock with `payment_terms['terms_indicator'] = 5`, `payment_terms['cash_sale'] = 0`, `pos['pos_account'] = 1`.
- Create `$trans_type = ST_SALESINVOICE`.

### Expected Output
- `$cart->pos['pos_account']` is changed to `3`.
- `$cart->payment_terms['cash_sale']` is changed to `1`.
- Hook returns `true`.

### Assertions
1. Bank account was remapped.
2. Cash sale flag was set.
3. Hook returned `true`.

---

## Test Case: Unmapped Term — No Change

### Setup
- Mock `ksf_payment_destinations_model` to throw `KSF_FIELD_NOT_SET` for `payment_term` not found.
- Create `$cart` mock with `payment_terms['terms_indicator'] = 99`.

### Expected Output
- `$cart->pos['pos_account']` is unchanged.
- `$cart->payment_terms['cash_sale']` is unchanged.
- Hook returns `true`.

### Assertions
1. Original POS account preserved.
2. Cash sale flag preserved.

---

## Test Case: Non-Invoice Transaction — No Change

### Setup
- `$trans_type = ST_SALESORDER`.

### Expected Output
- Hook returns without modifying the cart.
- Model is never instantiated.

### Assertions
1. Model constructor is not called.
2. Cart is untouched.

---

## Test Case: Payment Term Not Set on Model

### Setup
- Mock to throw `Exception("Payment Term not set")` (no `bank_account` in message).
- `$trans_type = ST_SALESINVOICE`.

### Expected Output
- `display_error()` is called with the exception message.

### Assertions
1. Error is displayed to the user.
