# UAT-PD-001 — Admin Add Mapping

## User Acceptance Test: End-to-End Mapping Creation

**Status:** Active
**Module:** ksf_payment_destinations
**Related UC:** UC-PD-002-add-payment-mapping.md

## Preconditions

- FA is running at `http://localhost:8080` (or appropriate port) with ksf_payment_destinations module installed.
- Test payment term exists (e.g. "Dream CC" with `terms_indicator = 5`).
- Test bank account exists (e.g. "Dream Holdings" with `id = 3`).
- User has `SA_ksf_payment_destinations` permission.
- Chromium browser available for UI verification.
- Two distinct bank accounts configured in FA (for routing comparison).

---

## UAT-PD-001-A: Add Mapping via UI

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to GL > orders > ksf_payment_destinations | Module loads, tabs visible |
| 2 | Click "Setup Payment Destination Mapping" tab | Mapping table displayed (may be empty) |
| 3 | Select "Dream CC" from Payment Terms combo | Value selected |
| 4 | Select "Dream Holdings" from Bank Account combo | Value selected |
| 5 | Click "Map the accounts" button | Page reloads, mapping table shows new row |
| 6 | Verify row shows "Dream CC" and "Dream Holdings" | Display names match selections |

**Given:** FA running at localhost:8080, module installed, payment term "Dream CC" and bank account "Dream Holdings" exist.

**Steps (Browser/Chromium):**
1. Open browser → `http://localhost:8080`
2. Login as admin
3. GL → orders → ksf_payment_destinations
4. Click "Setup Payment Destination Mapping" tab
5. Select "Dream CC" from Payment Terms
6. Select "Dream Holdings" from Bank Account
7. Click "Map the accounts"
8. Verify new row appears in table

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

**Cleanup:** Delete the test mapping from the UI.

---

## UAT-PD-001-B: Invoice GL Posting — Mapped Term Routes Correctly

**Given:** Payment term "Dream CC" mapped to bank account "Dream Holdings" (account ID 3). Default POS account is "Main Cash" (account ID 1).

**Steps (Browser/Chromium):**
1. Open FA → Sales → Direct Invoice
2. Select customer, add one line item
3. Select payment term "Dream CC"
4. Submit invoice
5. Immediately go to GL → Bank Account Transactions
6. Filter by "Dream Holdings" (account ID 3)
7. Verify the invoice payment is posted to account 3 (not account 1)

**Expected Result:** GL entry for the invoice payment appears in "Dream Holdings" (ID 3), NOT in the default "Main Cash" (ID 1).

**Verification Query:**
```sql
SELECT account_code, SUM(amount) as total
FROM `{TB_PREF}gl_trans`
WHERE account_code IN (1, 3)
  AND trandate = CURDATE()
GROUP BY account_code;
```
Expected: account 3 has non-zero total, account 1 has zero.

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-001-C: Invoice GL Posting — Unmapped Term Falls Through

**Given:** No mapping exists for payment term "Cheque" (terms_indicator = 2). Default POS account is "Main Cash" (account ID 1).

**Steps (Browser/Chromium):**
1. Create direct invoice with payment term "Cheque"
2. Submit invoice
3. Go to GL → Bank Account Transactions
4. Verify payment is posted to default "Main Cash" (account ID 1)

**Expected Result:** GL entry appears in account 1 (default), no error shown.

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-001-D: Multiple Mappings — Each Routes Independently

**Given:** Mappings: "Dream CC" → Dream Holdings (3), "Square" → Square Account (7), "Cheque" → no mapping (falls through to default 1).

**Steps (Browser/Chromium):**
1. Create Invoice A with "Dream CC" → verify GL posts to account 3
2. Create Invoice B with "Square" → verify GL posts to account 7
3. Create Invoice C with "Cheque" → verify GL posts to default account 1

**Expected Result:** Each invoice routes to its mapped account; unmapped term uses default.

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-001-E: cash_sale Flag Set on Mapped Non-Cash Term

**Given:** "Dream CC" is a non-cash payment term (cash_sale = 0 in payment_terms table). Mapping exists to account 3.

**Steps (Browser/Chromium):**
1. Create direct invoice with payment term "Dream CC"
2. Submit invoice
3. Go to Sales → Manage Payments (or AR allocation screen)
4. Verify a payment record exists against the invoice (not just an AR entry)

**Expected Result:** Invoice generates a payment allocation immediately (cash_sale behavior), not just an outstanding AR balance.

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-001-F: Known Bug Verification — Edit Button

**Given:** A mapping exists in the table.

**Steps (Browser/Chromium):**
1. Click the Edit (pencil) button on a mapping row
2. Observe whether an edit form loads

**Expected Result:** Per known bug FR-PD-001-001-mapping-ui.md — edit form does NOT load (known bug). If it DOES load, bug is fixed.

**Results:** [ ] Bug confirmed (edit fails)  [ ] Bug fixed (edit works)  [ ] Blocked

---

## UAT-PD-001-G: Known Bug Verification — Delete Button

**Given:** A mapping exists in the table.

**Steps (Browser/Chromium):**
1. Click the Delete (X) button on a mapping row
2. Switch to a different tab, then return to "Setup Payment Destination Mapping"
3. Observe whether the row is deleted

**Expected Result:** Per known bug — row is deleted but table does not refresh automatically. Tab switch required to see removal.

**Results:** [ ] Bug confirmed (no refresh)  [ ] Bug fixed (table auto-refreshes)  [ ] Blocked
