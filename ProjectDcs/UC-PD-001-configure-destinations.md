# UC-PD-001 — Configure Destinations

## Use Case: Admin Configures Module Settings

**Status:** Active  
**Module:** ksf_payment_destinations  
**Related Code:** `class.ksf_payment_destinations_view.php:68` (Configuration tab), inherited `show_config_form`  
**Related FR:** FR-PD-001-001-mapping-ui.md

## Actors

- **Admin** — user with `SA_ksf_payment_destinations` access permission.

## Preconditions

- Module is installed in FA.
- User is logged in with appropriate security clearance.

## Main Flow

1. Admin navigates to **GL > orders > ksf_payment_destinations**.
2. Admin clicks the **Configuration** tab.
3. Admin sees configuration options (currently: Debug level 0/1+).
4. Admin adjusts settings and saves.

## Acceptance Criteria

1. Configuration tab is accessible from the module's main screen.
2. Debug setting is persisted in FA preferences.
3. Changes take effect without requiring module re-install.
