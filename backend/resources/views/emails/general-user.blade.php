@extends('layouts.email')

@section('title', $subject)

@section('content')
    <div style="white-space: pre-wrap;">{!! nl2br(e($body)) !!}</div>
@endsection
