@extends('theme::layout')

@section('title', 'Bookmarks · '.config('app.name'))

@section('content')
    <header class="mb-8 pb-5 border-b vx-hairline">
        <p class="vx-meta mb-2">Saved</p>
        <h1 class="vx-display text-4xl font-semibold tracking-tight vx-heading">Bookmarks</h1>
    </header>

    <ul class="vx-row-divide">
        @forelse($bookmarks as $b)
            @php $t = $b->thread; @endphp
            <li class="py-4 flex items-start gap-4">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('threads.show', [$t->forum->slug, $t->slug]) }}"
                       class="vx-display text-lg font-medium vx-heading hover:text-[color:var(--accent)]">{{ $t->title }}</a>
                    <p class="vx-meta normal-case tracking-normal text-[0.72rem] mt-0.5">
                        in {{ $t->forum->name }}
                        @if($t->author) · by <a href="{{ route('users.show', $t->author) }}" class="hover:text-[color:var(--accent)] vx-muted">{{ $t->author->name }}</a>@endif
                        · saved {{ $b->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs shrink-0">
                    <span class="vx-muted tabular-nums">{{ $t->posts_count }} replies</span>
                    <form method="POST" action="{{ route('bookmarks.destroy', $t) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="vx-subtle hover:text-red-500" title="Remove">&times;</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="py-16 text-center vx-display text-xl vx-muted italic">Nothing saved yet.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $bookmarks->links() }}</div>
@endsection
