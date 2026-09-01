# AGENTS_APPENDIX.md — ksf_payment_destinations

## Module Code
PD

## Namespace Mapping
- Non-FA business logic: `ksfraser\PaymentDestinations\`
- FA-specific code: `ksfraser\FrontAccounting\PaymentDestinations\`

## Replacement of Deprecated Base Classes (per AGENTS.md + ksf_bank_import ARCHITECTURE_MIGRATION.md)
See `ProjectDcs/Architecture.md`:
- `generic_fa_interface_model` / `table_interface` → `Repository` (`TransactionRepository`) + `QueryBuilder`
- `generic_fa_interface_controller` → `Service` (`PaymentDestinationService`) + `Controller` (thin layer, DI)
- `hooks_ksf_payment_destinations` (bare functions) → `HooksPaymentDestinations` (namespaced, uses `Ksfraser\Traits\HookQueryProviderTrait` for inter-module queries via `ksf_get_value`/`ksf_get_values`/`ksf_set_value`)
- Traits (`Ksfraser\Traits\ValidatableTrait`) replace inline validation
- `ModulesDAO` (`ksf_ModulesDAO`) provides cross-platform abstraction for new persistence

## Directory Mapping
- Controller: `src/Controller/PaymentDestinationController.php`
- Model: `src/Model/PaymentDestinationModel.php`
- View: `src/View/PaymentDestinationView.php`
- Hooks: `hooks.php` (FA convention, kept top-level)
- Entry point: `ksf_payment_destinations.php`

## Requirement References
Every class/method docblock must include `@BABOK Related:` referencing the file in `ProjectDcs/`.

## Composer Dependencies
Any module using Composer packages must include `ComposerDependencies` from `ksf_FA_Common`:

```php
// In hooks.php before any namespaced class is used:
$composerDepsPath = dirname(__DIR__) . '/ksf_FA_Common/src/Utils/ComposerDependencies.php';
if (file_exists($composerDepsPath)) {
    require_once $composerDepsPath;
    \ksfraser\FrontAccounting\Common\Utils\ComposerDependencies::ensure(__DIR__);
}
```

This runs `composer install` if `vendor/autoload.php` is missing, then FA's normal autoloader takes over.
**Required for:** `ksf_modules_common`, `ksf_traits` packages, all PSR-4 namespaced code.

## Inter-Module Communication (ksf_traits)
`HooksPaymentDestinations` uses `Ksfraser\Traits\HookQueryProviderTrait` (`ksf_traits`) for standard inter-module queries:

- `hook_invoke_first('ksf_get_value', 'payment_destinations.routing', ...)` → returns routing array
- `hook_invoke_all('ksf_get_values', $keys, ...)` → batch lookup

See: `src/Hooks/HooksPaymentDestinations.php`

## Traceability Rule
When a function is rewritten/moved, the original file must retain a commented stub referencing new location and `@BABOK` ID.

## FA Session and Cookie Handling
FA uses rotating session IDs that change on every request. **Do not** reuse curl/phphttpclient handles across independent requests — each request must receive fresh cookies from the previous response.

### Correct pattern (PHP)
```php
// Each request gets its own HTTP handle with cookies from the LAST response
$ch1 = curl_init($url);
curl_setopt($ch1, CURLOPT_COOKIEJAR, '/tmp/fa_cookies.txt');
curl_setopt($ch1, CURLOPT_COOKIEFILE, '/tmp/fa_cookies.txt');
$resp1 = curl_exec($ch1);
curl_close($ch1);

// New handle, SAME cookie jar (passive)
$ch2 = curl_init($url);
curl_setopt($ch2, CURLOPT_COOKIEJAR, '/tmp/fa_cookies.txt');
curl_setopt($ch2, CURLOPT_COOKIEFILE, '/tmp/fa_cookies.txt');
$resp2 = curl_exec($ch2);
curl_close($ch2);
```

### FA Login Flow
1. GET `/index.php` — server sets `FA{hash}` session cookie in response
2. POST to `/index.php` with `user_name`, `password`, `submit=Login` — new session cookie set
3. All subsequent requests use `COOKIEFILE` to send session cookie

**Important:** If login fails (empty response, 200 with Content-Length: 0), FA is redirecting to the login form. The next request must still use the same cookie jar so the session is maintained.

## Browser Testing (Playwright + Chromium)
For end-to-end UAT scenarios against the live FA instance at `http://localhost:8080`:

### Setup
```bash
npx playwright install chromium --with-deps
```

### Example: Login and navigate to module
```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({
    executablePath: '/usr/bin/chromium-browser',
    headless: true,
    args: ['--no-sandbox', '--disable-dev-shm-usage']
  });
  const context = await browser.newContext();
  const page = await context.newPage();

  // 1. Load FA homepage (sets session cookie)
  await page.goto('http://localhost:8080/');

  // 2. Login
  await page.fill('input[name="user_name"]', 'opencode');
  await page.fill('input[name="password"]', 'opencode');
  await page.click('button[type="submit"], input[name="submit"]');

  // 3. Navigate to GL > orders > ksf_payment_destinations
  await page.click('text=GL');
  await page.click('text=orders');
  await page.click('text=ksf_payment_destinations');

  // 4. Click Setup Payment Destination Mapping tab
  await page.click('text=Setup Payment Destination Mapping');

  // 5. Select Dream CC from Payment Terms
  await page.selectOption('select[name*="payment_term"], combo[name*="payment_term"]', 'Dream CC');

  // 6. Select Dream Holdings from Bank Account
  await page.selectOption('select[name*="bank_account"], combo[name*="bank_account"]', 'Dream Holdings');

  // 7. Click Map the accounts
  await page.click('button:has-text("Map the accounts")');

  // 8. Verify row appears
  const row = await page.locator('tr:has-text("Dream CC"):has-text("Dream Holdings")');
  console.log('Mapping visible:', await row.isVisible());

  await browser.close();
})();
```

### Container Environment Notes
- This is a **container** — some system paths (`/etc/hosts`, `/etc/passwd`) are not meaningful
- FA runs at `http://localhost:8080` (not a named host)
- Chromium is at `/usr/bin/chromium-browser`
- Playwright is available (`npx playwright --version` → 1.62.1)
- Use `--no-sandbox` and `--disable-dev-shm-usage` Chromium flags in containers
- Playwright auto-handles cookies and session rotation — prefer Playwright over raw curl for browser tests

## Database Verification Queries
For GL posting verification in UAT tests, use `{TB_PREF}` replaced by actual table prefix (e.g. `test_`):

```sql
-- Check GL entries for an invoice
SELECT account_code, SUM(amount) as total
FROM `{TB_PREF}gl_trans`
WHERE account_code IN (1, 3, 7)
  AND trandate = CURDATE()
GROUP BY account_code;

-- Full invoice audit trail
SELECT dt.type, dt.trans_no, dt.reference, dt.total, dt.allocated, dt.cash_sale,
       gt.account_code, gt.amount
FROM `{TB_PREF}debtor_trans` dt
JOIN `{TB_PREF}gl_trans` gt ON gt.type = dt.type AND gt.type_no = dt.trans_no
WHERE dt.type = 10  -- ST_SALESINVOICE
  AND dt.tran_date = CURDATE()
ORDER BY dt.trans_no DESC;
```
