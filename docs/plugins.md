# Plugins

A plugin is a folder under `plugins/` that can register hooks, routes, views,
and migrations. Plugins are discovered automatically on boot but only run when
enabled.

## Create a plugin

```bash
php artisan make:plugin "Welcome Banner"
```

This scaffolds `plugins/welcome-banner/`:

```
welcome-banner/
  plugin.json        # manifest (slug, name, version, description, author)
  plugin.php         # bootstrap script — registers hooks/routes
  views/             # blade views, namespaced plugin.welcome-banner::
```

## Enable / disable

```bash
php artisan plugin:enable welcome-banner
php artisan plugin:disable welcome-banner
```

Enable state lives in `storage/app/plugins.json`. The admin Plugins screen
(`/admin/plugins`) does the same thing through the UI.

## The bootstrap file

`plugin.php` runs once at boot. It has these variables in scope:

| Variable   | Type                                           |
|------------|------------------------------------------------|
| `$app`     | `Illuminate\Contracts\Foundation\Application`  |
| `$hooks`   | `App\Services\HookManager`                     |
| `$plugin`  | `array` — manifest merged with runtime metadata |

Example: inject a banner above the forum index.

```php
$hooks->listen('before_content', function () use ($plugin) {
    if (! request()->routeIs('home')) return null;
    return view('plugin.'.$plugin['slug'].'::banner')->render();
});
```

## Hooks

Themes emit hooks via the `@hook('name')` blade directive. A listener returning
a string appends it to the rendered output; returning `null` is a no-op.

| Hook             | Where                   | Purpose                               |
|------------------|-------------------------|---------------------------------------|
| `head`           | `<head>` of every page  | Inject meta tags, analytics, styles   |
| `before_content` | Above `<main>` slot     | Banners, alerts, announcements        |
| `after_content`  | Below `<main>` slot     | Footer modules, trackers              |

Register your own hooks anywhere in the app by calling `$hooks->emit('name')`
(string concat) or `$hooks->filter('name', $value)` (pipeline).

## Optional plugin files

If present, `PluginManager` wires them up automatically on enable:

- `routes.php` — loaded as a standard route file
- `migrations/` — registered as a migration path (`php artisan migrate` picks
  them up)

## Distribution

A plugin directory is self-contained. Zip it, ship it, unzip into `plugins/`,
then `plugin:enable`. No composer package install is required for simple
plugins.
