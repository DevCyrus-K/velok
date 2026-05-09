@extends('layouts.auth', ['title' => '429 - Too Many Requests'])

@section('content')
@include('errors.partials.error-card', [
    'heading' => 'Too Many Requests',
    'message' => $message ?? 'We received too many requests in a short time. Please wait a few minutes and try again later.',
    'image' => '/images/404.svg',
    'imageAlt' => 'Too many requests',
    'primaryLabel' => 'Retry',
    'primaryHref' => 'javascript:location.reload()',
    'primaryClass' => 'btn-success',
    'secondaryLabel' => 'Go Back',
    'secondaryHref' => 'javascript:history.back()',
    'secondaryClass' => 'btn-light',
])
@endsection
