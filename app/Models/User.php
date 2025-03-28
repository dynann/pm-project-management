<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relationships\HasMany;
use Illuminate\Database\Eloquent\Relationships\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'profileURL',
        'gender',
        'roleSystem',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Enum casting for gender and roleSystem
    protected $casts = [
        'email_verified_at' => 'datetime',
        'gender' => 'string',
        'roleSystem' => 'string',
    ];

    // Relationships
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'ownerID');
    }

    public function memberships()
    {
        return $this->hasMany(Member::class, 'userID');
    }

    public function ownedSprints()
    {
        return $this->hasMany(Sprint::class, 'ownerID');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'userID');
    }

    public function assignedIssues()
    {
        return $this->hasMany(Issue::class, 'assigneeID');
    }

    public function assignerIssues()
    {
        return $this->hasMany(Issue::class, 'assignerID');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'userID');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'userID');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'members', 'userID', 'projectID')
                    ->withPivot('role');
    }
}
?>