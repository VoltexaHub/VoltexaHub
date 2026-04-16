<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeTheme extends Command
{
    protected $signature = 'make:theme {name : Human-readable theme name (e.g. "Midnight")} {--force : Overwrite if the target directory exists}';

    protected $description = 'Scaffold a new VoltexaHub theme under themes/{slug}/';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        if ($name === '') {
            $this->error('Theme name cannot be empty.');
            return self::INVALID;
        }

        $slug = Str::slug($name);
        $target = base_path("themes/{$slug}");

        if (File::isDirectory($target) && ! $this->option('force')) {
            $this->error("themes/{$slug} already exists. Use --force to overwrite.");
            return self::FAILURE;
        }

        File::ensureDirectoryExists("{$target}/views");
        File::ensureDirectoryExists("{$target}/assets");

        $manifest = json_encode([
            'slug' => $slug,
            'name' => $name,
            'version' => '1.0.0',
            'description' => '',
            'author' => '',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

        File::put("{$target}/theme.json", $manifest);
        File::put("{$target}/views/.gitkeep", '');
        File::put("{$target}/assets/.gitkeep", '');

        $this->info("Theme scaffolded at themes/{$slug}/");
        $this->line('');
        $this->line('Next steps:');
        $this->line("  1. Copy the blade files you want to override from themes/default/views/ into themes/{$slug}/views/.");
        $this->line("  2. Set active_theme to \"{$slug}\" in config/voltexahub.php (or via the admin Settings screen).");
        $this->line('');

        return self::SUCCESS;
    }
}
