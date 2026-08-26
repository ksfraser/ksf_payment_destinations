# FR-PD-001-001 — Mapping UI

## Functional Requirement: Payment Destination Mapping Interface

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_view.php:259-268` (master_form), `:179-207` (form_add), `:208-233` (edit_item_form)  
**Related UC:** UC-PD-002-add-payment-mapping.md

## Description

Provide an admin UI tab ("Setup Payment Destination Mapping") that displays existing payment-term-to-bank-account mappings in a table with edit/delete controls, and a form to add new mappings.

## UI Components

### Tab Navigation
- **Module How-To** — usage documentation and known bugs
- **Configuration** — module config (debug level)
- **Setup Payment Destination Mapping** — primary mapping management

### Mapping Table (`item_form`)
- Columns: Payment Terms, Bank Account, Edit button, Delete button
- Data source: `model->getAll()` query on `pref_ksf_payment_destinations`
- Row color alternation via `alt_table_row_color()`

### Add Form (`form_add`)
- Combo box: `sale_payment_list()` for payment term selection
- Combo box: `bank_accounts_list()` for bank account selection
- Submit button labeled "Map the accounts"

### Edit Form (`edit_item_form`)
- Pre-populated combo boxes for the selected mapping
- Hidden fields: `action`, `time`, `ksf_payment_destinations`
- Submit button labeled "Update"

## Acceptance Criteria

1. Admin can view all existing mappings in tabular form.
2. Admin can add a new mapping by selecting a payment term and bank account.
3. Admin can delete a mapping via the delete button.
4. Combo boxes only show active (non-inactive) bank accounts and payment terms.
5. After add/delete, the mapping table reflects the change.
