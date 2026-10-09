@extends('errors.minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))

@push('head')
    @if ($playStoreUrl = config('app.play_store_url'))
        <meta http-equiv="refresh" content="3;url={{ $playStoreUrl }}">
        <script>
                window.location.href = @json($playStoreUrl);
        </script>
    @endif
@endpush
