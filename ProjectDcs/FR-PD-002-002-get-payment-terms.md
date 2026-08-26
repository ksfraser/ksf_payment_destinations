# FR-PD-002-002 — Get Payment Terms

## Functional Requirement: Retrieve Payment Terms List

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_model.php:77-89` (getPaymentTerms)  
**Related BR:** BR-PD-001-payment-routing.md

## Description

Provide a method to retrieve all active payment terms from FrontAccounting's `payment_terms` table. Used to populate the combo box in the mapping UI.

## Logic

1. Call FA's built-in `get_payment_terms($show_inactive = false)`.
2. Fetch all rows from the result set.
3. Return an array of payment term records.

## Acceptance Criteria

1. Only active payment terms are returned (`$show_inactive = false`).
2. Returns an array of associative arrays, one per payment term.
3. Returns an empty array if no active terms exist.
