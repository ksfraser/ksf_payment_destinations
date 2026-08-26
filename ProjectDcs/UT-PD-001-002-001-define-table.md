# UT-PD-001-002-001 — Define Table

## Unit Test: Table Definition via table_interface

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:100-110` (define_table)  
**Related FR:** FR-PD-001-002-table-definition.md  
**Test File:** `Tests/Unit/PaymentDestinationInterfaceTest.php`

## Test Case: Table Name Is Correct

### Setup
- Instantiate `ksf_payment_destinations_model` with mocked dependencies.
- Requires mocking `table_interface` constructor expectations.

### Expected Output
- `$model->table_interface->table_details['tablename']` equals `{TB_PREF}ksf_payment_destinations`.

### Assertions
1. Table name starts with the FA table prefix.
2. Table name ends with `ksf_payment_destinations`.

---

## Test Case: Primary Key Is payment_term

### Expected Output
- `$model->table_interface->table_details['primarykey']` equals `"payment_term"`.

### Assertions
1. Primary key is set to the string `"payment_term"`.

---

## Test Case: Four Fields Defined

### Expected Output
- `$model->table_interface->fields_array` contains exactly 4 entries.

### Assertions
1. Field names are: `payment_term`, `payment_term_name`, `bank_account`, `bank_account_name`.
2. `payment_term` type is `int(11)`.
3. `bank_account` type is `int(11)`.
4. `payment_term_name` and `bank_account_name` are `varchar(DESCRIPTION_LENGTH)`.
5. All fields have `null => 'NOT NULL'`.
6. All fields have `readwrite => 'readwrite'`.
