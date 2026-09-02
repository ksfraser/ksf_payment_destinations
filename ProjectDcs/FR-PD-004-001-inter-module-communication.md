# FR-PD-004-001 — Inter-Module Communication

## Functional Requirement: Four Standard Hook Methods for Module Discovery

**Status:** Active
**Module:** ksf_payment_destinations
**Related Code:** `hooks.php` — `getModuleConstants`, `getModuleCapabilities`, `hasCapability`, `respondToCapabilityRequest`

## Description

Implement the four standard inter-module communication methods so other modules can query this module's capabilities and constants. This uses FA's standard hook interface and coexists with the PSR-4 DI approach.

## Acceptance Criteria

1. `getModuleConstants()` returns `['KSF_PAYMENT_DESTINATIONS_MODULE' => 'ksf_payment_destinations']`
2. `getModuleCapabilities()` returns `payment_redirect` capability with `methods: ['db_prewrite']`
3. `hasCapability('payment_redirect')` returns `true`; any other capability returns `false`
4. `respondToCapabilityRequest()` dispatches to the appropriate method based on request type (`capabilities`, `constants`, `has:<name>`)

## Implementation

These methods allow any FA module to query this module without requiring Composer's autoloader or DI container. The PSR-4 implementation (Service/Repository) is used internally by `db_prewrite`; these capability methods are a separate, FA-native interface.

## Scope

- Other modules call `hook_invoke_all('respondToCapabilityRequest', $data, ['request' => 'capabilities|constants|has:payment_redirect'])`
- No Composer autoload required for inter-module queries
- Both mechanisms (capability methods + PSR-4 DI) coexist independently
