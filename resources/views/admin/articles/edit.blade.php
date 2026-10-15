@extends('layouts.admin')

@section('title', 'Edit Artikel')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Edit Artikel</h1>
        <p class="text-sm text-gray-400">/{{ $article->slug }}</p>
    </div>

    @include('admin.articles.form')
</div>
@endsection
