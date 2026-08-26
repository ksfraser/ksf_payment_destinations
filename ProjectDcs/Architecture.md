# Architecture — ksf_payment_destinations

## Overview

A FrontAccounting (FA) module that maps **payment terms** (e.g. credit card type, cheque, Dream) to **destination bank accounts**, so that when a direct invoice is created the payment is routed to the correct GL account. The module intercepts the `db_prewrite` hook on `ST_SALESINVOICE` and redirects non-cash payments to behave like cash payments into the mapped bank account.

## Module Code

`PD` — registerd as `ksf_payment_destinations` in FA.

## Namespace Mapping

| Layer | Namespace | Responsibility |
|---|---|---|
| Non-FA business logic | `ksfraser\PaymentDestinations\` | Domain models, interfaces, pure logic |
| FA-specific code | `ksfraser\FrontAccounting\PaymentDestinations\` | Controllers, views, hooks, FA integration |

## Directory Structure

```
ksf_payment_destinations/
├── ksf_payment_destinations.php          # FA entry point
├── ksf_payment_destinations.inc.php      # Constants (PREFS, HELP)
├── hooks.php                             # FA hooks (install_options, install_access, db_prewrite)
├── class.ksf_payment_destinations.php    # Controller (legacy, extends generic_fa_interface_controller)
├── class.ksf_payment_destinations_model.php  # Model (legacy, extends generic_fa_interface_model)
├── class.ksf_payment_destinations_view.php   # View (legacy, extends generic_fa_interface_view)
├── src/                                  # PSR-4 namespaced replacement (in progress)
│   ├── Controller/
│   ├── Model/
│   ├── View/
│   ├── Interfaces/
│   └── Exceptions/
├── Tests/
│   ├── bootstrap.php
│   └── Unit/
├── ProjectDcs/                           # This documentation
├── composer.json
└── phpunit.xml
```

## Legacy Architecture (current)

The module uses the `ksf_modules_common` framework:

- **`generic_fa_interface_controller`** — orchestrates form display, edit/delete/insert dispatch, and model/view coordination.
- **`generic_fa_interface_model`** — wraps `table_interface` for CRUD against `pref_ksf_payment_destinations`.
- **`generic_fa_interface_view`** — renders FA-style tables, combo boxes (bank accounts, payment terms), and tab navigation.
- **`table_interface`** — declarative table schema: field definitions, primary key, and SQL generation.
- **`hooks_ksf_payment_destinations`** — FA hooks class for menu registration, access control, and `db_prewrite` payment interception.

## Migration Plan (per AGENTS.md + ksf_bank_import ARCHITECTURE_MIGRATION.md)

| Deprecated | Replacement |
|---|---|
| `generic_fa_interface_model` / `table_interface` | `Repository` (`TransactionRepository`) + `QueryBuilder` |
| `generic_fa_interface_controller` | `Service` (`PaymentDestinationService`) + thin `Controller` with DI |
| `hooks_ksf_payment_destinations` (bare functions) | `HooksPaymentDestinations` (namespaced, inter-module: `getModuleConstants`, `getModuleCapabilities`, `hasCapability`, `respondToCapabilityRequest`) |
| Inline validation | `ValidatableTrait` from `ksfraser/traits` |
| Direct DB access | `ModulesDAO` (`ksf_ModulesDAO`) cross-platform abstraction |

## Key Data Flow

1. Admin navigates to **Setup Payment Destination Mapping** tab.
2. Selects a payment term (combo box from `sale_payment_list`) and a bank account (combo box from `bank_accounts_list`).
3. On submit, `model->insert_data()` resolves display names via `fa_bank_accounts` and `fa_payment_terms`, then persists to `pref_ksf_payment_destinations`.
4. At invoice time, FA calls `hooks_ksf_payment_destinations::db_prewrite()`.
5. The hook checks `$trans_type === ST_SALESINVOICE`, looks up the mapping for the cart's `terms_indicator`, and overwrites `$cart->pos['pos_account']` with the mapped bank account.
6. If the payment term is non-cash, the hook sets `$cart->payment_terms['cash_sale'] = 1` so FA generates a payment record.

## Security

- Access area: `SA_ksf_payment_destinations`
- Security section: `SS_ksf_payment_destinations` (111 << 8)
- Menu appears under **GL > orders** for users with the access permission.

## Database Schema

Table: `{TB_PREF}ksf_payment_destinations`

| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `payment_term` | `int(11)` | NOT NULL | 0 | PK — FK to payment terms |
| `payment_term_name` | `varchar(DESCRIPTION_LENGTH)` | NOT NULL | 0 | Denormalized display name |
| `bank_account` | `int(11)` | NOT NULL | 0 | FK to bank accounts |
| `bank_account_name` | `varchar(DESCRIPTION_LENGTH)` | NOT NULL | 0 | Denormalized display name |
