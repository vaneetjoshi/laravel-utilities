# 📖 Laravel Utilities Engine — Complete Master Reference & API Guide

The **Laravel Utilities Engine** (`vaneetjoshi/laravel-utilities`) is a foundational ecosystem package that provides options storage, event-driven Action/Filter/View hooks, dynamic navigation and dashboard widget engines, currency/date formatting, and a component-based Settings Schema engine with interactive JavaScript dependencies and repeaters.

---

## ⚙️ System Requirements

Before installing the **Laravel Utilities Engine**, ensure your host application server meets the following minimum constraints as defined in the package configuration:

* **PHP:** `^8.2`, `^8.3`, `^8.4`, or `^8.5`

* **Laravel Framework:** `^11.0`, `^12.0`, or `^13.0`

* **Database:** MySQL 8.0+, PostgreSQL 12+, or SQLite 3.35+
* **Cache Driver:** Redis or Memcached is strongly recommended for production environments to utilize the Tenant-Aware caching layer.

---

## 📦 Installation Instructions

Follow these step-by-step instructions to install and initialize the package in your host application.

### Step 1: Install via Composer

Pull the package into your host application using Composer.

**Terminal**

```bash
composer require vaneetjoshi/laravel-utilities

```

### Step 2: Publish Package Assets

Publish the configuration files, database migrations, and optionally the public assets and views. The `utilities-config` tag will publish `config/utilities.php`, `config/settings.php`, and `config/widgets.php` into your host app.

**Terminal**

```bash
php artisan vendor:publish --provider="Vaneetjoshi\LaravelUtilities\LaravelUtilitiesServiceProvider" --tag="utilities-config"
php artisan vendor:publish --provider="Vaneetjoshi\LaravelUtilities\LaravelUtilitiesServiceProvider" --tag="utilities-views"

```

### Step 3: Run Database Migrations

The package requires the `options` table to store key-value settings globally. Run your standard Laravel migration command to create this table.

**Terminal**

```bash
php artisan migrate

```

*(Note: If you are using `vaneetjoshi/laravel-tenancy`, ensure you also add the utilities migration path to your `config('tenancy.tenant_migrations')` array so it runs on tenant databases as well).*

### Step 4: Configure the UI Components (Optional)

If you intend to use the headless Settings Component (`<x-utilities::settings-panel />`), you must define your own named route in your host application's routing files.

**routes/web.php**

```php
<?php

use Illuminate\Support\Facades\Route;

// Define your host-controlled route for the settings panel
Route::get('/admin/settings/{group?}', function (?string $group = null) {
    return view('admin.settings', ['group' => $group]);
})->middleware(['web', 'auth'])->name('admin.settings');

```

### Step 5: Clear Laravel Caches

To ensure all Service Providers, Facades, and Blade Components are correctly registered and discovered by the framework, clear the application caches.

**Terminal**

```bash
php artisan optimize:clear

```

You are now fully ready to utilize the Options Storage, Hooks, Navbars, Widgets, and Component-Based Settings schema!


## Architecture Overview & Service Provider

The package automatically registers singletons, facades, helpers, Blade components, database migrations, and headless HTTP update routes upon boot:

```php
// Service Container Aliases
app('utilities.options');    // Vaneetjoshi\LaravelUtilities\Services\OptionsService
app('utilities.hooks');      // Vaneetjoshi\LaravelUtilities\Services\HookService
app('utilities.formatting'); // Vaneetjoshi\LaravelUtilities\Services\FormattingService
app('utilities.widgets');    // Vaneetjoshi\LaravelUtilities\Widgets\WidgetManager
app('utilities.navbars');    // Vaneetjoshi\LaravelUtilities\Navbars\NavbarManager
app(SettingsRegistry::class); // Vaneetjoshi\LaravelUtilities\Settings\SettingsRegistry

```

---

## Options Storage Engine

The Options Engine provides key-value persistence in the `options` database table. All values are JSON-encoded during `set()` operations to preserve strict PHP data types (booleans, integers, floats, arrays, and associative structures).

### Database Schema (`options` table)

| Column | Type | Description |
| --- | --- | --- |
| `id` | `bigint` (Primary Key) | Auto-incrementing identifier |
| `type` | `string(150)` (Unique, Indexed) | The unique option key |
| `value` | `longText` | JSON-encoded value string |
| `created_at` / `updated_at` | `timestamp` | Timestamps |

### Multi-Tenancy Cache Isolation

When integrated with `laravel-tenancy`, the store automatically prefixes cache keys using `tenant_id()` or the active database connection name (`tenant_{id}_key` vs `global_key`).

### Available Methods & Examples

#### `getOption(string $key, mixed $default = null): mixed`

Retrieves an option from cache or the database. Automatically JSON-decodes stored strings back to native PHP arrays or primitives.

```php
// Primitive retrieval
$siteName = getOption('site_name', 'Default Platform');

// Array retrieval
$locales = getOption('supported_locales', ['en', 'es']);

```

#### `setOption(string $key, mixed $value): mixed`

Persists or updates an option. Updates the tenant-isolated cache and database table simultaneously.

```php
// Store primitive boolean
setOption('maintenance_mode', true);

// Store structured array
setOption('smtp_config', [
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'encryption' => 'tls',
]);

```

#### `setManyOptions(array $options): void`

Batch-persists an associative array of key-value pairs in a single operation.

```php
setManyOptions([
    'platform_currency' => 'USD',
    'commission_rate' => 12.5,
    'enable_registrations' => false,
]);

```

#### `deleteOption(string $key): bool`

Deletes an option record from the database and purges its cached value.

```php
deleteOption('temporary_promo_code');

```

#### `flushOptionCache(): void`

Clears cached values across all options for the active context.

```php
flushOptionCache();

```

---

## Smart Settings & Dynamic Form Engine

The Settings Engine enables you to define schema groups and fields directly in PHP (`config/settings.php`).

---

### Setting Groups (`Group`)

Groups organize setting fields into section panels. They enforce role/permission access controls and custom icons.

```php
use Vaneetjoshi\LaravelUtilities\Settings\SettingsManager as Settings;

return [
    'groups' => [
        'general' => Settings::group('general')
            ->label('General Platform Configuration')
            ->description('Manage primary platform settings, branding, and status.')
            ->icon('<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>')
            ->roles(['super-admin', 'administrator'])
            ->permissions(['manage-settings'])
            ->addFields([ ... ]),
    ],
];

```

---

### Full List of Available Field Types

All field types are instantiated via factory methods on `Vaneetjoshi\LaravelUtilities\Settings\SettingsManager`.

| Factory Method | Returned Class | Input HTML Rendered | Primary Use Case |
| --- | --- | --- | --- |
| `Settings::text($name)` | `Field` | `<input type="text">` | Plain text, names, titles |
| `Settings::email($name)` | `Field` | `<input type="email">` | Email addresses |
| `Settings::password($name)` | `Field` | `<input type="password">` with toggle | API Secrets, Keys, Passwords |
| `Settings::textarea($name)` | `Field` | `<textarea rows="4">` | Descriptions, CSS, raw scripts |
| `Settings::checkbox($name)` | `Field` | `<input type="checkbox">` | Boolean toggles (1 / 0) |
| `Settings::date($name)` | `Field` | `<input type="date">` | Date selections |
| `Settings::datetime($name)` | `Field` | `<input type="datetime-local">` | Date and time selections |
| `Settings::select($name)` | `SelectField` | `<select>` | Dropdown single choice |
| `Settings::number($name)` | `NumberField` | `<input type="number">` | Integers, floats, quantities |
| `Settings::file($name)` | `FileField` | `<input type="file">` | General file uploads |
| `Settings::image($name)` | `FileField` | `<input type="file">` + Preview | Logo, favicon, avatar uploads |
| `Settings::keyValue($name)` | `Field` | Key-Value Table | Headers, key-value mappings |
| `Settings::array($name)` | `ArrayField` | Drag-and-Drop Repeater | Multi-row schemas, lists, nested objects |

#### Code Examples for Every Field Type

```php
use Vaneetjoshi\LaravelUtilities\Settings\SettingsManager as Settings;

// 1. Text Field
Settings::text('company_name')
    ->label('Company Name')
    ->placeholder('e.g. Acme Corp')
    ->rules(['required', 'string', 'max:255'])
    ->default('Universal MLM');

// 2. Email Field
Settings::email('support_email')
    ->label('Support Email')
    ->placeholder('support@example.com')
    ->rules(['required', 'email']);

// 3. Password Field (Includes toggle show/hide JS built-in)
Settings::password('api_secret_key')
    ->label('API Secret')
    ->placeholder('Enter private key')
    ->rules(['nullable', 'string', 'min:16']);

// 4. Textarea Field
Settings::textarea('footer_copyright_text')
    ->label('Footer Copyright Notice')
    ->placeholder('© 2026 All Rights Reserved')
    ->rules(['nullable', 'string', 'max:500']);

// 5. Checkbox Field (Outputs 1 or 0)
Settings::checkbox('enable_registration')
    ->label('Allow New User Registrations')
    ->helpText('When disabled, registration endpoints will throw 403 HTTP errors.')
    ->default(true);

// 6. Select Field (Single Choice)
Settings::select('default_language')
    ->label('Default System Language')
    ->options([
        'en' => 'English (US)',
        'es' => 'Spanish',
        'fr' => 'French',
    ])
    ->default('en');

// 7. Multi-Select Field
Settings::select('allowed_payout_methods')
    ->label('Active Payout Options')
    ->multiple(true)
    ->options([
        'usdt' => 'USDT (TRC20)',
        'bank' => 'Bank Transfer',
        'paypal' => 'PayPal',
    ])
    ->default(['usdt']);

// 8. Number Field
Settings::number('minimum_payout_amount')
    ->label('Minimum Withdrawal Limit')
    ->min(10.00)
    ->max(10000.00)
    ->step(0.01)
    ->rules(['required', 'numeric'])
    ->default(50.00);

// 9. File Field
Settings::file('terms_document')
    ->label('Terms & Conditions PDF')
    ->disk('public')
    ->directory('documents/legal')
    ->accept(['.pdf', '.docx']);

// 10. Image Field (Renders thumbnail preview if file exists on disk)
Settings::image('site_logo')
    ->label('Header Logo')
    ->disk('public')
    ->directory('branding')
    ->helpText('Upload PNG or SVG format (Max: 2MB).')
    ->rules(['nullable', 'image', 'max:2048']);

// 11. Date Field
Settings::date('promotion_start_date')
    ->label('Campaign Launch Date');

// 12. DateTime Field
Settings::datetime('maintenance_window_end')
    ->label('Maintenance Downtime End');

```

---

### Fluent Field Modifiers

All fields inherit from `Vaneetjoshi\LaravelUtilities\Settings\Fields\Field` and support method chaining:

```php
Settings::text('custom_field')
    ->label('Display Label')         // Custom UI headline label
    ->placeholder('Placeholder...')  // Input placeholder text
    ->helpText('Explanatory text')   // Sub-label help text
    ->rules(['required', 'min:3'])   // Laravel validation rules
    ->default('Initial Fallback')    // Default value if unpopulated
    ->view('custom.field.template')  // Override Blade rendering template
    ->roles(['admin'])               // Restrict visibility to specific roles
    ->permissions(['edit-settings']) // Restrict visibility to specific permissions
    ->dependsOn('parent_field', '1'); // Conditional rendering rule

```

---

### Conditional Field Dependencies (`dependsOn`)

Fields can dynamically show or hide based on the real-time input state of another field in the DOM.

```php
// Step 1: Parent Checkbox Field
Settings::checkbox('enable_smtp')
    ->label('Enable External SMTP Relay')
    ->default(false);

// Step 2: Child Field conditionally rendered when enable_smtp == 1
Settings::text('smtp_host')
    ->label('SMTP Server Host')
    ->placeholder('smtp.sendgrid.net')
    ->dependsOn('enable_smtp', '1');

// Step 3: Dependent on multiple matched array values
Settings::text('stripe_public_key')
    ->label('Stripe Public Key')
    ->dependsOn('payment_gateway', ['stripe', 'stripe_connect']);

```

#### Client-Side & Server-Side Security Behavior

* **Client-Side:** The JS engine listens to input events, recursively toggles CSS `.hidden` classes, and handles nested dependencies up to 5 iterations deep.
* **Server-Side:** Validation rules automatically morph! If the dependency condition is not satisfied during form submission, `required` rules are converted to `nullable`, preventing validation errors for hidden fields.

---

### Repeater & Nested Schema Fields (`ArrayField`)

`ArrayField` creates dynamic drag-and-drop repeaters. It supports re-ordering (via `SortableJS`), row insertion/deletion, min/max constraints, and nested schema evaluation.

```php
Settings::array('social_links')
    ->label('Social Media Profiles')
    ->helpText('Add up to 5 social media links for the platform footer.')
    ->minRows(1)
    ->maxRows(5)
    ->schema([
        Settings::select('platform')
            ->label('Platform')
            ->options([
                'facebook'  => 'Facebook',
                'twitter'   => 'Twitter / X',
                'instagram' => 'Instagram',
                'telegram'  => 'Telegram',
            ])
            ->rules(['required', 'string']),

        Settings::text('url')
            ->label('Profile Target URL')
            ->placeholder('https://...')
            ->rules(['required', 'url']),

        Settings::checkbox('open_in_new_tab')
            ->label('Open link in new window')
            ->default(true),
    ]);

```

#### Re-indexing & Drag-and-Drop Pipeline

When the user drags rows or deletes items, JavaScript generates timestamped form keys (e.g., `social_links[1723456789][platform]`). Upon submission:

1. `ArrayField::getRules()` validates rows using wildcard expansion (`social_links.*.platform`).
2. `SettingsManager::recursivelyReindexArray()` resets numeric keys starting from `0` to remove array gaps before database storage.

---

### Headless Settings Component

Instead of package-defined routes, the UI is embedded directly into host application views using the `<x-utilities::settings-panel />` Blade component.

```html
<!-- resources/views/admin/settings.blade.php -->
@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <x-utilities::settings-panel 
            :group="$group" 
            route-name="admin.settings" 
        />
    </div>
@endsection

```

#### Host Application Route Mapping

```php
// routes/web.php
Route::get('/admin/settings/{group?}', function (?string $group = null) {
    return view('admin.settings', ['group' => $group]);
})->middleware(['web', 'auth', 'role:admin'])->name('admin.settings');

```

---

### The `setting()` Helper & `SettingsRegistry`

The `setting()` helper resolves field defaults without database lookups using the `SettingsRegistry` singleton.

```php
// 1. Checks Database/Cache via getOption('site_name')
// 2. If null, returns the schema default defined in config/settings.php
// 3. If no schema default exists, returns the fallback argument ('Fallback App')
$title = setting('site_name', 'Fallback App');

```

---

## 4. Event-Driven Hook Engine

The `HookService` enables decoupled modules to register lifecycle actions, transform data payloads, or inject Blade view partials without direct class dependencies.

```php
use Vaneetjoshi\LaravelUtilities\Facades\Hooks;

```

### Action Hooks

Actions execute side-effects at designated code execution points.

```php
// Register Action Listener
Hooks::addAction('user.registered', function ($user) {
    Mail::to($user)->send(new WelcomeMail());
}, $priority = 10);

// Trigger Action
Hooks::doAction('user.registered', $user);

// Remove Action
Hooks::removeAction('user.registered', $callback);

// Check / Inspect
bool $hasAction = Hooks::hasAction('user.registered');

```

### Filter Hooks

Filters accept a payload value, transform it across registered listeners, and return the mutated output.

```php
// Register Filter Listener
Hooks::addFilter('payout_commission_rate', function (float $rate, $user) {
    if ($user->isVip()) {
        return $rate + 5.0; // Bonus 5% for VIP members
    }
    return $rate;
}, $priority = 10);

// Apply Filter
$finalRate = Hooks::applyFilters('payout_commission_rate', 10.0, $currentUser);


```

### View Hooks

View Hooks execute Blade/HTML callbacks safely inside layout view slots. Exceptions are isolated so a failing view hook will not crash the layout.

```php
// Register View Hook Slot Partial
Hooks::addView('navbar.end', function ($user) {
    return view('partials.user-avatar-badge', ['user' => $user]);
});

// Render View Hook Slot in Blade
{!! renderViewHook('navbar.end', auth()->user()) !!}

```

### Inspection & Debugging API

```php
// Count registered listeners
$actionCount = Hooks::count('user.registered', 'action');
$filterCount = Hooks::count('payout_commission_rate', 'filter');

// Inspect execution priorities array
$priorities = Hooks::priorities('user.registered', 'action'); // e.g. [5, 10, 20]

// Flush hooks
Hooks::removeAll('user.registered'); // Clears specific hook
Hooks::removeAll();                  // Clears all registered hooks

```

---

## Navigation Engine (`Navbars`)

The Navbar Engine provides structural DTO objects for hierarchical navigation, support for nested submenus, automatic sorting, active URL pattern matching, and role/permission authorization gating.

### Registering Menu Items in a Zone

```php
use Vaneetjoshi\LaravelUtilities\Facades\Navbars;
use Vaneetjoshi\LaravelUtilities\Navbars\DTOs\NavbarItemDTO;

Navbars::zone('sidebar_main')->add(function (array $items, $user) {
    
    // Parent Menu Item
    $dashboard = NavbarItemDTO::make('dashboard', 'Dashboard')
        ->route('admin.dashboard')
        ->icon('tabler:layout-dashboard')
        ->activePatterns(['admin/dashboard*'])
        ->setOrder(1);

    // Parent Menu Item with Nested Children
    $management = NavbarItemDTO::make('management', 'User Management')
        ->icon('tabler:users')
        ->roles(['admin', 'manager'])
        ->setOrder(2);

    $allUsers = NavbarItemDTO::make('users_index', 'All Users')
        ->route('admin.users.index')
        ->permissions(['users.view'])
        ->setOrder(1);

    $management->addChild($allUsers);

    // Conditional Visibility Rule
    $vipPortal = NavbarItemDTO::make('vip_portal', 'VIP Portal')
        ->url('/vip')
        ->visibleIf(fn($u) => $u && $u->isVip())
        ->setOrder(3);

    $items[] = $dashboard;
    $items[] = $management;
    $items[] = $vipPortal;

    return $items;
});

```

### Fetching & Rendering Navbars

```php
// Fetches, filters authorized items, and sorts items and children by order
$menuItems = navbar('sidebar_main');

```

```blade
@foreach(navbar('sidebar_main') as $item)
    <a href="{{ $item->url ?? route($item->route) }}" 
       class="nav-link {{ request()->is($item->activePatterns) ? 'active' : '' }}">
        @if($item->icon)
            <x-icon :name="$item->icon" class="w-5 h-5" />
        @endif
        <span>{{ $item->label }}</span>
    </a>

    @if(!empty($item->children))
        <div class="submenu ml-4">
            @foreach($item->children as $child)
                <a href="{{ $child->url ?? route($child->route) }}">{{ $child->label }}</a>
            @endforeach
        </div>
    @endif
@endforeach

```

---

## Dashboard Widget Engine (`Widgets`)

The Widget Engine provides metric card DTOs with support for standard semantic colors, Iconify icons, visibility rules, and wrapper templates.

### Registering Widgets

```php
use Vaneetjoshi\LaravelUtilities\Facades\Widgets;
use Vaneetjoshi\LaravelUtilities\Widgets\DTOs\WidgetDTO;
use Vaneetjoshi\LaravelUtilities\Widgets\Enums\WidgetColor;
use Vaneetjoshi\LaravelUtilities\Widgets\Enums\WidgetIcon;

Widgets::zone('dashboard_top')->add(function (array $widgets, $user) {
    
    $widgets[] = WidgetDTO::make('total_revenue')
        ->title('Total Platform Revenue')
        ->value(currency_format(124500.00))
        ->icon(WidgetIcon::FIAT_USD)
        ->color(WidgetColor::SUCCESS)
        ->visibleIf(fn($u) => $u && $u->hasRole('admin'))
        ->setOrder(10);

    $widgets[] = WidgetDTO::make('active_users')
        ->title('Active Network Members')
        ->value('1,280 Users')
        ->icon(WidgetIcon::MLM_TEAM)
        ->color(WidgetColor::PRIMARY)
        ->setOrder(20);

    return $widgets;
});

```

### Rendering Widgets in Blade

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach(widget('dashboard_top') as $widget)
        @include($widget->view ?? config('widgets.default_wrapper'), ['widget' => $widget])
    @endforeach
</div>

```

---

## Currency & Date Formatting Service

The `FormattingService` formats financial metrics and date objects consistently across tenant storefronts, emails, and dashboards.

### `currency_symbol(?string $currencyCode = null): string`

Resolves the currency symbol from option settings or an explicit ISO string.

```php
currency_symbol('USD'); // Returns '$'
currency_symbol('EUR'); // Returns '€'
currency_symbol('INR'); // Returns '₹'
currency_symbol('USDT'); // Returns '$'

```

### `currency_format($amount, ?int $decimals, ?string $decSep, ?string $thousandSep, ?string $code): string`

Formats numerical values into formatted currency strings.

```php
// Standard system format
currency_format(1250.5); 
// Output: "$ 1,250.50"

// Custom precision and symbol override
currency_format(500000.758, 2, '.', ',', 'EUR'); 
// Output: "€ 500,000.76"

```

### `formatDate($date, ?string $format = null): string`

Formats timestamps, Carbon instances, or date strings into system date structures.

```php
formatDate(now()); 
// Output: "27 July 2026 08:43 AM" (Or as configured in utilities.date_format)

formatDate('2026-12-31 23:59:59', 'Y/m/d'); 
// Output: "2026/12/31"

```

---

## Smart Icon Component (`<x-icon>`)

The `<x-icon>` component renders local SVGs or falls back to the Iconify vector engine.

### Fallback Priority Logic

1. **Remote Image URLs:** If the `:name` prop starts with `http://` or `https://`, it renders an `<img>` tag.
2. **Host Application Override:** Checks for a Blade view at `resources/views/components/icons/{name}.blade.php`.
3. **Package Icon Library:** Checks for package built-in SVGs inside `utilities::components.icons.{name}` (e.g., `cryptocurrency-color-usdt`).
4. **Iconify Fallback Engine:** Renders `<span class="iconify" data-icon="{name}"></span>` and dynamically includes `iconify.min.js`.

```html
<!-- Remote Image -->
<x-icon name="https://example.com/badge.png" class="w-8 h-8" />

<!-- Core Package SVG -->
<x-icon name="cryptocurrency-color-usdt" class="w-6 h-6" />

<!-- Iconify Library Fallback -->
<x-icon name="tabler:binary-tree" class="w-5 h-5 text-indigo-600" />

```