# FR-PD-001-002 — Table Definition

## Functional Requirement: Database Table Schema

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:100-110` (define_table)  
**Related UT:** UT-PD-001-002-001-define-table.md

## Table Name

`{TB_PREF}ksf_payment_destinations`

## Schema

| Column | SQL Type | Null | Default | Description |
|---|---|---|---|---|
| `payment_term` | `int(11)` | NOT NULL | 0 | Primary key. FK to `{TB_PREF}payment_terms.terms_indicator` |
| `payment_term_name` | `varchar(DESCRIPTION_LENGTH)` | NOT NULL | 0 | Denormalized display name of the payment term |
| `bank_account` | `int(11)` | NOT NULL | 0 | FK to `{TB_PREF}bank_accounts.id` |
| `bank_account_name` | `varchar(DESCRIPTION_LENGTH)` | NOT NULL | 0 | Denormalized display name of the bank account |

## Constraints

- **Primary Key:** `payment_term`
- **Unique:** A payment term can appear only once (enforced by PK).
- **Referential integrity:** Enforced at the application level (not DB-level FK constraints), since FA modules typically run on MySQL without FK enforcement.

## Definition Method

Table is defined declaratively via `table_interface`:
```php
$this->table_interface->table_details['tablename'] = TB_PREF . 'ksf_payment_destinations';
$this->table_interface->table_details['primarykey'] = "payment_term";
$this->table_interface->fields_array[] = array(/* ... */);
```

Creation happens in `model->create_table()` called from `controller->install()`.
