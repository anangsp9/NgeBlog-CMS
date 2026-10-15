@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Users</h1>
        <p class="text-sm text-gray-500">{{ $users->total() }} akun terdaftar</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Artikel</th>
                        <th class="px-4 py-3">Komentar</th>
                        <th class="px-4 py-3">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if ($user->isAdmin())
                                    <span class="rounded-full bg-gray-900 px-2 py-0.5 text-xs font-medium text-white">admin</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">visitor</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->articles_count }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->comments_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ \App\Support\Tanggal::pendek($user->created_at) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div>{{ $users->links() }}</div>
    @endif
</div>
@endsection
