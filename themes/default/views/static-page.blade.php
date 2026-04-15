@extends('theme::layout')

@section('title', $title.' · '.config('app.name'))

@section('content')
    <header class="mb-8 pb-5 border-b vx-hairline">
        <p class="vx-meta mb-2">{{ config('app.name') }}</p>
        <h1 class="vx-display text-4xl font-semibold tracking-tight vx-heading">{{ $title }}</h1>
    </header>

    <article class="vx-prose max-w-2xl">
        {!! $html !!}
    </article>
@endsection
