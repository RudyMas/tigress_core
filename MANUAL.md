# Tigress Core — Programmer's Manual

**Package:** `tigress/core`  
**Version:** `2026.05.18`  
**PHP:** `>= 8.5`  
**License:** GPL-3.0-or-later  
**Author:** Rudy Mas

---

## 1. Overview

Tigress Core is the central bootstrap library of the **Tigress PHP Framework** — an MVC framework for building web applications. It wires together:

- **Routing** (`tigress/router`)
- **Security / Auth** (`tigress/security`, `tigress/rights`)
- **Menu** (`tigress/menu`)
- **Database** (`tigress/database`, `tigress/repository`, `tigress/model`)
- **Templating** with Twig
- **Logging** with Monolog
- **PDF generation** with Dompdf
- **Translations / i18n**

The entry point is `Tigress\Core` which bootstraps the entire framework by reading config, connecting DB, creating Twig, loading routes, and executing the matched controller.

---

## 2. Installation

```bash
composer create-project tigress/tigress <project_name>
```

This pulls `tigress/core` as a dependency along with all other Tigress modules.

The project structure created on first run (by `FrameworkHelper::create()`):

```
<project_root>/
├── config/
│   ├── config.json         # application config
│   ├── routes.json         # routing definitions
│   └── .htaccess
├── private/                # protected server-side files
├── public/
│   ├── css/home/
│   ├── images/
│   ├── javascript/
│   ├── json/
│   └── scripts/
├── src/
│   ├── controllers/home/
│   ├── menus/
│   ├── models/
│   ├── repositories/
│   ├── services/
│   └── views/
├── system/
│   └── config.json
├── tests/
├── translations/
└── logs/                   # created by LoggerHelper
```

---

## 3. Configuration

### `system/config.json` — system-level (shipped with the package)

```json
{
  "debug": false,
  "Core": { "Twig": { "views": "src/views" } },
  "timezone": "Europe/Brussels",
  "subfolder": "",
  "subdomain_in_subfolder": false
}
```

| Key | Type | Purpose |
|---|---|---|
| `debug` | bool | Enables PHP error display and Twig debug mode. Auto-set to `true` in development. |
| `Core.Twig.views` | string | Directory path for Twig templates (relative to project root). |
| `timezone` | string | PHP timezone (used by `date_default_timezone_set`). |
| `subfolder` | string | If the app runs in a subfolder, e.g. `/myapp`. |
| `subdomain_in_subfolder` | bool | If true, subdomains are mapped to subfolders in `DOCUMENT_ROOT`. |

### `config/config.json` — application-level (user-created from `config.sample.json`)

```json
{
  "website": {
    "title": "...", "description": "...", "keywords": "...",
    "author": "...", "contact": "...",
    "html_lang": "nl-BE",
    "mode": "light"
  },
  "packages": { "tigress_database": false },
  "databases": {
    "development": { "default": { "host": "...", "port": 3306, ... } },
    "test": { ... },
    "production": { ... }
  },
  "servers": {
    "localhost": "development",
    "example.com": "production"
  }
}
```

**Key sections:**
- `website` — metadata used in Twig via `WEBSITE` constant. `mode` can be `"light"` or `"dark"` (controls CSS).
- `packages.tigress_database` — enables the database connection on boot.
- `databases` — keyed by server type (`development`, `test`, `production`), each with named DB connections (at minimum `"default"`).
- `servers` — maps HTTP hostnames to server types (`development`, `test`, `production`).

### `config/routes.json` — routing definitions

```json
{
  "routes": [
    {
      "request": "GET",
      "path": "/",
      "controller": "home\\HomeController",
      "method": "index",
      "level_rights": [100]
    }
  ],
  "extraRoutes": [{ "package": "tigress/core" }],
  "defaultRoute": "/version"
}
```

Each route has:
- `request` — HTTP method (`GET`, `POST`, etc.)
- `path` — URL pattern
- `controller` — fully-qualified class name (the router uses `Controller\` namespace prefix)
- `method` — method to call
- `level_rights` (optional) — array of user level IDs required
- `extraRoutes` — pulls in route files from other packages (e.g. `tigress/core` loads the built-in routes from `vendor/tigress/core/config/routes.json`)
- `defaultRoute` — fallback route if nothing matches

---

## 4. Core Bootstrap (`Tigress\Core`)

**File:** `src/Core.php`

The constructor runs the entire framework bootstrap in order:

1. **Define `TIGRESS_CORE_VERSION`**
2. **Load `config/config.json`** → defines `CONFIG`
3. **Load `system/config.json`** → defines `SYSTEM`
4. **Run `settingUpRootMapping()`** → defines `BASE_URL` and `SYSTEM_ROOT`
5. **If config files missing, run `FrameworkHelper::create()`** (first-install scaffolding)
6. **Define `WEBSITE`** constant
7. **Set timezone** from `SYSTEM->timezone`
8. **Database**: If `CONFIG->packages->tigress_database` is true, connect per server type → defines `DATABASE`
9. **Translation**: Instantiates `TranslationHelper` → defines `TRANSLATIONS`
10. **Twig**: Instantiates `DisplayHelper` with view path → defines `TWIG`. Adds core's own view path.
11. **Menu**: Instantiates `Controller\Menu` → defines `MENU`
12. **Security & Rights**: Instantiates `Security` and `Rights` → defines `SECURITY`, `RIGHTS`
13. **Server type**: Matches `$_SERVER['HTTP_HOST']` against `CONFIG->servers` → defines `SERVER_TYPE`
14. **Router**: Instantiates `Router`, creates routes, sets rights, executes → defines `ROUTER`

**Key static methods:**
- `Core::settingUpRootMapping()` — computes `BASE_URL` (relative web path) and `SYSTEM_ROOT` (absolute filesystem path). Handles subdomain-in-subfolder logic.
- `Core::dump(mixed $data, bool $stop = true)` — alias of `debug()` for printing `pre`-wrapped data.

---

## 5. Helper Classes

### 5.1 `DisplayHelper` (`Tigress\DisplayHelper`)

**File:** `src/DisplayHelper.php`

The templating engine wrapper around **Twig**. Instantiated during Core bootstrap and available as `TWIG`.

#### Methods

| Method | Signature | Description |
|---|---|---|
| `addGlobal` | `(string $name, mixed $value): void` | Add a global Twig variable |
| `addPath` | `(string $path): void` | Add a template directory to the loader |
| `render` | `(?string $template, array $data = [], string $type = 'TWIG', int $httpResponseCode = 200, array $config = []): string` | Main render method. Dispatches by `$type` |
| `redirect` | `(string $page): void` | Redirect to a URL or internal path |
| `version` | `(): string` (static) | Returns version `'2026.05.07'` |

#### Render types

| Type | Behavior |
|---|---|
| `TWIG` | Renders Twig template, flushes output, clears session flash messages |
| `PHP` | `include` with `extract($data)` |
| `HTML` | Reads raw HTML file from `src/Views/` |
| `JSON` | Sets JSON headers, outputs JSON via `DataConverter` |
| `DT` | Datatable-compatible JSON (`{"data": [...]}`) |
| `XML` | Outputs XML via `DataConverter` |
| `PDF` | Renders Twig → HTML, converts to PDF via `PdfCreatorHelper`, streams or saves |
| `STWIG` | Renders Twig to a string (no echo) |

#### Twig globals available in all templates

| Variable | Source |
|---|---|
| `_COOKIE` | `$_COOKIE` |
| `_GET` | `$_GET` |
| `_POST` | `$_POST` |
| `_SESSION` | `$_SESSION` |
| `BASE_URL` | computed URL root |
| `SERVER_TYPE` | development/test/production |
| `SYSTEM_ROOT` | absolute filesystem root |
| `WEBSITE` | `CONFIG->website` |
| `menu` | `MENU` constant (Menu manager) |
| `rights` | `['access'=>bool, 'read'=>bool, 'write'=>bool, 'delete'=>bool]` |

#### Custom Twig Filters

| Filter | Description |
|---|---|
| `base64_encode` | Base64-encode a string |
| `bitwise_and`, `bitwise_or`, `bitwise_xor`, `bitwise_not` | Bitwise operations |

#### Custom Twig Functions

| Function | Description |
|---|---|
| `__($word)` | Translate a string |
| `add_slider(name, value, text, labelPlacing, buttonText)` | Render a toggle-switch checkbox |
| `file_exists(path)` | Check if a file exists (relative to `SYSTEM_ROOT`) |
| `get_all_attrs(html)` | Parse all attributes from an HTML string into an array |
| `get_attr(html, attr)` | Get a single attribute value from an HTML string |
| `in_keys(needle, haystack)` | Check if value exists in array keys |
| `in_values(needle, haystack)` | Check if value exists in array values (supports JSON string) |
| `match(pattern, subject)` | Execute `preg_match`, returns matches array |
| `strip_dangerous_tags(text, profile)` | HTMLPurifier with profiles: `default` (b,i,u,br), `links` (+a[href]), `images` (+img) |
| `trans(key, translations)` | Manual translation lookup from a passed array |
| `week_range(isoWeek)` | Given `"2026-W20"`, returns e.g. `"20 - 11-05-2026 tem 17-05-2026"` |

---

### 5.2 `LoggerHelper` (`Tigress\LoggerHelper`)

**File:** `src/LoggerHelper.php`

Wraps **Monolog** with a `RotatingFileHandler` or `StreamHandler`.

#### Methods

| Method | Signature | Description |
|---|---|---|
| `create` | `(string $channelName, Level $level = Error, int $retentionDays = 30, ?string $dateFormat = 'Y-m-d', ?string $logDirectory = null): LoggerInterface` | Creates a logger. If `$retentionDays > 0`, uses `RotatingFileHandler` (rotates and auto-deletes old logs). Otherwise uses `StreamHandler` with date-suffixed filename. |
| `getDefault` | `(): LoggerInterface` | Convenience: returns logger with channel `'tigress'` and defaults. |

**Log format:** `[%datetime%] %channel%.%level_name%: %message% %context%`

**File naming:**
- With rotation: `<logDir>/<channelName>/<channelName>-YYYY-MM-DD.log`
- Without rotation: `<logDir>/<channelName>_YYYY-MM-DD.log`

---

### 5.3 `PdfCreatorHelper` (`Tigress\PdfCreatorHelper`)

**File:** `src/PdfCreatorHelper.php`

Wraps **Dompdf** for HTML-to-PDF conversion. Can be used standalone (does not require the full framework).

#### Methods

| Method | Signature | Description |
|---|---|---|
| `createPdf` | `(string $html, string $format = 'A4', string $orientation = 'portrait', string $filename = 'document.pdf', string $filepath = '/public/tmp/', bool $pagination = false, int $attachment = 1, string $font = 'Helvetica', int $fontSize = 8): void` | Converts HTML to PDF. `$attachment`: 0=inline, 1=download, 2=save to server. |
| `getImage` | `(string $image, string $alt = 'Logo', ?array $options = null): string` | Returns `<img>` tag with base64-encoded image (for embedding in PDF HTML). `$options`: `width`, `height`. |
| `setLanguage` | `(string $language): void` | Sets language for pagination text |
| `version` | `(): string` (static) | Returns `'2026.01.23'` |

**Pagination text** is localized in 40+ languages. The page number text is rendered at fixed coordinates per paper size/orientation.

---

### 5.4 `TranslationHelper` (`Tigress\TranslationHelper`)

**File:** `src/TranslationHelper.php`

Manages loading and retrieving translation strings from JSON files.

#### Methods

| Method | Signature | Description |
|---|---|---|
| `load` | `(string $filePath): void` | Loads a JSON translation file and merges into existing translations. JSON format: `{"en": {"key": "value"}, "nl": {...}}`. |
| `get` | `(): array` | Returns all loaded translations |
| `version` | `(): string` (static) | Returns `'2025.06.30'` |

**Usage in Twig:** `{{ __('Hello') }}`  
**Usage in PHP:** `__('Hello')`  
**Usage in JS:** `window.__('Hello')` (after loading translation files via `tigress.loadTranslations()`)

---

### 5.5 `FrameworkHelper` (`Tigress\FrameworkHelper`)

**File:** `src/FrameworkHelper.php`

Scaffolding tool that creates the initial project directory structure on first run.

#### Methods

| Method | Signature | Description |
|---|---|---|
| `create` | `(): void` | Creates `config/`, `private/`, `public/`, `src/`, `system/`, `tests/`, `translations/` directories with starter files |
| `update` | `(): void` | Copies updated `config.sample.json` and `system/config.json`, writes `update_version.txt` |
| `version` | `(): string` (static) | Returns `'2026.03.02'` |

Copied artifacts include:
- `HomeController.php` with an `index()` method rendering `home/home.twig`
- `base.twig`, `base_api.twig`, `datatable.twig` base templates
- CSS, JS starter files
- `.htaccess` deny-all files in protected directories
- TinyMCE upload scripts

---

## 6. Built-in Controllers

### 6.1 `Controller\Core\PhpInfoController`

**Route:** `GET /phpinfo` (requires level_rights `[100]`, i.e. superadmin)

Displays `phpinfo()`. Checks `RIGHTS->checkRights()` — redirects to `/home` on failure.

### 6.2 `Controller\Core\LockPagesController`

**Routes:** `POST /lock-pages/refresh`, `POST /lock-pages/release`

Manages collaborative page-locking using a `system_lock_pages` database table (PK: `resource` + `resource_id`).

| Method | Description |
|---|---|
| `checkIfPageIsLocked(resource, resourceId)` | Checks/acquires lock. Returns `true` if locked by someone else. Cleans expired locks. |
| `refreshLock(args, body)` | POST endpoint — refreshes/reserves lock. JSON response. |
| `releaseLock(args, body)` | POST endpoint — releases lock. JSON response. |
| `removePageLock(resource, resourceId)` | Direct lock deletion. |

### 6.3 `Controller\Core\GoogleDriveController`

**Route:** `GET /google/drive/link`

Handles encrypted Google Docs/Drive links. Uses RSA encryption to store/retrieve Google Drive URLs from repositories.

| Method | Description |
|---|---|
| `loadByLink()` | Reads `?repository=X&id=Y&field=Z`, loads repo data, decrypts or encrypts the Google Drive link, redirects. |
| `setPublicKey(path)` / `setPrivateKey(path)` | Override default key paths. |

### 6.4 `Controller\Core\SettingsController`

No built-in route — used programmatically for `system_settings` database table CRUD with optional encryption.

**Constructor:** `new SettingsController(bool $encryption = false)`

| Method | Description |
|---|---|
| `loadSettings(array $settings)` | Loads settings by key names |
| `getSettings()` | Returns `[key => value]`, auto-decrypting if encryption enabled |
| `saveSettings(array $settings)` | Saves settings, auto-encrypting if enabled |
| `setPublicKey(path)` / `setPrivateKey(path)` / `setKeySize(int)` | Configure RSA keys |

When encryption is enabled:
- Values <= max RSA payload → RSA-encrypted directly
- Larger values → AES key + AES encryption, with AES key RSA-encrypted, stored as `rsaKey:aes:iv:aes:ciphertext`

### 6.5 `Controller\version\VersionController`

**Route:** `GET /version`

Displays a detailed version info page showing all loaded Tigress modules and their versions. Disables the menu (`MENU->setPosition('none')`).

### 6.6 `Controller\tigress\TigressHelpController`

**Route:** `GET /help`

Placeholder — shows a static "manual coming soon" message.

---

## 7. Repositories

### 7.1 `Repository\SystemSettingsRepo`

**Table:** `system_settings`  
**PK:** `setting` (varchar 30)  
**Columns:** `setting`, `value`

### 7.2 `Repository\SystemLockPagesRepo`

**Table:** `system_lock_pages`  
**PK:** `resource` (varchar 50) + `resource_id` (varchar 11)  
**Columns:** `resource`, `resource_id`, `locked_by_user_id`, `locked_at`, `expires_at`  
**Auto-creates** the table on instantiation via `createTable` property.

---

## 8. Global Functions

**File:** `src/helpers/functions.php` (auto-loaded via Composer `files` autoload)

### `__(string $text): string`

Translates a string using the current language (from `CONFIG->website->html_lang`, first 2 chars). Falls back to the original string if no translation found.

### `is_json($string): bool`

Checks if a string is valid JSON using `json_decode` with `JSON_THROW_ON_ERROR`.

---

## 9. Twig Templates

### Base Templates (shipped in `files/src/views/`)

| Template | Description |
|---|---|
| `base.twig` | Full page layout with menu, sidebar, header, main content, footer, flash messages, loading popup. Includes Bootstrap 5, Font Awesome, Select2, Lucide, DataTables assets. |
| `base_api.twig` | Minimal layout — no menu/sidebar, just header/content/footer. Same CSS/JS assets. |
| `datatable.twig` | Extends `base.twig`, adds DataTables + plugins (Select, FixedColumns, FixedHeader, Buttons, SearchPanes, ColumnControl, JSZip, pdfmake). |

### Core In-Package Views

| Template | Description |
|---|---|
| `view/version/index.twig` | Version info page |
| `pdf.twig` | Base PDF layout with `pdfStyle` injection |
| `under_construction.twig` | Placeholder page |
| `icons.twig` | Twig macro for inline SVG icons (`Tigress` icons + Font Awesome subsets) |

### Flash Messages

The `base.twig` template checks `$_SESSION['error']`, `$_SESSION['success']`, `$_SESSION['warning']`, `$_SESSION['message']` and renders Bootstrap dismissible alerts. These are cleared after rendering.

---

## 10. Frontend Assets

### JavaScript (`public/javascript/`)

| File | Description |
|---|---|
| `tigress.js` | Main JS library (~580 lines). Initializes tooltips, auto-grow textareas, DataTables i18n, password toggles, unsaved-changes warning, lock-pages heartbeat. Self-executing on `DOMContentLoaded`. |
| `tigress_legacy.js` | jQuery-based Select2 initialization (for legacy support) |
| `isValidIBAN.js` | IBAN validation with country-specific length checks and mod-97 |
| `isValidNationalInsuranceNumber.js` | National identification number validation for BE, NL, DE, FR, LU with formatting and birthdate extraction |

**`tigress.js` key features:**
- `warnUnsavedChanges()` — tracks form dirty state, warns on `beforeunload`
- `tigress.loadTranslations()` / `window.__()` — client-side i18n
- `tigress.lockPages.start(resource, resourceId)` — heartbeat lock refresh every 2 min
- `initPasswordToggles()` — eye-icon toggle for password fields
- `resetDataTables(tableName)` — resets DataTables localStorage state

### CSS (`public/css/`)

| File | Description |
|---|---|
| `tigress.css` | Core styles: grid layout, toggle switches, survey steps, color schemes (`.red`, `.blue`, `.green`, etc.), break-out containers, loading popup, DataTables fixes |
| `tigress_dark.css` / `tigress_light.css` | Dark/light mode CSS variables |
| `PdfCreatorCss.css` | Print-optimized CSS for PDF generation (tables, headers/footers, page breaks, Bootstrap-compatible grid) |

---

## 11. Built-in Routes

| Method | Path | Controller | Action |
|---|---|---|---|
| `GET` | `/phpinfo` | `Core\PhpInfoController` | `index` (superadmin only) |
| `GET` | `/version` | `version\VersionController` | `index` |
| `GET` | `/help` | `tigress\TigressHelpController` | `index` |
| `GET` | `/google/drive/link` | `Core\GoogleDriveController` | `loadByLink` |
| `POST` | `/lock-pages/refresh` | `Core\LockPagesController` | `refreshLock` |
| `POST` | `/lock-pages/release` | `Core\LockPagesController` | `releaseLock` |

---

## 12. Code Review Observations

### Strengths
- **Clean separation of concerns** — each helper class has a single responsibility.
- **Good use of PHP 8.4+ features** — named arguments, match expressions, `#[NoReturn]`, typed properties.
- **First-install scaffolding** is automatic and well-thought-out.
- **Twig integration** is comprehensive with custom filters, functions, and globals.
- **i18n support** covered at PHP, Twig, and JavaScript levels.
- **Pagination in PDFs** is localized in 40+ languages.

### Quickstart for a New Controller

```php
namespace Controller\admin;

class DashboardController
{
    public function index(): void
    {
        TWIG->render('admin/dashboard.twig', [
            'stats' => ['users' => 42],
        ]);
    }
}
```

Add the route in `config/routes.json`:
```json
{
  "request": "GET",
  "path": "/admin",
  "controller": "admin\\DashboardController",
  "method": "index"
}
```