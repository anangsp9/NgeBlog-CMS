<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index yang seluruhnya melayani query existing, bukan query baru.
     *
     * - (status, published_at)  -> scopePublished() + latest/oldest('published_at')
     *                              dipakai home, list, feed, sitemap, dan COUNT(*) paginate.
     * - (article_id, status)    -> approvedComments() dan kolom jumlah komentar
     *                              di daftar artikel admin.
     * - (status, created_at)    -> filter status + latest() di panel moderasi
     *                              dan GROUP BY status di dashboard.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'articles_status_published_at_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['article_id', 'status'], 'comments_article_id_status_index');
            $table->index(['status', 'created_at'], 'comments_status_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('articles_status_published_at_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_article_id_status_index');
            $table->dropIndex('comments_status_created_at_index');
        });
    }
};
