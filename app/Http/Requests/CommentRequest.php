<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Pengunjung yang belum login wajib mengisi nama & email, sedangkan user
     * terdaftar otomatis memakai identitas dari akunnya.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isGuest = $this->user() === null;

        return [
            'name' => [$isGuest ? 'required' : 'nullable', 'string', 'max:255'],
            'email' => [$isGuest ? 'required' : 'nullable', 'string', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 2000 karakter.',
        ];
    }

    /**
     * Identitas pengirim komentar, mengikuti status login Pengunjung.
     *
     * @return array<string, mixed>
     */
    public function authorAttributes(): array
    {
        $user = $this->user();

        return [
            'user_id' => $user?->getKey(),
            'name' => $user?->name ?? $this->validated('name'),
            'email' => $user?->email ?? $this->validated('email'),
        ];
    }
}
