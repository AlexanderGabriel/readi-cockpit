<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'keycloakGroup',
        'keycloakAdminGroup',
        'moderated',
        'has_mailinglist',
        'mailingListURL',
        'mailingListPassword',
    ];

    protected $hidden = [
        'mailingListPassword',
    ];

    public function members()
    {
        return $this->hasMany(Groupmember::class);
    }

}
