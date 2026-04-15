<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Markdown;
use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    private const DEFAULTS = [
        'privacy' => [
            'title' => 'Privacy Policy',
            'body' => <<<MD
## What we collect

- Your account details (name, handle, email, password hash).
- Posts, threads, messages, and reactions you create — these are public to other signed-in members unless explicitly private.
- Technical access logs (IP, user-agent) for abuse prevention, rotated on a short schedule.

## How we use it

- To operate the forum: authenticate you, show content, deliver notifications.
- To send digest and per-event emails you've opted into.
- To detect and respond to abuse.

We don't sell your data.

## Your controls

- Edit or delete your posts any time.
- Change or disable email / in-app notifications in **Profile → Notifications**.
- Delete your account from **Profile → Delete Account** — this removes your profile and most of your content.

## Contact

Questions? Reach out through the admin account on the forum.
MD,
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'body' => <<<MD
## Using the forum

By registering, you agree to follow community norms:

- Be civil. No harassment, hate speech, or targeted abuse.
- No spam, unsolicited advertising, or coordinated inauthentic activity.
- No illegal content (piracy, CSAM, credible threats, etc.).

Moderators may remove content, lock threads, or suspend accounts at their discretion.

## Your content

You keep ownership of what you post. By posting, you grant us a non-exclusive, royalty-free licence to host, display, and distribute it within the forum.

## Account termination

You can delete your account at any time. We may suspend or terminate accounts that violate these terms.

## Changes

We may update these terms; significant changes will be announced sitewide.
MD,
        ],
    ];

    public function __invoke(string $page): View
    {
        abort_unless(array_key_exists($page, self::DEFAULTS), 404);

        $title = Setting::get("pages.{$page}.title", self::DEFAULTS[$page]['title']);
        $body = Setting::get("pages.{$page}.body", self::DEFAULTS[$page]['body']);
        $html = app(Markdown::class)->toHtml($body);

        return view('theme::static-page', [
            'pageKey' => $page,
            'title' => $title,
            'html' => $html,
        ]);
    }
}
