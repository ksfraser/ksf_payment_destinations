# UC-PD-002 — Add Payment Mapping

## Use Case: Admin Adds a Payment-to-Bank Mapping

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_view.php:179-207` (form_add), `class.ksf_payment_destinations_model.php:111-128` (insert_data)  
**Related FR:** FR-PD-001-001-mapping-ui.md, FR-PD-002-001-insert-data.md

## Actors

- **Admin** — user with `SA_ksf_payment_destinations` access permission.

## Preconditions

- Module is installed.
- At least one payment term exists in FA.
- At least one bank account exists in FA.

## Main Flow

1. Admin navigates to **GL > orders > ksf_payment_destinations**.
2. Admin clicks the **Setup Payment Destination Mapping** tab.
3. Admin sees the current list of mappings (or empty table).
4. Admin selects a payment term from the combo box.
5. Admin selects a bank account from the combo box.
6. Admin clicks **Map the accounts**.
7. System resolves display names for both the term and account.
8. System inserts the mapping into `pref_ksf_payment_destinations`.
9. System displays the updated mapping list.

## Alternative Flows

### 4a. Payment term already mapped (duplicate PK)
- System rejects the insert (PK constraint).
- Admin is notified and must delete the existing mapping first.

## Postconditions

- The new mapping is active.
- Future direct invoices using this payment term will be routed to the mapped bank account.
