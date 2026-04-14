@extends('theme::layout')

@section('title', $thread->title.' · '.config('app.name'))

@push('scripts')
    @vite(['resources/js/markdown-editor.js', 'resources/js/thread-live.js'])
@endpush

@section('content')
    @include('theme::partials.breadcrumbs', ['items' => [
        ['label' => 'Forums', 'url' => route('home')],
        ['label' => $forum->name, 'url' => route('forums.show', $forum->slug)],
        ['label' => $thread->title],
    ]])

    <h1 class="text-2xl font-semibold text-gray-900 mb-4">{{ $thread->title }}</h1>

    <div class="space-y-4" data-thread-posts data-thread-id="{{ $thread->id }}">
        @foreach($posts as $post)
            <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="post-{{ $post->id }}">
                <header class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        @if($post->author)
                            <a href="{{ route('users.show', $post->author) }}" class="flex items-center gap-2 hover:opacity-80">
                                <img src="{{ $post->author->avatar_url }}" alt="" class="w-7 h-7 rounded-full" />
                                <span class="font-medium text-gray-800 hover:text-indigo-600">{{ $post->author->name }}</span>
                            </a>
                        @else
                            <span class="text-gray-500">[deleted]</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500">{{ $post->created_at->format('M j, Y g:i A') }}</span>
                        @auth
                            @if(auth()->user()->is_admin || auth()->id() === $post->user_id)
                                <a href="{{ route('posts.edit', $post->id) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('posts.destroy', $post->id) }}" class="inline" onsubmit="return confirm('Delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                                </form>
                            @elseif(auth()->id() !== $post->user_id)
                                <details class="relative">
                                    <summary class="text-gray-500 hover:text-red-600 text-xs cursor-pointer list-none">Report</summary>
                                    <form method="POST" action="{{ route('posts.report', $post->id) }}"
                                          class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded shadow-lg p-3 z-10 space-y-2">
                                        @csrf
                                        <label class="block text-xs font-medium text-gray-700">Reason</label>
                                        <select name="reason" class="w-full rounded border-gray-300 text-sm" required>
                                            <option value="spam">Spam</option>
                                            <option value="harassment">Harassment</option>
                                            <option value="off-topic">Off-topic</option>
                                            <option value="illegal">Illegal content</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <textarea name="note" rows="2" maxlength="500" placeholder="Optional note..."
                                                  class="w-full rounded border-gray-300 text-sm"></textarea>
                                        <button type="submit" class="w-full px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                                            Submit report
                                        </button>
                                    </form>
                                </details>
                            @endif
                        @endauth
                    </div>
                </header>
                <div class="px-4 py-4 prose prose-sm max-w-none prose-indigo">
                    {!! $post->body_html !!}
                </div>
                @if($post->edited_at)
                    <div class="px-4 pb-3 text-xs text-gray-400 italic">edited {{ $post->edited_at->diffForHumans() }}</div>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>

    @auth
        @if(!$thread->is_locked)
            <section class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-lg font-medium text-gray-900 mb-3">Reply</h2>
                <form method="POST" action="{{ route('posts.store', [$forum->slug, $thread->slug]) }}">
                    @csrf
                    <textarea name="body" rows="5" data-markdown
                              class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Write your reply in markdown...">{{ old('body') }}</textarea>
                    @error('body')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">Post Reply</button>
                    </div>
                </form>
            </section>
        @else
            <div class="mt-6 text-center text-sm text-red-600">This thread is locked.</div>
        @endif
    @else
        <div class="mt-6 text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Log in</a> to reply.
        </div>
    @endauth
@endsection
