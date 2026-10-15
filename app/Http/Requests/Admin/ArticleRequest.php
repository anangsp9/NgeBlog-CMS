<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'excerpt' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'cover_url' => [
                'nullable',
                'url:http,https',
                'max:255',
                // Cover cuma boleh ditentukan lewat satu jalur. Bila keduanya
                // diisi, file yang menang akan membuat URL yang diketik hilang
                // diam-diam, sehingga lebih jelas ditolak di sini.
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (filled($value) && $this->hasFile('cover_image')) {
                        $fail('Isi salah satu saja: upload file cover atau masukkan URL gambar, tidak keduanya.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cover_image.max' => 'Ukuran gambar maksimal 2MB.',
            'cover_image.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'cover_url.url' => 'URL gambar tidak valid, harus diawali http:// atau https://.',
            'cover_url.max' => 'URL gambar maksimal 255 karakter.',
        ];
    }
}
