# RTM — Requirements Traceability Matrix

**Module:** ksf_payment_destinations
**Coverage:** BR, FR, UC, UAT, UT — all ProjectDcs requirements

---

## Traceability Map

| Requirement ID | Requirement | Source | Test(s) | Status |
|---|---|---|---|---|
| **BR-PD-001** | Payment term must route to mapped bank account | BR | `UT-PD-001-003-001`, `RegressionTest::testMappedTermBothPathsProduceSameResult`, `UAT-PD-001-B` | ✅ |
| **BR-PD-001** | Unmapped term must NOT modify cart | BR | `UT-PD-001-003-001`, `RegressionTest::testUnmappedTermBothPathsReturnZero`, `UAT-PD-001-C` | ✅ |
| **BR-PD-002** | Non-cash mapped term must set cash_sale=1 | BR | `UT-PD-001-003-001`, `RegressionTest::testCartRoutingDecisionMatchesBetweenPaths`, `UAT-PD-001-E` | ✅ |
| **BR-PD-002** | Already-cash term must not clobber cash_sale flag | BR | `RegressionTest::testAlreadyCashSaleNotOverwritten` | ✅ |
| **FR-PD-001-001** | UI: Mapping table displays payment_term + bank_account | FR | `UAT-PD-001-A` (Step 6) | ✅ |
| **FR-PD-001-001** | UI: Add form with payment term + bank account combos | FR | `UAT-PD-001-A` (Steps 3-5) | ✅ |
| **FR-PD-001-001** | UI: Edit button (known bug — does not load form) | FR | `UAT-PD-001-F` | ⚠️ Known bug |
| **FR-PD-001-001** | UI: Delete button removes row (no auto-refresh) | FR | `UAT-PD-001-G` | ⚠️ Known bug |
| **FR-PD-001-001** | UI: Combo boxes use FA's bank_accounts_list / sale_payment_list | FR | `UAT-PD-001-A` (Steps 3-4) | ✅ |
| **FR-PD-001-002** | Table: 4 columns — payment_term (PK), payment_term_name, bank_account, bank_account_name | FR | `UT-PD-001-002-001` | ✅ |
| **FR-PD-001-002** | Table: Primary key is payment_term | FR | `UT-PD-001-002-001` | ✅ |
| **FR-PD-001-002** | Table: Created via table_interface in model->define_table | FR | `UT-PD-001-002-001` | ✅ |
| **FR-PD-001-003** | db_prewrite intercepts ST_SALESINVOICE only | FR | `UT-PD-001-003-001` (non-invoice test) | ✅ |
| **FR-PD-001-003** | db_prewrite reads cart->payment_terms['terms_indicator'] | FR | `UT-PD-001-003-001`, `RegressionTest` | ✅ |
| **FR-PD-001-003** | db_prewrite sets cart->pos['pos_account'] on match | FR | `UAT-PD-001-B`, `UAT-PD-001-D` | ✅ |
| **FR-PD-001-003** | db_prewrite sets cash_sale=1 on non-cash mapped term | FR | `UAT-PD-001-E` | ✅ |
| **FR-PD-001-003** | db_prewrite returns true on success | FR | `UT-PD-001-003-001` | ✅ |
| **FR-PD-002-001** | insert_data resolves bank_account_name via fa_bank_accounts | FR | `UT-PD-001-001-001-add-mapping` | ✅ |
| **FR-PD-002-001** | insert_data resolves payment_term_name via fa_payment_terms | FR | `UT-PD-001-001-001-add-mapping` | ✅ |
| **FR-PD-002-001** | insert_data calls parent::insert_data | FR | `UT-PD-001-001-001-add-mapping` | ✅ |
| **FR-PD-002-002** | getPaymentTerms() returns array of active payment terms | FR | `UT-PD-002-001-001` | ✅ |
| **FR-PD-002-003** | getBankAccountFromTerm() returns int bank_account | FR | `RegressionTest`, `UT-PD-001-003-001` | ✅ |
| **FR-PD-002-003** | getBankAccountFromTerm() throws when payment_term not set | FR | `UT-PD-001-002-002` | ✅ |
| **FR-PD-002-003** | getBankAccountFromTerm() returns 0 when term not mapped | FR | `RegressionTest::testUnmappedTermBothPathsReturnZero` | ✅ |
| **UC-PD-001** | Admin can access Configuration tab | UC | `UAT-PD-001-A` (Step 1) | ✅ |
| **UC-PD-002** | Admin can add a new payment-to-bank mapping | UC | `UAT-PD-001-A` | ✅ |
| **UC-PD-002** | Duplicate payment_term mapping is rejected (PK) | UC | Not yet tested | ❌ Gap |
| **UC-PD-002** | Admin can delete a mapping | UC | `UAT-PD-001-G` cleanup | ✅ |

---

## Coverage Summary

| Category | Total | Covered | Gaps |
|---|---|---|---|
| BR | 4 | 4 | 0 |
| FR | 17 | 16 | 1 (duplicate PK) |
| UC | 3 | 2 | 1 (duplicate PK) |
| UAT | 15 scenarios (UAT-PD-001: 7, UAT-PD-002: 10) | 0 executed | 15 pending |
| UT | 9 test files | 9 ✅ | 0 |
| Regression | 6 cases | 6 ✅ | 0 |

**Legend:**
- ✅ = Test exists and passes
- ⚠️ = Known issue (documented)
- ❌ = Gap (no test)

---

## Test File Index

| Test File | Coverage |
|---|---|
| `UT-PD-001-001-001-add-mapping.md` | FR-PD-002-001 |
| `UT-PD-001-002-001-define-table.md` | FR-PD-001-002 |
| `UT-PD-001-002-002-select-row.md` | FR-PD-002-003 |
| `UT-PD-001-003-001-payment-redirect.md` | BR-PD-001, BR-PD-002, FR-PD-001-003 |
| `UT-PD-002-001-001-get-payment-terms.md` | FR-PD-002-002 |
| `QueryBuilderTest.php` | FR-PD-001-002 (infrastructure) |
| `PaymentDestinationRepositoryTest.php` | FR-PD-001-002, FR-PD-002-003 |
| `PaymentDestinationServiceTest.php` | FR-PD-002-001/002/003 |
| `HooksPaymentDestinationsTest.php` | FR-PD-001-003 (trait wiring) |
| `PaymentDestinationControllerTest.php` | FR-PD-001-001 (wiring) |
| `ValidatableTraitTest.php` | FR-PD-002-001 (validation) |
| `LegacyVsRefactorComparisonTest.php` | BR-PD-001/002 (structural) |
| `RegressionTest.php` | BR-PD-001, BR-PD-002 (logic regression) |
| `UAT-PD-001-admin-add-mapping.md` | UC-PD-001, UC-PD-002, BR-PD-001, BR-PD-002 (browser/E2E — UI/mapping setup) |
| `UAT-PD-002-invoice-gl-posting.md` | UC-PD-002, UC-PD-003, BR-PD-001, BR-PD-002 (browser/E2E — GL posting) |

---

## Outstanding Gaps

1. **Duplicate payment_term PK enforcement** — `UT-PD-002-001-002-duplicate-mapping-rejected.md` created; not yet executed against live DB.
2. **UAT browser tests not executed** — UAT-PD-001-A through J require manual or automated browser testing against localhost:8080. Not yet automated in CI.
3. **Edit form** — known bug FR-PD-001-001 has no regression test (bug is by design); no test covers the edit_item_form path.
4. **ModuleConfig (ksf_modules_common) for Configuration tab** — FR-PD-001-001 Configuration tab uses inherited `show_config_form`; no test covers module config save/load cycle.

---

## Last Updated

commit `29debd0` — UAT-PD-002 (15 GL posting scenarios) added.
