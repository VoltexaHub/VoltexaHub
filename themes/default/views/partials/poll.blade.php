@php
    $total = $poll->totalVotes();
    $myVotes = $poll->userVoteOptionIds(auth()->id());
    $hasVoted = count($myVotes) > 0;
    $closed = $poll->isClosed();
    $showResults = $hasVoted || $closed || ! auth()->check();
@endphp

<section class="mb-10 vx-card p-5">
    <header class="mb-4 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="vx-meta mb-1">Poll{{ $poll->allow_multiple ? ' · multi-choice' : '' }}{{ $closed ? ' · closed' : '' }}</p>
            <h2 class="vx-display text-xl font-semibold vx-heading leading-tight">{{ $poll->question }}</h2>
        </div>
        <span class="vx-meta shrink-0 tabular-nums">{{ $total }} vote{{ $total === 1 ? '' : 's' }}</span>
    </header>

    @auth
        @if(! $showResults)
            <form method="POST" action="{{ route('polls.vote', $poll) }}" class="space-y-2">
                @csrf
                @foreach($poll->options as $opt)
                    <label class="flex items-center gap-3 px-3 py-2 rounded-lg border vx-hairline hover:border-[color:var(--accent)] cursor-pointer transition-colors">
                        <input type="{{ $poll->allow_multiple ? 'checkbox' : 'radio' }}" name="option[]" value="{{ $opt->id }}"
                               class="text-[color:var(--accent)] focus:ring-[color:var(--accent)] focus:ring-offset-0"
                               style="{{ $poll->allow_multiple ? 'border-radius:0.25rem;' : 'border-radius:9999px;' }} border-color: var(--border);" />
                        <span class="flex-1">{{ $opt->text }}</span>
                    </label>
                @endforeach
                <div class="pt-2 flex justify-end">
                    <button type="submit" class="vx-btn-primary">Vote</button>
                </div>
            </form>
        @else
            <div class="space-y-2">
                @foreach($poll->options as $opt)
                    @php
                        $pct = $total > 0 ? round($opt->votes_count / $total * 100) : 0;
                        $mine = in_array($opt->id, $myVotes);
                    @endphp
                    <div class="px-3 py-2.5 rounded-lg border vx-hairline relative overflow-hidden {{ $mine ? 'border-[color:var(--accent)]' : '' }}">
                        <div class="absolute inset-y-0 left-0" style="width: {{ $pct }}%; background: color-mix(in oklch, var(--accent) {{ $mine ? '18%' : '7%' }}, transparent);"></div>
                        <div class="relative flex items-center justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 min-w-0 truncate">
                                @if($mine)<span class="text-[color:var(--accent)] text-xs">●</span>@endif
                                <span class="truncate">{{ $opt->text }}</span>
                            </span>
                            <span class="vx-meta shrink-0 tabular-nums">{{ $pct }}% · {{ $opt->votes_count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($hasVoted && ! $closed)
                <form method="POST" action="{{ route('polls.clear', $poll) }}" class="mt-3 flex justify-end">
                    @csrf @method('DELETE')
                    <button type="submit" class="vx-meta hover:text-[color:var(--accent)]">Clear my vote</button>
                </form>
            @endif
        @endif
    @else
        <div class="space-y-2">
            @foreach($poll->options as $opt)
                @php $pct = $total > 0 ? round($opt->votes_count / $total * 100) : 0; @endphp
                <div class="px-3 py-2.5 rounded-lg border vx-hairline relative overflow-hidden">
                    <div class="absolute inset-y-0 left-0" style="width: {{ $pct }}%; background: color-mix(in oklch, var(--accent) 7%, transparent);"></div>
                    <div class="relative flex items-center justify-between gap-4 text-sm">
                        <span class="truncate">{{ $opt->text }}</span>
                        <span class="vx-meta shrink-0 tabular-nums">{{ $pct }}% · {{ $opt->votes_count }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="vx-meta mt-3"><a href="{{ route('login') }}" class="text-[color:var(--accent)] hover:underline">Log in</a> to vote.</p>
    @endauth
</section>
