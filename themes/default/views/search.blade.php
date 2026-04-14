@extends('theme::layout')

@section('title', ($q !== '' ? 'Search: '.$q : 'Search').' · '.config('app.name'))

@section('content')
    <form method="GET" action="{{ route('search') }}" class="mb-6 flex gap-2">
        <input name="q" type="search" value="{{ $q }}" placeholder="Search threads and posts..." autofocus
               class="flex-1 rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">
            Search
        </button>
    </form>

    @if($q === '')
        <p class="text-center text-gray-500 py-12">Enter a query to search.</p>
    @else
        <h1 class="text-xl font-semibold text-gray-900 mb-4">
            Results for <span class="text-indigo-600">{{ $q }}</span>
        </h1>

        @if($threads->isNotEmpty())
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <header class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">Matching threads</h2>
                </header>
                <ul class="divide-y divide-gray-100">
                    @foreach($threads as $thread)
                        <li class="px-4 py-3">
                            <a href="{{ route('threads.show', [$thread->forum->slug, $thread->slug]) }}"
                               class="text-indigo-600 hover:underline font-medium">{{ $thread->title }}</a>
                            <div class="text-xs text-gray-500">
                                in {{ $thread->forum->name }}
                                @if($thread->author)
                                    · by <a href="{{ route('users.show', $thread->author) }}" class="hover:text-indigo-600">{{ $thread->author->name }}</a>
                                @endif
                                · {{ $thread->created_at->diffForHumans() }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($posts && $posts->isNotEmpty())
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <header class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">Matching posts ({{ $posts->total() }})</h2>
                </header>
                <ul class="divide-y divide-gray-100">
                    @foreach($posts as $post)
                        <li class="px-4 py-3">
                            <a href="{{ route('threads.show', [$post->thread->forum->slug, $post->thread->slug]) }}#post-{{ $post->id }}"
                               class="text-indigo-600 hover:underline font-medium">{{ $post->thread->title }}</a>
                            <div class="text-xs text-gray-500">
                                in {{ $post->thread->forum->name }}
                                @if($post->author)
                                    · by <a href="{{ route('users.show', $post->author) }}" class="hover:text-indigo-600">{{ $post->author->name }}</a>
                                @endif
                                · {{ $post->created_at->diffForHumans() }}
                            </div>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit(strip_tags($post->body), 220) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
            <div class="mt-4">{{ $posts->links() }}</div>
        @elseif($threads->isEmpty())
            <p class="text-center text-gray-500 py-12">No matches found.</p>
        @endif
    @endif
@endsection
