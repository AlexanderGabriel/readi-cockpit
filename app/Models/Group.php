<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use splattner\MailmanAPI\mailmanAPI;

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

    public function get_mailmanmembers()
    {
        $mailmanapi = new MailmanAPI($this->mailingListURL, $this->mailingListPassword);
        $mailmanmembers = $mailmanapi->getMemberlist();
        return $mailmanmembers;
    }

    public function remove_mailmanmembers(array $members)
    {
        $mailmanapi = new MailmanAPI($this->mailingListURL, $this->mailingListPassword);
        if(is_array($members) && count($members) > 0) {
            $mailmanapi->removeMembers($members);
        }
        return true;
    }

    public function add_mailmanmembers(array $members)
    {
        $mailmanapi = new MailmanAPI($this->mailingListURL, $this->mailingListPassword);
        if(is_array($members) && count($members) > 0) {
            $mailmanapi->addMembers($members);
        }
        return true;
    }

}
