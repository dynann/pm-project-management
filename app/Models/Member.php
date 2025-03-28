<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'userID',
        'projectID',
        'role',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectID');
    }
} 