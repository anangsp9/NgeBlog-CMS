@extends('layouts.admin')

@section('title', 'Artikel Baru')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Artikel Baru</h1>
        <p class="text-sm text-gray-500">Slug dibuat otomatis dari judul.</p>
    </div>

    @include('admin.articles.form')
</div>
@endsection
