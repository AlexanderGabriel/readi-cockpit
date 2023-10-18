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

    public function remove_mailmanmember(string $member)
    {
        $mailmanapi = new MailmanAPI($this->mailingListURL, $this->mailingListPassword);
        $mailmanapi->removeMembers([$member]);
        return true;
    }

    public function add_mailmanmember(string $member)
    {
        $mailmanapi = new MailmanAPI($this->mailingListURL, $this->mailingListPassword);
        $mailmanapi->addMembers([$member]);
        return true;
    }

    private function connectToKeycloak()
    {
        if(!isset($this->client)) {
            $this->client = new Client();
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
            $this->headers = ['Authorization' => "bearer {$access_token}"];
        }


    }

    public function get_keycloakgroups() {
        $this->connectToKeycloak();
        $res = $this->client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/groups/'.env('KEYCLOAK_PARENTGROUP'), ['headers' => $this->headers]);
        $kc_groups = json_decode($res->getBody());
        return $kc_groups;
    }

    public function get_keycloakgroupbyname($group) {
        $this->connectToKeycloak();
        $res = $this->client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/groups/'.env('KEYCLOAK_PARENTGROUP'), ['headers' => $this->headers]);

        $kc_groups = json_decode($res->getBody());
        $foundKcGroup = false;
        foreach($kc_groups->subGroups as $subgroup) {
            if($subgroup->name == $group) {
                $foundKcGroup = true;
                $kc_group = $subgroup->id;
            }
        }
        if(!$foundKcGroup) return $foundKcGroup;
        else return $kc_group;
    }

    public function get_keycloakuserbymail($email) {
        $this->connectToKeycloak();
        $res = $this->client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users?email='.$email, ['headers' => $this->headers]);
        $kc_users = json_decode($res->getBody());
        $foundKcUser = false;
        foreach($kc_users as $kc_user) {
            if($kc_user->email == $email) {
                $foundKcUser = true;
                $kc_user_id = $kc_user->id;
            }
        }
        if(!$foundKcUser) return $foundKcUser;
        else return $kc_user_id;
    }

    public function get_keycloakmembers($group)
    {
        $this->connectToKeycloak();
        $kc_groups = $this->get_keycloakgroups();
        $foundSubgroup = false;
        foreach($kc_groups->subGroups as $subgroup) {
            if($subgroup->name == $group->keycloakGroup) {
                $foundSubgroup = true;
                $kc_group = $subgroup->id;
            }
        }
        if(!$foundSubgroup) return false;
        else {
            $res = $this->client->request('GET', env('KEYCLOAK_BASE_URL')."/admin/realms/".env('KEYCLOAK_REALM')."/groups/$kc_group/members", ['headers' => $this->headers]);
            $kc_groupmembers = json_decode($res->getBody());
            return $kc_groupmembers;
        }
    }

    public function add_keycloakmember($group, $email) {
        $this->connectToKeycloak();
        $kc_groupmembers = $this->get_keycloakmembers($group);
        if(!$kc_groupmembers) return false;
        $kc_user_id = $this->get_keycloakuserbymail($email);
        if(!$kc_user_id) return false;
        $kc_group = $this->get_keycloakgroupbyname($group);
        if(!$kc_group) return false;

        if(!in_array($email, $kc_groupmembers)) {
            $this->client->request('PUT', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/'.$kc_group, ['headers' => $this->headers]);
        }
        return true;
    }



}
