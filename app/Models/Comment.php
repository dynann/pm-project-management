<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'userID',
        'issueID',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issueID');
    }
} 