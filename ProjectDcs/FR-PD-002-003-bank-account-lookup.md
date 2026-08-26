# FR-PD-002-003 — Bank Account Lookup

## Functional Requirement: Look Up Bank Account from Payment Term

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:90-99` (getBankAccountFromTerm)  
**Related BR:** BR-PD-001-payment-routing.md, FR-PD-001-003-payment-redirect.md

## Description

Given a currently-set `payment_term`, query the mapping table to retrieve the corresponding `bank_account` ID. Used during the `db_prewrite` hook to resolve the destination bank account.

## Logic

1. Verify `$this->payment_term` is set; throw `Exception("Payment Term not set")` if not.
2. Execute SQL: `SELECT * FROM {tablename} WHERE payment_term = '{payment_term}'`.
3. Return `$this->data['bank_account']` (int).

## Acceptance Criteria

1. Returns the bank account ID (int) for the given payment term.
2. Throws `Exception("Payment Term not set")` if `payment_term` has not been set on the model.
3. Throws an exception (via parent `mysql_query`) if no row matches the term.
4. The query uses `table_interface->table_details['tablename']` for table name resolution.

## Notes

- The current SQL has a minor bug: missing space before `WHERE` (`...tablename] . "WHERE ...`). Should be `...tablename] . " WHERE ..."`.
- Consider using parameterized queries in the PSR-4 refactor to prevent SQL injection.
