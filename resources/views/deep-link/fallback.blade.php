@extends('errors.minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))

@push('head')
    @if ($playStoreUrl = config('app.play_store_url'))
        <meta http-equiv="refresh" content="3;url={{ $playStoreUrl }}">
        <script>
            setTimeout(function () {
                window.location.href = @json($playStoreUrl);
            }, 2500);
        </script>
    @endif
@endpush
