@extends('theme::layout')

@section('title', 'Blocked users · '.config('app.name'))

@section('content')
    <header class="mb-8 pb-5 border-b vx-hairline">
        <p class="vx-meta mb-2">Privacy</p>
        <h1 class="vx-display text-4xl font-semibold tracking-tight vx-heading">Blocked users</h1>
        <p class="vx-muted mt-2 text-sm max-w-xl">Blocked users can't message you, and their posts won't appear in threads you read.</p>
    </header>

    <ul class="vx-row-divide">
        @forelse($blocks as $block)
            @php $u = $block->blocked; @endphp
            <li class="py-4 flex items-center gap-4">
                @if($u)
                    <img src="{{ $u->avatar_url }}" alt="" class="w-10 h-10 rounded-full border vx-hairline" />
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('users.show', $u) }}" class="vx-display font-medium vx-heading hover:text-[color:var(--accent)]">{{ $u->name }}</a>
                        <p class="vx-meta normal-case tracking-normal text-[0.7rem] mt-0.5">
                            Blocked {{ $block->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('blocks.destroy', $u) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="vx-btn-secondary text-xs py-1.5 px-3">Unblock</button>
                    </form>
                @else
                    <span class="vx-muted italic">[deleted user]</span>
                @endif
            </li>
        @empty
            <li class="py-16 text-center vx-display text-xl vx-muted italic">Nobody blocked.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $blocks->links() }}</div>
@endsection
