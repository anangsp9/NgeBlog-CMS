<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * 'status' sengaja TIDAK masuk fillable. Status adalah hasil moderasi admin,
     * bukan data dari pengirim komentar; bila masuk fillable, satu baris
     * $comment->update($request->all()) di masa depan akan mengubah menjadi
     * permintaan eskalasi. Nilainya selalu ditentukan oleh server lewat
     * model property.
     *
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'user_id',
        'name',
        'email',
        'content',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
