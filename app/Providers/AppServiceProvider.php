<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Satu-satunya definisi "apakah pengguna ini admin". Middleware
        // EnsureUserIsAdmin, ArticleRequest, CategoryRequest, dan CommentRequest
        // semuanya menanyakan kemampuan ini, sehingga kebijakan akses admin tidak
        // tersebar di beberapa file dan mudah diuji lewat Gate.
        //
        // Tanpa tipe pada $user karena Gate boleh mengevaluasi kemampuan untuk tamu
        // (user null); pada titik itu jawaban yang benar hanyalah false.
        Gate::define('admin', fn ($user) => $user instanceof User && $user->isAdmin());
    }
}
