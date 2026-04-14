<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notify_reply_email' => ['sometimes', 'boolean'],
            'notify_reply_app'   => ['sometimes', 'boolean'],
            'notify_pm_email'    => ['sometimes', 'boolean'],
            'notify_pm_app'      => ['sometimes', 'boolean'],
            'digest_frequency'   => ['sometimes', 'string', 'in:off,weekly'],
        ]);

        $request->user()->update([
            'notify_reply_email' => (bool) ($data['notify_reply_email'] ?? false),
            'notify_reply_app'   => (bool) ($data['notify_reply_app']   ?? false),
            'notify_pm_email'    => (bool) ($data['notify_pm_email']    ?? false),
            'notify_pm_app'      => (bool) ($data['notify_pm_app']      ?? false),
            'digest_frequency'   => $data['digest_frequency'] ?? 'off',
        ]);

        return back()->with('flash.success', 'Notification preferences saved.');
    }
}
