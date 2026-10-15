@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Moderasi Komentar</h1>
        <p class="text-sm text-gray-500">Hanya komentar <span class="font-medium text-green-700">approved</span> yang tampil di halaman publik.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['pending', 'approved', 'rejected'] as $option)
            <a
                href="{{ route('admin.comments.index', ['status' => $option]) }}"
                @class([
                    'rounded-full border px-4 py-1.5 text-sm transition',
                    'border-gray-900 bg-gray-900 text-white' => $status === $option,
                    'border-gray-300 bg-white text-gray-700 hover:border-gray-900' => $status !== $option,
                ])
            >
                {{ ucfirst($option) }}
                <span class="ml-1 text-xs opacity-70">{{ $counts[$option] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        @if ($comments->isEmpty())
            <p class="p-10 text-center text-sm text-gray-500">Tidak ada komentar dengan status ini.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($comments as $comment)
                    <li class="p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900">
                                    {{ $comment->name }}
                                    @if ($comment->user_id)
                                        <span class="ml-1 text-xs font-normal text-gray-400">(user terdaftar)</span>
                                    @else
                                        <span class="ml-1 text-xs font-normal text-gray-400">(guest)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $comment->email }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    pada
                                    <a href="{{ route('articles.show', $comment->article) }}" class="text-gray-700 hover:underline">
                                        {{ $comment->article->title }}
                                    </a>
                                </p>
                            </div>

                            <time class="whitespace-nowrap text-xs text-gray-400">
                                {{ \App\Support\Tanggal::pendek($comment->created_at, denganWaktu: true) }}
                            </time>
                        </div>

                        <p class="mt-3 text-sm leading-relaxed text-gray-700">{{ $comment->content }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @foreach (['approved' => 'Setujui', 'rejected' => 'Tolak', 'pending' => 'Tunda'] as $value => $label)
                                <form method="POST" action="{{ route('admin.comments.update', $comment) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $value }}">
                                    <button
                                        type="submit"
                                        @class([
                                            'rounded-md border px-3 py-1.5 text-xs font-medium',
                                            'border-gray-900 bg-gray-900 text-white' => $comment->status === $value,
                                            'border-gray-300 text-gray-700 hover:border-gray-900' => $comment->status !== $value,
                                        ])
                                    >
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach

                            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                                  onsubmit="return confirm('Hapus komentar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:border-red-500">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($comments->hasPages())
        <div>{{ $comments->links() }}</div>
    @endif
</div>
@endsection
