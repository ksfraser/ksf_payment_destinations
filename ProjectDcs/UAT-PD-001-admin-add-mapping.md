# UAT-PD-001 — Admin Add Mapping

## User Acceptance Test: End-to-End Mapping Creation

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related UC:** UC-PD-002-add-payment-mapping.md

## Preconditions

- FA is running with ksf_payment_destinations module installed.
- Test payment term exists (e.g. "Dream CC" with `terms_indicator = 5`).
- Test bank account exists (e.g. "Dream Holdings" with `id = 3`).
- User has `SA_ksf_payment_destinations` permission.

## Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to GL > orders > ksf_payment_destinations | Module loads, tabs visible |
| 2 | Click "Setup Payment Destination Mapping" tab | Mapping table displayed (may be empty) |
| 3 | Select "Dream CC" from Payment Terms combo | Value selected |
| 4 | Select "Dream Holdings" from Bank Account combo | Value selected |
| 5 | Click "Map the accounts" button | Page reloads, mapping table shows new row |
| 6 | Verify row shows "Dream CC" and "Dream Holdings" | Display names match selections |
| 7 | Create a direct invoice with "Dream CC" payment term | Invoice posts to Dream Holdings account |
| 8 | Verify GL ledger shows the payment in Dream Holdings | Bank account correct |

## Cleanup

- Delete the test mapping from the UI.
- Verify the mapping no longer appears in the table.
