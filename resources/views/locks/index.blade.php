@extends('statamic::layout')
@section('title', __('Locks'))

@section('content')

    <div class="flex items-center mb-6">
        <h1 class="flex-1">{{ __('Locks') }}</h1>
    </div>

    <locks-listing
        :initial-columns="{{ json_encode($initialColumns) }}"
    ></locks-listing>

@endsection
