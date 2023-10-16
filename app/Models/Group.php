<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use splattner\MailmanAPI\mailmanAPI;
use GuzzleHttp\Client;

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
        'automatic_mode',
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

    public function get_keycloakmembers($group)
    {
        //Keycloak-Infos abfragen
        $client = new Client();
        $res = $client->request('POST', env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/token', [
            'form_params' => [
                'client_id' => 'admin-cli'
                , 'username' => env('KEYCLOAK_API_USER')
                , 'password' => env('KEYCLOAK_API_PASSWORD')
                , 'grant_type' => 'password'
                , 'scope' => 'openid'
            ]
        ]);
        $access_token = json_decode($res->getBody())->access_token;

        $headers = ['Authorization' => "bearer {$access_token}"];
        $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/groups/'.env('KEYCLOAK_PARENTGROUP'), ['headers' => $headers]);

        $kc_groups = json_decode($res->getBody());
        $foundSubgroup = false;
        foreach($kc_groups->subGroups as $subgroup) {
            if($subgroup->name == $group->keycloakGroup) {
                $foundSubgroup = true;
                $kc_group = $subgroup->id;
            }
        }
        if(!$foundSubgroup) return false;
        else {
            $res = $client->request('GET', env('KEYCLOAK_BASE_URL')."/admin/realms/".env('KEYCLOAK_REALM')."/groups/$kc_group/members", ['headers' => $headers]);
            $kc_groupmembers = json_decode($res->getBody());
            return $kc_groupmembers;
        }
    }

}
