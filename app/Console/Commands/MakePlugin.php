<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePlugin extends Command
{
    protected $signature = 'make:plugin {name : Human-readable plugin name (e.g. "Welcome Banner")} {--force : Overwrite if the target directory exists}';

    protected $description = 'Scaffold a new VoltexaHub plugin under plugins/{slug}/';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        if ($name === '') {
            $this->error('Plugin name cannot be empty.');
            return self::INVALID;
        }

        $slug = Str::slug($name);
        $target = base_path("plugins/{$slug}");

        if (File::isDirectory($target) && ! $this->option('force')) {
            $this->error("plugins/{$slug} already exists. Use --force to overwrite.");
            return self::FAILURE;
        }

        File::ensureDirectoryExists("{$target}/views");

        $manifest = json_encode([
            'slug' => $slug,
            'name' => $name,
            'version' => '1.0.0',
            'description' => '',
            'author' => '',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

        File::put("{$target}/plugin.json", $manifest);
        File::put("{$target}/plugin.php", $this->bootstrapStub());
        File::put("{$target}/views/.gitkeep", '');

        $this->info("Plugin scaffolded at plugins/{$slug}/");
        $this->line('');
        $this->line('Next steps:');
        $this->line("  1. Edit plugins/{$slug}/plugin.php to register hooks.");
        $this->line("  2. php artisan plugin:enable {$slug}");
        $this->line('');

        return self::SUCCESS;
    }

    private function bootstrapStub(): string
    {
        return <<<'PHP'
<?php

/**
 * Plugin bootstrap.
 *
 * Available in scope:
 * @var \Illuminate\Contracts\Foundation\Application $app
 * @var \App\Services\HookManager $hooks
 * @var array $plugin  (manifest merged with runtime metadata)
 */

// Example: inject content above the forum index body.
//
// $hooks->listen('before_content', function () use ($plugin) {
//     if (! request()->routeIs('home')) {
//         return null;
//     }
//     return view('plugin.'.$plugin['slug'].'::banner')->render();
// });

PHP;
    }
}
