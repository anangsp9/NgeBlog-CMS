@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Articles</h1>
            <p class="text-sm text-gray-500">{{ $articles->total() }} artikel</p>
        </div>

        <a href="{{ route('admin.articles.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            + Artikel Baru
        </a>
    </div>

    <form method="GET" action="{{ route('admin.articles.index') }}" class="flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 bg-white p-4">
        <div class="min-w-56 flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700">Cari</label>
            <input
                id="search"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Judul atau excerpt..."
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
                id="status"
                name="status"
                class="mt-1 block rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
                <option value="">Semua</option>
                <option value="published" @selected($status === 'published')>Published</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
        </div>

        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            Terapkan
        </button>

        @if ($search || $status)
            <a href="{{ route('admin.articles.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:border-gray-900">
                Reset
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        @if ($articles->isEmpty())
            <p class="p-10 text-center text-sm text-gray-500">Tidak ada artikel.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Komentar</th>
                            <th class="px-4 py-3">Diperbarui</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr>
                                <td class="max-w-xs px-4 py-3">
                                    <a href="{{ route('articles.show', $article) }}" class="font-medium text-gray-900 hover:underline">
                                        {{ $article->title }}
                                    </a>
                                    <p class="truncate text-xs text-gray-400">/{{ $article->slug }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $article->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($article->status === 'published')
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">published</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">draft</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $article->approved_comments_count }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ \App\Support\Tanggal::pendek($article->updated_at) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="font-medium text-gray-700 hover:text-gray-900">Edit</a>

                                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                                              onsubmit="return confirm('Hapus artikel &quot;{{ $article->title }}&quot;?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($articles->hasPages())
        <div>{{ $articles->links() }}</div>
    @endif
</div>
@endsection
