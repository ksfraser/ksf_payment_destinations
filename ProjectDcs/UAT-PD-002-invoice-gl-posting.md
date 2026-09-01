# UAT-PD-002 — Invoice GL Posting

## User Acceptance Test: Invoice Payment Routing via GL

**Status:** Active
**Module:** ksf_payment_destinations
**Related UC:** UC-PD-002-add-payment-mapping.md, UC-PD-003-invoice-payment-routing.md
**Related BR:** BR-PD-001, BR-PD-002

## Preconditions

- FA is running at `http://localhost:8080` with ksf_payment_destinations module installed.
- FA is connected to a test company database with GL enabled.
- Payment terms:
  - "Dream CC" (`terms_indicator = 5`) — non-cash term
  - "Square" (`terms_indicator = 6`) — non-cash term
  - "Cheque" (`terms_indicator = 2`) — cash term
- Bank accounts:
  - "Dream Holdings" — account ID 3 (mapped to "Dream CC")
  - "Square Account" — account ID 7 (mapped to "Square")
  - "Main Cash" — account ID 1 (default POS account, unmapped)
- User has `SA_ksf_payment_destinations` permission.
- Chromium browser available.
- Database query access (e.g. phpMyAdmin or `mysql` CLI) for GL verification.

---

## UAT-PD-002-A: Mapped Non-Cash Term Posts to Mapped Account

**Objective:** Verify that an invoice with a mapped non-cash payment term routes its GL entry to the mapped bank account.

**Given:**
- "Dream CC" (terms_indicator = 5) is mapped to bank account "Dream Holdings" (ID 3)
- "Main Cash" (ID 1) is the default POS account
- A customer exists: "Test Customer"

**Steps (Browser/Chromium):**
1. Open FA → Sales → Direct Invoice
2. Select customer "Test Customer"
3. Add one line item (e.g. 1 × "Consulting" at $100)
4. Select payment term "Dream CC"
5. Click "Add" to post the invoice
6. Open phpMyAdmin or mysql CLI
7. Run:
   ```sql
   SELECT account_code, SUM(amount) as total
   FROM `{TB_PREF}gl_trans`
   WHERE account_code IN (1, 3)
     AND trandate = CURDATE()
   GROUP BY account_code;
   ```
8. Record which account has the non-zero total

**Expected Result:**
- Account 3 ("Dream Holdings") has a non-zero GL entry
- Account 1 ("Main Cash") has zero (or no row)
- Invoice total ($100) matches `SUM(amount)` for account 3

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

**Notes:**
- `cash_sale` flag should be 1 for this invoice (non-cash term mapped)
- AR entry for "Test Customer" should show invoice as paid (not outstanding)

---

## UAT-PD-002-B: Unmapped Cash Term Posts to Default Account

**Objective:** Verify that an unmapped cash payment term routes to the default POS account.

**Given:**
- "Cheque" (terms_indicator = 2) has NO mapping in `ksf_payment_destinations`
- "Main Cash" (ID 1) is the default POS account
- "Test Customer" exists

**Steps (Browser/Chromium):**
1. Open FA → Sales → Direct Invoice
2. Select customer "Test Customer"
3. Add one line item (e.g. 1 × "Consulting" at $100)
4. Select payment term "Cheque"
5. Click "Add" to post the invoice
6. Run GL query:
   ```sql
   SELECT account_code, SUM(amount) as total
   FROM `{TB_PREF}gl_trans`
   WHERE account_code IN (1, 3)
     AND trandate = CURDATE()
   GROUP BY account_code;
   ```

**Expected Result:**
- Account 1 ("Main Cash") has a non-zero GL entry
- Account 3 ("Dream Holdings") has zero (or no row)
- Invoice total ($100) matches `SUM(amount)` for account 1

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

**Notes:**
- No error should be thrown for unmapped term
- AR entry should show invoice as settled (not outstanding)

---

## UAT-PD-002-C: Multiple Mappings Route Independently

**Objective:** Verify that different mapped terms route to their respective bank accounts simultaneously.

**Given:**
- "Dream CC" → "Dream Holdings" (ID 3)
- "Square" → "Square Account" (ID 7)
- "Cheque" → no mapping (falls through to default ID 1)
- "Test Customer" exists

**Steps (Browser/Chromium):**
1. Invoice A: Customer "Test Customer", line item $100, term "Dream CC" → Add
2. Invoice B: Customer "Test Customer", line item $200, term "Square" → Add
3. Invoice C: Customer "Test Customer", line item $50, term "Cheque" → Add
4. Run GL query:
   ```sql
   SELECT account_code, SUM(amount) as total
   FROM `{TB_PREF}gl_trans`
   WHERE account_code IN (1, 3, 7)
     AND trandate = CURDATE()
   GROUP BY account_code;
   ```

**Expected Result:**
- Account 3: total = $100 (Dream CC only)
- Account 7: total = $200 (Square only)
- Account 1: total = $50 (Cheque — default)
- Each account has exactly its routed invoices

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-D: Non-Cash Mapped Term Sets cash_sale Flag

**Objective:** Verify that mapped non-cash terms result in immediate payment allocation (cash_sale behavior), not outstanding AR.

**Given:**
- "Dream CC" mapped to account 3 (non-cash term)
- "Test Customer" exists with AR account

**Steps (Browser/Chromium):**
1. Create invoice with "Dream CC" term, $100 total
2. Submit invoice
3. Go to Sales → Manage Payments (or AR → Customer Allocation)
4. Look for a payment record against the invoice

**Expected Result:**
- A payment/allocation record exists for the invoice
- Invoice is marked as settled, not outstanding
- `cash_sale` column in `{TB_PREF}sales_orders` or `{TB_PREF}debtor_trans` is set appropriately

**Verification Query:**
```sql
SELECT dt.type, dt.trans_no, dt.reference, dt.tran_date, dt.due_date,
       dt.total, dt.allocated, dt.debtor_no, dt.branch_id,
       sorder.reference AS so_ref
FROM `{TB_PREF}debtor_trans` dt
LEFT JOIN `{TB_PREF}sales_orders` sorder ON dt.order_ = sorder.order_no
WHERE dt.type = 10  -- ST_SALESINVOICE
  AND dt.tran_date = CURDATE()
ORDER BY dt.trans_no DESC
LIMIT 5;
```

Also check:
```sql
SELECT trans_no, type, cash_sale FROM `{TB_PREF}debtor_trans_details`
WHERE trans_no = (SELECT MAX(trans_no) FROM `{TB_PREF}debtor_trans` WHERE type = 10);
```

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-E: POS Invoice with Mapped Term Posts to Correct GL Account

**Objective:** Verify that POS (point of sale) invoices with mapped payment terms also route correctly.

**Given:**
- POS is enabled in FA (Point of Sales module)
- "Dream CC" is mapped to "Dream Holdings" (ID 3)
- POS terminal configured with default account "Main Cash" (ID 1)

**Steps (Browser/Chromium):**
1. Open FA → Point of Sale → New POS Invoice
2. Add items to cart
3. Select payment term "Dream CC"
4. Complete the sale
5. Query GL:
   ```sql
   SELECT account_code, SUM(amount) as total
   FROM `{TB_PREF}gl_trans`
   WHERE account_code IN (1, 3)
     AND trandate = CURDATE()
   GROUP BY account_code;
   ```

**Expected Result:**
- Account 3 has the non-zero GL entry (not account 1)
- Same routing behavior as direct invoice

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

**Notes:**
- POS invoices flow through the same `db_prewrite` hook
- This confirms the routing logic works across invoice types

---

## UAT-PD-002-F: Zero-Amount Invoice with Mapped Term Skips Routing

**Objective:** Verify that zero-amount invoices (free items, fully credited) do not produce GL entries and do not error.

**Given:**
- "Dream CC" mapped to account 3
- Customer "Test Customer" exists

**Steps (Browser/Chromium):**
1. Create direct invoice for $0 (e.g. "Free Sample" item with $0 price)
2. Select payment term "Dream CC"
3. Submit invoice
4. Run GL query:
   ```sql
   SELECT COUNT(*) as entries
   FROM `{TB_PREF}gl_trans`
   WHERE trandate = CURDATE()
     AND account_code IN (1, 3);
   ```

**Expected Result:**
- Zero rows in GL for this invoice (no GL entries for $0 invoice)
- No error thrown

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-G: GL Audit Trail — Full Invoice Lifecycle

**Objective:** End-to-end audit trail from invoice creation through GL posting for a mapped term.

**Given:**
- "Dream CC" mapped to "Dream Holdings" (ID 3)
- Customer "Test Customer" (debtor_no = 1, branch_id = 1)
- Item "Consulting" (stock_id = 1, unit_price = 150)

**Steps (Browser/Chromium + SQL):**
1. Create and post invoice with "Dream CC", $150
2. Record the `trans_no` from the confirmation message
3. Run audit trail query:
   ```sql
   -- Debtor trans (invoice)
   SELECT * FROM `{TB_PREF}debtor_trans`
   WHERE type = 10 AND trans_no = @inv_no;

   -- Debtor trans details (line items)
   SELECT * FROM `{TB_PREF}debtor_trans_details`
   WHERE debtor_trans_type = 10 AND debtor_trans_no = @inv_no;

   -- GL entries
   SELECT * FROM `{TB_PREF}gl_trans`
   WHERE type = 10 AND type_no = @inv_no
   ORDER BY account_code;

   -- Bank account transaction
   SELECT * FROM `{TB_PREF}bank_trans`
   WHERE type = 10 AND type_no = @inv_no;
   ```

**Expected Result:**
| Table | Field | Expected Value |
|---|---|---|
| `debtor_trans` | total | 150 |
| `debtor_trans` | allocated | 150 |
| `debtor_trans` | cash_sale | 1 |
| `gl_trans` | account_code | 3 ("Dream Holdings") |
| `gl_trans` | amount | 150 |
| `bank_trans` | account | 3 |
| `bank_trans` | amount | 150 |

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-H: Unmapped Term With Zero Mapping Returns Default

**Objective:** If `getBankAccountFromTerm()` returns 0 (term not mapped), verify routing falls through to default.

**Steps (Browser/Chromium):**
1. Verify "Cheque" has no mapping
2. Create invoice with "Cheque", $75
3. Run GL query

**Expected Result:**
- Account 1 ("Main Cash") receives the GL entry
- No errors

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-I: Delete Mapping — Next Invoice Falls Through to Default

**Objective:** After deleting a mapping, invoices using that term should fall through to default.

**Given:**
- "Dream CC" → "Dream Holdings" (ID 3) mapping exists
- Default account = "Main Cash" (ID 1)

**Steps (Browser/Chromium):**
1. Create Invoice A with "Dream CC" → verify posts to account 3
2. Go to GL → orders → ksf_payment_destinations → Setup Payment Destination Mapping
3. Click Delete (X) on the "Dream CC" row
4. Switch tab and return
5. Create Invoice B with "Dream CC" → verify posts to account 1 (default)

**Expected Result:**
- Invoice A posts to account 3
- Invoice B posts to account 1 (no mapping = default)

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked

---

## UAT-PD-002-J: Delete Mapping — Does Not Affect Existing Posted Invoices

**Objective:** Deleting a mapping must not retroactively change GL entries for already-posted invoices.

**Steps (Browser/Chromium + SQL):**
1. Create and post Invoice A with "Dream CC" → posts to account 3
2. Note the GL entries for Invoice A (read-only, already settled)
3. Delete the "Dream CC" mapping
4. Query GL for the original Invoice A trans_no:
   ```sql
   SELECT account_code, amount FROM `{TB_PREF}gl_trans`
   WHERE type = 10 AND type_no = @inv_no_a;
   ```

**Expected Result:**
- Original GL entries for Invoice A are unchanged (account 3, amount preserved)
- Deleting mapping has no retroactive effect

**Results:** [ ] Pass  [ ] Fail  [ ] Blocked
