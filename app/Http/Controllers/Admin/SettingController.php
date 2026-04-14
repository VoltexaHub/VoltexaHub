<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'oauth' => [
                    'github' => [
                        'client_id' => Setting::get('oauth.github.client_id', ''),
                        'has_secret' => (bool) Setting::get('oauth.github.client_secret'),
                    ],
                    'google' => [
                        'client_id' => Setting::get('oauth.google.client_id', ''),
                        'has_secret' => (bool) Setting::get('oauth.google.client_secret'),
                    ],
                ],
            ],
            'callbackUrls' => [
                'github' => url('/auth/github/callback'),
                'google' => url('/auth/google/callback'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'github_client_id' => ['nullable', 'string', 'max:200'],
            'github_client_secret' => ['nullable', 'string', 'max:500'],
            'google_client_id' => ['nullable', 'string', 'max:200'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::put('oauth.github.client_id', $data['github_client_id'] ?? null);
        if (! empty($data['github_client_secret'])) {
            Setting::put('oauth.github.client_secret', $data['github_client_secret'], encrypted: true);
        }

        Setting::put('oauth.google.client_id', $data['google_client_id'] ?? null);
        if (! empty($data['google_client_secret'])) {
            Setting::put('oauth.google.client_secret', $data['google_client_secret'], encrypted: true);
        }

        return back()->with('flash.success', 'Settings saved.');
    }

    public function clearSecret(Request $request): RedirectResponse
    {
        $provider = $request->validate(['provider' => ['required', 'in:github,google']])['provider'];
        Setting::forget("oauth.{$provider}.client_secret");

        return back()->with('flash.success', ucfirst($provider).' secret cleared.');
    }
}
