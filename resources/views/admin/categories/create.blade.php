@extends('layouts.admin')

@section('title', 'Kategori Baru')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Kategori Baru</h1>
    </div>

    @include('admin.categories.form')
</div>
@endsection
