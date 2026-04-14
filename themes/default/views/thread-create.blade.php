@extends('theme::layout')

@section('title', 'New thread in '.$forum->name)

@push('scripts')
    @vite('resources/js/markdown-editor.js')
@endpush

@section('content')
    @include('theme::partials.breadcrumbs', ['items' => [
        ['label' => 'Forums', 'url' => route('home')],
        ['label' => $forum->name, 'url' => route('forums.show', $forum->slug)],
        ['label' => 'New Thread'],
    ]])

    <header class="mb-8">
        <p class="vx-meta mb-2">Posting to · {{ $forum->name }}</p>
        <h1 class="vx-display text-4xl font-semibold tracking-tight vx-heading">Start a new thread</h1>
    </header>

    <form method="POST" action="{{ route('threads.store', $forum->slug) }}" class="space-y-6 max-w-3xl">
        @csrf
        <div>
            <label class="vx-meta mb-2 block">Title</label>
            <input name="title" value="{{ old('title') }}" type="text" required
                   class="vx-input text-xl vx-display font-medium" style="padding:0.7rem 1rem;" placeholder="Give it a headline worth reading…" />
            @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="vx-meta mb-2 block">Body</label>
            <textarea name="body" rows="14" required data-markdown class="vx-input">{{ old('body') }}</textarea>
            @error('body')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <details class="vx-card overflow-hidden" @if(old('poll_question')) open @endif>
            <summary class="cursor-pointer list-none px-4 py-3 border-b vx-hairline flex items-center justify-between">
                <span><span class="vx-meta mr-2">Optional</span><span class="font-medium">Add a poll</span></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
            </summary>
            <div class="p-4 space-y-4">
                <div>
                    <label class="vx-meta mb-2 block">Question</label>
                    <input name="poll_question" value="{{ old('poll_question') }}" type="text" maxlength="250"
                           placeholder="e.g. What should we build next?" class="vx-input" />
                </div>
                <div>
                    <label class="vx-meta mb-2 block">Options <span class="normal-case tracking-normal opacity-60">(at least 2, up to 10)</span></label>
                    <div class="space-y-2" id="poll-options">
                        @foreach(old('poll_options', ['', '']) as $i => $opt)
                            <input name="poll_options[]" value="{{ $opt }}" type="text" maxlength="200"
                                   placeholder="Option {{ $i + 1 }}" class="vx-input text-sm" />
                        @endforeach
                    </div>
                    <button type="button" onclick="var d=document.getElementById('poll-options'); if(d.children.length<10){var i=document.createElement('input');i.name='poll_options[]';i.type='text';i.maxLength=200;i.placeholder='Option '+(d.children.length+1);i.className='vx-input text-sm';d.appendChild(i);}"
                            class="mt-2 vx-meta hover:text-[color:var(--accent)]">+ Add option</button>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="poll_allow_multiple" value="1" @checked(old('poll_allow_multiple'))
                           class="rounded" style="border-color: var(--border); color: var(--accent);" />
                    Allow voters to pick multiple options
                </label>
            </div>
        </details>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('forums.show', $forum->slug) }}" class="vx-btn-secondary">Cancel</a>
            <button type="submit" class="vx-btn-primary">Publish Thread</button>
        </div>
    </form>
@endsection
