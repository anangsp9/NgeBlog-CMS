@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500">Ringkasan data NgeBlog! Klik salah satu kartu untuk mengelola datanya.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $stats = [
                [
                    'label' => 'Total Artikel',
                    'value' => $totalArticles,
                    'hint' => $publishedArticles.' published, '.$draftArticles.' draft',
                    'to' => route('admin.articles.index'),
                ],
                [
                    'label' => 'Kategori',
                    'value' => $totalCategories,
                    'hint' => 'Kelola daftar kategori',
                    'to' => route('admin.categories.index'),
                ],
                [
                    'label' => 'Total Komentar',
                    'value' => $totalComments,
                    'hint' => $pendingComments.' menunggu persetujuan',
                    'to' => route('admin.comments.index', ['status' => 'pending']),
                ],
                [
                    'label' => 'Total User',
                    'value' => $totalUsers,
                    'hint' => 'Daftar akun terdaftar',
                    'to' => route('admin.users.index'),
                ],
            ];
        @endphp

        @foreach ($stats as $stat)
            <a
                href="{{ $stat['to'] }}"
                class="group rounded-lg border border-gray-200 bg-white p-5 transition hover:border-gray-900 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
            >
                <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400 group-hover:text-gray-600">{{ $stat['hint'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Status Artikel</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">
                    Kelola artikel &rarr;
                </a>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Published</dt>
                    <dd class="font-medium text-gray-900">{{ $publishedArticles }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Draft</dt>
                    <dd class="font-medium text-gray-900">{{ $draftArticles }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Moderasi Komentar</h2>
                <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">
                    Buka moderasi &rarr;
                </a>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Pending</dt>
                    <dd class="font-medium text-amber-600">{{ $pendingComments }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Approved</dt>
                    <dd class="font-medium text-green-600">{{ $approvedComments }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Rejected</dt>
                    <dd class="font-medium text-red-600">{{ $rejectedComments }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
