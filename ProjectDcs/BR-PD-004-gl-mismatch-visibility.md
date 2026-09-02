# BR-PD-004 — GL Mismatch Visibility

## Business Rule: GL Account Mismatch Advisory in Import Review

**Status:** Active
**Owner:** ksf_payment_destinations module / ksf_FA_ISU
**Related Code:** ISU `class.ksf_import_square.php` (match display), `class.ksf_import_square_transactions_model.php` (match queries)

## Description

When staged transactions (from Square, WooCommerce, or other importers) are matched to existing FrontAccounting transactions during the ISU review/match phase, the system SHALL compare the GL accounts. If the GL account on the staged transaction (from the importing module's config, e.g., `square_gl`) differs from the GL account on the existing FA transaction (potentially routed by this module's PaymentDestinations mapping), the mismatch SHALL be flagged as an advisory warning in the review UI.

This is an advisory only — it does not block processing. It provides visibility into reconciliation discrepancies.

## Rationale

PaymentDestinations routes GL postings based on payment terms. Importing modules have their own GL config (e.g., `square_gl`). If these differ for the same transaction, the mismatch indicates a reconciliation issue that needs human review. Without visibility, mismatches go undetected until manual reconciliation.

## Rules

1. ISU's `findMatchingTransactions()` and related match methods compare GL accounts between staged and matched FA transactions.
2. The staged GL comes from the importing module's config (e.g., Square's `square_gl` setting).
3. The FA GL comes from `gl_trans` for the matched transaction.
4. Mismatch is displayed as a visual warning (icon/text) on the match row.
5. Mismatch is advisory only — does not block processing.
6. This module does NOT participate directly in mismatch detection; the comparison is performed by ISU using data from both this module's routing and the importing module's config.

## Scope

- Applies only during ISU staged-transaction review phase.
- Applies only when importing transactions that may have been routed by PaymentDestinations.
- Applies only to transactions with existing matches in FA (not new/staged-only transactions).

## Exception Handling

- If ISU is not installed, this rule is not applicable.
- If the matched FA transaction has no routed GL (unmapped term), no mismatch check is performed.
- If the importing module has no GL config, no mismatch check is performed.
