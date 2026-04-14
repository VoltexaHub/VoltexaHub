<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->databaseReady()) {
            return;
        }

        foreach (['github', 'google'] as $provider) {
            $id = Setting::get("oauth.{$provider}.client_id");
            $secret = Setting::get("oauth.{$provider}.client_secret");

            if ($id) {
                config(["services.{$provider}.client_id" => $id]);
            }
            if ($secret) {
                config(["services.{$provider}.client_secret" => $secret]);
            }
        }
    }

    private function databaseReady(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
