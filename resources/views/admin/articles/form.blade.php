@php
    $isEdit = $article->exists;
    $action = $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Konten</h2>

                <div class="mt-4 space-y-5">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title', $article->title) }}"
                            required
                            maxlength="255"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="3"
                            required
                            maxlength="1000"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >{{ old('excerpt', $article->excerpt) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Ringkasan singkat, tampil di kartu artikel.</p>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Isi Artikel</label>
                        <textarea
                            id="content"
                            name="content"
                            rows="14"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >{{ old('content', $article->content) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Pisahkan paragraf dengan baris kosong.</p>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Publikasi</h2>

                <div class="mt-4 space-y-5">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >
                            <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="published_at" class="block text-sm font-medium text-gray-700">Tanggal Publish</label>
                        <input
                            id="published_at"
                            name="published_at"
                            type="datetime-local"
                            value="{{ old('published_at', \App\Support\Tanggal::input($article->published_at)) }}"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >
                        <p class="mt-1 text-xs text-gray-500">Diabaikan jika status = Draft.</p>
                        @error('published_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select
                            id="category_id"
                            name="category_id"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $article->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Cover Image</h2>

                @if ($article->cover_image)
                    <img
                        src="{{ $article->coverUrl() }}"
                        alt="Cover saat ini"
                        class="mt-4 aspect-[16/9] w-full rounded-md object-cover"
                    >
                    <p class="mt-2 truncate text-xs text-gray-400">{{ $article->cover_image }}</p>
                @endif

                <label for="cover_image" class="mt-4 block text-sm font-medium text-gray-700">Unggah berkas</label>
                <input
                    id="cover_image"
                    name="cover_image"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700"
                >
                <p class="mt-2 text-xs text-gray-500">jpg, jpeg, png, atau webp. Maksimal 2MB.</p>
                @error('cover_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 border-t border-gray-100 pt-5">
                    <label for="cover_url" class="block text-sm font-medium text-gray-700">Atau tempel link gambar</label>
                    <input
                        id="cover_url"
                        name="cover_url"
                        type="url"
                        value="{{ old('cover_url') }}"
                        placeholder="https://contoh.com/gambar.jpg"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                    >
                    <p class="mt-2 text-xs text-gray-500">
                        Gambar dibaca langsung dari link oleh browser, bukan diunggah.
                        Isi salah satu: berkas <em>atau</em> link, tidak keduanya.
                        Contoh acak: https://picsum.photos/seed/judul-artikel/1200/675
                    </p>
                    @error('cover_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Artikel' }}
        </button>

        <a href="{{ route('admin.articles.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:border-gray-900">
            Batal
        </a>
    </div>
</form>
