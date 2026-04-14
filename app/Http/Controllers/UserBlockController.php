<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserBlockController extends Controller
{
    public function index(Request $request): View
    {
        $blocks = UserBlock::query()
            ->where('blocker_id', $request->user()->id)
            ->with('blocked:id,name,email')
            ->latest()
            ->paginate(25);

        return view('theme::blocks-index', compact('blocks'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('flash.error', "You can't block yourself.");
        }

        UserBlock::firstOrCreate([
            'blocker_id' => $request->user()->id,
            'blocked_id' => $user->id,
        ]);

        return back()->with('flash.success', "You won't see {$user->name}'s posts and they can't message you.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        UserBlock::where('blocker_id', $request->user()->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return back()->with('flash.success', "Unblocked {$user->name}.");
    }
}
