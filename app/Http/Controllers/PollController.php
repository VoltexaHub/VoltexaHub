<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function vote(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->isClosed()) {
            return back()->with('flash.error', 'This poll is closed.');
        }

        $userId = $request->user()->id;
        $optionIds = (array) $request->input('option', []);
        $optionIds = array_values(array_unique(array_map('intval', $optionIds)));

        if (empty($optionIds)) {
            return back()->with('flash.error', 'Pick at least one option.');
        }

        $validIds = $poll->options->pluck('id')->all();
        $optionIds = array_values(array_intersect($optionIds, $validIds));

        if (empty($optionIds)) {
            return back()->with('flash.error', 'Invalid option.');
        }

        if (! $poll->allow_multiple && count($optionIds) > 1) {
            $optionIds = [array_shift($optionIds)];
        }

        DB::transaction(function () use ($poll, $userId, $optionIds) {
            $previous = PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->pluck('poll_option_id')->all();
            if ($previous) {
                PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->delete();
                PollOption::whereIn('id', $previous)->each(function ($o) {
                    $o->update(['votes_count' => $o->votes()->count()]);
                });
            }

            foreach ($optionIds as $oid) {
                PollVote::create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $oid,
                    'user_id' => $userId,
                ]);
            }

            PollOption::whereIn('id', $optionIds)->each(function ($o) {
                $o->update(['votes_count' => $o->votes()->count()]);
            });
        });

        return back()->with('flash.success', 'Vote recorded.');
    }

    public function clear(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->isClosed()) {
            return back()->with('flash.error', 'This poll is closed.');
        }

        $userId = $request->user()->id;

        DB::transaction(function () use ($poll, $userId) {
            $previous = PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->pluck('poll_option_id')->all();
            if ($previous) {
                PollVote::where('poll_id', $poll->id)->where('user_id', $userId)->delete();
                PollOption::whereIn('id', $previous)->each(function ($o) {
                    $o->update(['votes_count' => $o->votes()->count()]);
                });
            }
        });

        return back()->with('flash.success', 'Vote cleared.');
    }
}
