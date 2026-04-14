<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OgImageController extends Controller
{
    public function __invoke(Thread $thread): Response
    {
        $thread->load('forum:id,name');

        $hash = md5($thread->title.'|'.$thread->forum?->name.'|'.$thread->updated_at?->timestamp);
        $relative = "og/thread-{$thread->id}-{$hash}.png";
        $disk = Storage::disk('public');

        if (! $disk->exists($relative)) {
            $png = $this->render($thread);
            $disk->put($relative, $png);
        }

        return response($disk->get($relative), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function render(Thread $thread): string
    {
        $w = 1200;
        $h = 630;

        $img = imagecreatetruecolor($w, $h);

        $bg       = imagecolorallocate($img, 0xfb, 0xf8, 0xf2);
        $ink      = imagecolorallocate($img, 0x1a, 0x18, 0x16);
        $muted    = imagecolorallocate($img, 0x6b, 0x65, 0x5e);
        $subtle   = imagecolorallocate($img, 0xa3, 0x9c, 0x93);
        $accent   = imagecolorallocate($img, 0xb4, 0x53, 0x09);
        $border   = imagecolorallocate($img, 0xe6, 0xe0, 0xd3);

        imagefill($img, 0, 0, $bg);

        for ($i = 0; $i < 280; $i++) {
            imagesetpixel($img, random_int(0, $w - 1), random_int(0, $h - 1), $border);
        }

        imagefilledrectangle($img, 0, 0, 8, $h, $accent);

        $pad = 88;

        $forumLabel = strtoupper('THE HUB · '.($thread->forum?->name ?? 'Forum'));
        $this->text($img, 5, $forumLabel, $pad, 80, $muted, 4);

        imagefilledrectangle($img, $pad, 112, $pad + 120, 114, $accent);

        $lines = $this->wrap($thread->title, 24);
        $titleStartY = 170;
        $lineHeight = 72;
        foreach (array_slice($lines, 0, 4) as $i => $line) {
            $this->bigText($img, $line, $pad, $titleStartY + $i * $lineHeight, $ink);
        }

        $this->text($img, 5, 'VOLTEXAHUB', $pad, $h - 90, $accent, 6);
        $this->text($img, 3, 'Community · Discussion', $pad, $h - 60, $subtle, 3);

        ob_start();
        imagepng($img);
        $blob = ob_get_clean();
        imagedestroy($img);

        return $blob;
    }

    private function text($img, int $font, string $text, int $x, int $y, int $color, int $spacing = 0): void
    {
        $chars = mb_str_split($text);
        $cx = $x;
        foreach ($chars as $c) {
            imagestring($img, $font, $cx, $y, $c, $color);
            $cx += imagefontwidth($font) + $spacing;
        }
    }

    private function bigText($img, string $text, int $x, int $y, int $color): void
    {
        $text = mb_substr($text, 0, 40);
        $font = 5;
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);
        $w = $charW * mb_strlen($text);
        $h = $charH;

        if ($w <= 0) return;

        $small = imagecreatetruecolor($w, $h);
        imagealphablending($small, false);
        $transparent = imagecolorallocatealpha($small, 0, 0, 0, 127);
        imagefill($small, 0, 0, $transparent);
        imagesavealpha($small, true);
        imagestring($small, $font, 0, 0, $text, $color);

        $scale = 6;
        imagealphablending($img, true);
        imagecopyresampled($img, $small, $x, $y, 0, 0, $w * $scale, $h * $scale, $w, $h);

        imagedestroy($small);
    }

    private function wrap(string $text, int $width): array
    {
        return explode("\n", wordwrap($text, $width, "\n", true));
    }
}
