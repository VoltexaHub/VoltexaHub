# Themes

A theme is a folder under `themes/` that overrides the Blade views used to
render the public-facing forum. Only one theme is active at a time; it falls
back to `themes/default/` for any view it doesn't override.

## Create a theme

```bash
php artisan make:theme "Midnight"
```

This scaffolds `themes/midnight/`:

```
midnight/
  theme.json   # manifest (slug, name, version, description, author)
  views/       # blade files that override themes/default/views/*
  assets/      # optional static assets
```

## Activate

Set the active theme in `config/voltexahub.php`:

```php
'active_theme' => 'midnight',
```

…or change it via the admin **Settings** screen. Changes take effect on the
next request.

## Overriding views

Any blade file you place under `themes/{slug}/views/` wins over the same file
in `themes/default/views/`. Start by copying only the files you want to
change — unlisted views still render from the default theme.

Common override targets:

| File                        | Renders                      |
|-----------------------------|------------------------------|
| `layout.blade.php`          | Site chrome (header, footer) |
| `forum-index.blade.php`     | `/` (forum list)             |
| `forum-show.blade.php`      | `/forums/{slug}`             |
| `thread-show.blade.php`     | `/forums/{f}/threads/{t}`    |
| `thread-create.blade.php`   | New thread form              |
| `partials/breadcrumbs.blade.php` | Breadcrumb trail         |

The `theme::` namespace resolves against the active theme, so
`@extends('theme::layout')` always picks up the correct layout.

## Hooks your theme should emit

If your theme replaces `layout.blade.php`, keep these directives so plugins
still have their injection points:

```blade
@hook('head')           {{-- inside <head> --}}
@hook('before_content') {{-- above <main> --}}
@hook('after_content')  {{-- below <main> --}}
```

## Assets

Drop static files in `themes/{slug}/assets/` and reference them from blade as
`{{ asset('themes/'.$theme['slug'].'/...') }}` (once the asset-publishing
pipeline is wired up). For CSS/JS you want Vite to process, add entries to the
root `vite.config.js` and import them from a blade via `@vite(...)`.

> **Note:** the `check-vite-inputs` prebuild script will fail the Docker image
> build if you `@vite()` a file that isn't declared in `vite.config.js`
> inputs.

## Distribution

A theme directory is self-contained. Zip it, ship it, unzip into `themes/`,
flip the active-theme setting.
