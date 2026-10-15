@php
    $isEdit = $category->exists;
    $action = $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store');
@endphp

<form method="POST" action="{{ $action }}" class="max-w-lg space-y-6 rounded-lg border border-gray-200 bg-white p-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $category->name) }}"
            required
            maxlength="255"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
        >
        <p class="mt-1 text-xs text-gray-500">Slug dibuat otomatis dari nama.</p>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Kategori' }}
        </button>

        <a href="{{ route('admin.categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:border-gray-900">
            Batal
        </a>
    </div>
</form>
