@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Edit Kategori</h1>
    </div>

    @include('admin.categories.form')
</div>
@endsection
