@extends('theme::layout')

@php
    $hasFilters = $q !== '' || $forumId || $author !== '' || $from || $to;
@endphp

@section('title', ($q !== '' ? 'Search: '.$q : 'Search').' · '.config('app.name'))

@section('content')
    <header class="mb-8">
        <p class="vx-meta mb-2">The Hub · Search</p>
        <h1 class="vx-display text-4xl font-semibold tracking-tight vx-heading">
            @if($q !== '')Results for <em class="not-italic text-[color:var(--accent)]">"{{ $q }}"</em>@else Search @endif
        </h1>
    </header>

    <form method="GET" action="{{ route('search') }}" class="mb-10 space-y-3 max-w-4xl">
        <div class="flex gap-2">
            <input name="q" type="search" value="{{ $q }}" placeholder="Search threads and posts…" autofocus class="vx-input flex-1 text-base" />
            <button type="submit" class="vx-btn-primary">Search</button>
        </div>

        <details class="vx-card p-0 overflow-hidden" @if($hasFilters && ($forumId || $author || $from || $to)) open @endif>
            <summary class="cursor-pointer list-none px-4 py-2.5 text-sm vx-muted hover:text-[color:var(--text)] flex items-center justify-between border-b vx-hairline">
                <span>
                    <span class="vx-meta mr-2">Filters</span>
                    @if($forumId || $author || $from || $to || $type !== 'all')
                        <span class="text-[color:var(--accent)] text-xs">active</span>
                    @endif
                </span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
            </summary>
            <div class="grid sm:grid-cols-2 gap-4 p-4">
                <label class="block">
                    <span class="vx-meta mb-1 block">Type</span>
                    <select name="type" class="vx-input text-sm">
                        <option value="all" @selected($type==='all')>Everything</option>
                        <option value="threads" @selected($type==='threads')>Threads only</option>
                        <option value="posts" @selected($type==='posts')>Posts only</option>
                    </select>
                </label>
                <label class="block">
                    <span class="vx-meta mb-1 block">Forum</span>
                    <select name="forum" class="vx-input text-sm">
                        <option value="">Any forum</option>
                        @foreach($forums as $f)
                            <option value="{{ $f->id }}" @selected($forumId===$f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="vx-meta mb-1 block">Author (exact name)</span>
                    <input name="author" value="{{ $author }}" type="text" placeholder="e.g. Admin" class="vx-input text-sm" />
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="vx-meta mb-1 block">From</span>
                        <input name="from" type="date" value="{{ $from }}" class="vx-input text-sm" />
                    </label>
                    <label class="block">
                        <span class="vx-meta mb-1 block">To</span>
                        <input name="to" type="date" value="{{ $to }}" class="vx-input text-sm" />
                    </label>
                </div>
            </div>
            <div class="px-4 pb-3 flex justify-end">
                <a href="{{ route('search') }}" class="vx-meta hover:text-[color:var(--accent)]">Clear filters</a>
            </div>
        </details>
    </form>

    @if(! $hasFilters)
        <p class="vx-display text-xl vx-muted italic text-center py-16">What are you looking for?</p>
    @else
        @if($type !== 'posts' && $threads->isNotEmpty())
            <section class="mb-10">
                <h2 class="vx-meta mb-3 text-[color:var(--accent)]">Matching threads</h2>
                <ul class="vx-row-divide">
                    @foreach($threads as $thread)
                        <li class="py-3">
                            <a href="{{ route('threads.show', [$thread->forum->slug, $thread->slug]) }}" class="vx-display font-medium vx-heading hover:text-[color:var(--accent)]">{{ $thread->title }}</a>
                            <p class="vx-meta normal-case tracking-normal text-[0.7rem] mt-0.5">
                                in {{ $thread->forum->name }}
                                @if($thread->author) · by <a href="{{ route('users.show', $thread->author) }}" class="hover:text-[color:var(--accent)]">{{ $thread->author->name }}</a>@endif
                                · {{ $thread->created_at->diffForHumans() }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($type !== 'threads' && $posts && $posts->isNotEmpty())
            <section>
                <h2 class="vx-meta mb-3 text-[color:var(--accent)]">Matching posts · {{ $posts->total() }}</h2>
                <ul class="vx-row-divide">
                    @foreach($posts as $post)
                        <li class="py-4">
                            <a href="{{ route('threads.show', [$post->thread->forum->slug, $post->thread->slug]) }}#post-{{ $post->id }}" class="vx-display font-medium vx-heading hover:text-[color:var(--accent)]">{{ $post->thread->title }}</a>
                            <p class="vx-meta normal-case tracking-normal text-[0.7rem] mt-0.5">
                                in {{ $post->thread->forum->name }}
                                @if($post->author) · by <a href="{{ route('users.show', $post->author) }}" class="hover:text-[color:var(--accent)]">{{ $post->author->name }}</a>@endif
                                · {{ $post->created_at->diffForHumans() }}
                            </p>
                            <p class="vx-muted text-sm mt-2 line-clamp-2">{{ Str::limit(strip_tags($post->body), 240) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
            <div class="mt-6">{{ $posts->links() }}</div>
        @elseif(($type === 'posts' || ($type === 'all' && $threads->isEmpty())) && (! $posts || $posts->isEmpty()))
            <p class="vx-display text-xl vx-muted italic text-center py-16">Nothing matches.</p>
        @endif
    @endif
@endsection
