<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Groupmember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use App\Mail\WaitingForJoin;
use App\Mail\JoinApproved;
use App\Mail\JoinDeclined;

class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::latest()->paginate(10);

        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Nur Administratoren dürfen Gruppen anlagen
        if(!Auth::hasRole('Administratoren')) {
            return abort(403);
        }

        return view('groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Nur Administratoren dürfen Gruppen speichern (neu)
        if(!Auth::hasRole('Administratoren')) {
            return abort(403);
        }

        if(!$request->has('has_mailinglist')) {
            $newHasMailinglist = 0;
        } else {
            $newHasMailinglist = 1;
        }
        if(!$request->has('moderated')) {
            $newModerated = 0;
        } else {
            $newModerated = 1;
        }
        if(!$request->has('automatic_mode')) {
            $newautomatic_mode = 0;
        } else {
            $newautomatic_mode = 1;
        }

        Group::create([
            "name" => $request->name,
            "description" => $request->description,
            "keycloakGroup" => $request->keycloakGroup,
            "keycloakAdminGroup" => $request->keycloakAdminGroup,
            "moderated" => $newModerated,
            "has_mailinglist" => $newHasMailinglist,
            "mailingListURL" => $request->mailingListURL,
            "mailingListAdmin" => $request->mailingListAdmin,
            "mailingListPassword" => $request->mailingListPassword,
            "automatic_mode" => $newautomatic_mode,
        ]);

        return redirect()->route('groups.index')
            ->withSuccess(__('Gruppe erfolgreich erstellt.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = Group::findOrFail($id);
        $groupmembers = $group->members;
        $groupmemberemails = Array();
        foreach($groupmembers as $groupmember) {
            array_push($groupmemberemails, $groupmember->email);
        }
        if(Auth::check() && in_array(Auth::user()->email, $groupmemberemails)) {
            $canJoinGroup = false;
        } else {
            $canJoinGroup = true;
        }

        $groupmemberemails = Array();
        foreach ($groupmembers as $groupmember) {
            array_push($groupmemberemails, $groupmember->email);
        }
        $inCockpitNotInKeycloaks = Array();
        $inKeyCloakNotInCockpits = Array();
        $notToBeInKeyCloaks = Array();
        $kc_groupmembers = $group->get_keycloakmembers($group);
        if($kc_groupmembers) {
            $kc_groupmemberemails = Array();
            foreach ($kc_groupmembers as $kc_groupmember) {
                array_push($kc_groupmemberemails, $kc_groupmember->email);
            }
            foreach ($groupmembers as $groupmember) {
                if(!$groupmember->toBeInNextCloud && in_array($groupmember->email, $kc_groupmemberemails)) array_push($notToBeInKeyCloaks, $groupmember->email);
            }

            $inCockpitNotInKeycloaks = array_diff($groupmemberemails, $kc_groupmemberemails);
            $inKeyCloakNotInCockpits = array_diff($kc_groupmemberemails, $groupmemberemails);
        }
        else {
            $inCockpitNotInKeycloaks = $groupmemberemails;
        }
        
        $inMailmanNotInCockpits = Array();
        $inCockpitNotInMailmans = Array();
        $notToBeInMailmans = Array();
        if($group->has_mailinglist) {
            $mailmanMembers = $group->get_mailmanmembers();
            if(count($mailmanMembers) > 0) {
                $inMailmanNotInCockpits = array_diff($mailmanMembers, $groupmemberemails);
            }
            $inCockpitNotInMailmans = array_diff($groupmemberemails, $mailmanMembers);
            foreach ($groupmembers as $groupmember) {
                if(!$groupmember->toBeInMailinglist && in_array($groupmember->email, $mailmanMembers)) array_push($notToBeInMailmans, $groupmember->email);
            }
        }

        return view('groups.show', [
            'group' => $group
            , 'groupmembers' => $groupmembers
            , 'canJoinGroup' => $canJoinGroup
            , 'inKeyCloakNotInCockpits' => $inKeyCloakNotInCockpits
            , 'inCockpitNotInKeycloaks' => $inCockpitNotInKeycloaks
            , 'inCockpitNotInMailmans' => $inCockpitNotInMailmans
            , 'inMailmanNotInCockpits' => $inMailmanNotInCockpits
            , 'notToBeInKeyCloaks' => $notToBeInKeyCloaks
            , 'notToBeInMailmans' => $notToBeInMailmans
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $group = Group::findOrFail($id);
        //Nur Administratoren dürfen Gruppen bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        return view('groups.edit', [
            'group' => $group
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $group = Group::findOrFail($id);
        //Nur Administratoren dürfen Gruppen aktualisieren
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $oldGroupPassword = $group->mailingListPassword;
        if(!$request->has("mailingListPassword") || $request->mailingListPassword == "") {
            $newMailingListPassword = $oldGroupPassword;
        } else {
            $newMailingListPassword = $request->mailingListPassword;
        }
        if(!$request->has('has_mailinglist')) {
            $newHasMailinglist = 0;
        } else {
            $newHasMailinglist = 1;
        }
        if(!$request->has('moderated')) {
            $newModerated = 0;
        } else {
            $newModerated = 1;
        }
        if(!$request->has('automatic_mode')) {
            $newautomatic_mode = 0;
        } else {
            $newautomatic_mode = 1;
        }

        if(Auth::hasRole('Administratoren')) {
            $group->update([
                "name" => $request->name,
                "description" => $request->description,
                "keycloakGroup" => $request->keycloakGroup,
                "keycloakAdminGroup" => $request->keycloakAdminGroup,
                "moderated" => $newModerated,
                "has_mailinglist" => $newHasMailinglist,
                "mailingListURL" => $request->mailingListURL,
                "mailingListAdmin" => $request->mailingListAdmin,
                "mailingListPassword" => $newMailingListPassword,
                "automatic_mode" => $newautomatic_mode,
                ]
            );
        }

        if(Auth::user()->hasRole($group->keycloakAdminGroup)) {
            $group->update([
                "description" => $request->description,
                "moderated" => $newModerated,
                ]
            );
        }

        return redirect()->route('groups.show', $id)
            ->withSuccess(__('Gruppe aktualisiert.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Nur Administratoren dürfen Gruppen löschen
        if(!Auth::hasRole('Administratoren')) {
            return abort(403);
        }

        Group::findOrFail($id)->delete();

        return redirect()->route('groups.index')
            ->withSuccess(__('Gruppe erfolgreich gelöscht.'));
    }

    public function addmember(request $request, string $id) {
        $group = Group::findOrFail($id);
        //Nur Administratoren dürfen Gruppenmitglieder hinzufüngen
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        Group::findOrFail($id);
        $groupmember = Groupmember::where([["email", $request->email], ["group_id", $id]])->get();
        if(count($groupmember) > 0) {
            return redirect()->route('groups.show', $id)
            ->withSuccess(__('Mitglied exisitert bereits.'));
        }
        else {
            Groupmember::create([
                "email" => $request->email,
                "group_id" => $id
            ]);
            return redirect()->route('groups.show', $id)
                ->withSuccess(__('Mitglied hinzugefügt.'));
        }
    }

    public function deletemember(string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen Gruppenmitglieder entfernen
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        //Member deleted while waiting for join?
        if($groupmember->waitingForJoin) {
            $groupname = $group->name;
            Mail::to($groupmember->email)->send(new JoinDeclined($groupname));
        }
        $groupmember->delete();

        return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('Mitglied wurde gelöscht.'));
    }

    public function toggleToBeInMailinglist(request $request, string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $groupmember->update([
            "email" => $groupmember->email,
            "toBeInNextCloud" => $groupmember->toBeInNextCloud,
            "toBeInMailinglist" => !$groupmember->toBeInMailinglist,
        ]);
        return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('Mailinglistenstatus wurde abgeändert.'));
    }

    public function toggleToBeInNextCloud(request $request, string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $groupmember->update([
            "email" => $groupmember->email,
            "toBeInNextCloud" => !$groupmember->toBeInNextCloud,
            "toBeInMailinglist" => $groupmember->toBeInMailinglist,
        ]);
        return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('Mailinglistenstatus wurde abgeändert.'));
    }

    public function toggleToBeInGroup(request $request, string $id) {
        $group = Group::findOrFail($id);

        $groupmembers = $group->members;
        $groupmemberemails = Array();
        foreach($groupmembers as $groupmember) {
            array_push($groupmemberemails, $groupmember->email);
        }
        if(in_array(Auth::user()->email, $groupmemberemails)) {
            $groupmember = Groupmember::where([["email", Auth::user()->email], ["group_id", $id]])->get();
            $groupmember[0]->delete();

            return redirect()->route('groups.show', $id)
            ->withSuccess(__('Du bist jetzt nicht mehr in der Projektgruppe.'));

        } else {
            Groupmember::create([
                "email" => Auth::user()->email
                , "group_id" => $id
                , "waitingForJoin" => $group->moderated
            ]);

            if($group->moderated) {
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
                $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/groups/'.env('KEYCLOAK_PARENTGROUP_ADMINS'), ['headers' => $headers]);

                $kc_groups = json_decode($res->getBody());
                $foundKcGroup = false;
                foreach($kc_groups->subGroups as $subgroup) {
                    if($subgroup->name == $group->keycloakAdminGroup) {
                        $foundKcGroup = true;
                        $kc_group = $subgroup->id;
                    }
                }
                if(!$foundKcGroup) {
                    return redirect()->route('groups.show', $id)
                    ->withError(__('Die Admin-Gruppe wurde im Keycloak nicht gefunden!.'));
                }

                $res = $client->request('GET', env('KEYCLOAK_BASE_URL')."/admin/realms/".env('KEYCLOAK_REALM')."/groups/$kc_group/members", ['headers' => $headers]);
                $kc_groupmembers = json_decode($res->getBody());

                $kc_groupmemberemails = Array();
                foreach ($kc_groupmembers as $kc_groupmember) {
                    array_push($kc_groupmemberemails, $kc_groupmember->email);
                }

                foreach($kc_groupmemberemails as $recipient) {
                    $email = Auth::user()->email;
                    $groupname = $group->name;
                    Mail::to($recipient)->send(new WaitingForJoin($email, $groupname));
                }

                return redirect()->route('groups.show', $id)
                ->withSuccess(__('Du wurdest hinzugefügt, weil die Gruppe moderiert ist musst du ein bisschen warten...'));
            } else {
                return redirect()->route('groups.show', $id)
                ->withSuccess(__('Du wurdest hinzugefügt.'));
            }
        }
    }


    public function allowJoin(request $request, string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $groupmember->update([
            "waitingForJoin" => 0
        ]);
        $groupname = $group->name;
        Mail::to($groupmember->email)->send(new JoinApproved($groupname));
        return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('Beitritt wurde genehmigt.'));
    }


    public function toggleMembershipInKeycloak(request $request, string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);


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
        $foundKcGroup = false;
        foreach($kc_groups->subGroups as $subgroup) {
            if($subgroup->name == $group->keycloakGroup) {
                $foundKcGroup = true;
                $kc_group = $subgroup->id;
            }
        }

        $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users?email='.$groupmember->email, ['headers' => $headers]);
        $kc_users = json_decode($res->getBody());
        $foundKcUser = false;
        foreach($kc_users as $kc_user) {
            if($kc_user->email == $groupmember->email) {
                $foundKcUser = true;
                $kc_user_id = $kc_user->id;
            }
        }

        if($foundKcGroup && $foundKcUser) {
            $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/', ['headers' => $headers]);
            $kc_user_groups = json_decode($res->getBody());
            $found_kc_user_group = false;
            foreach($kc_user_groups as $kc_user_group) {
                if($kc_user_group->name == $group->keycloakGroup) {
                    $found_kc_user_group = true;
                }
            }
            if($found_kc_user_group) {
                $res = $client->delete(env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/'.$kc_group, ['headers' => $headers]);
            } else {
                $res = $client->request('PUT', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/'.$kc_group, ['headers' => $headers]);
            }
            return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('Keycloak-Gruppenzuordnung wurde abgeändert.'));
        }
        if(!$foundKcUser) {
            return redirect()->route('groups.show', $group_id)
            ->withError(__('User existiert im Keycloak gar nicht.'));
        }
        return redirect()->route('groups.show', $group_id)
        ->withWarning(__('Nichts verändert.'));

    }


    public function toggleMembershipInKeycloakByEmail(request $request, string $id) {
        $group = Group::findOrFail($id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

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
        $foundKcGroup = false;
        foreach($kc_groups->subGroups as $subgroup) {
            if($subgroup->name == $group->keycloakGroup) {
                $foundKcGroup = true;
                $kc_group = $subgroup->id;
            }
        }

        $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users?email='.$request->email, ['headers' => $headers]);
        $kc_users = json_decode($res->getBody());
        $foundKcUser = false;
        foreach($kc_users as $kc_user) {
            if($kc_user->email == $request->email) {
                $foundKcUser = true;
                $kc_user_id = $kc_user->id;
            }
        }

        if($foundKcGroup && $foundKcUser) {
            $res = $client->request('GET', env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/', ['headers' => $headers]);
            $kc_user_groups = json_decode($res->getBody());
            $found_kc_user_group = false;
            foreach($kc_user_groups as $kc_user_group) {
                if($kc_user_group->name == $group->keycloakGroup) {
                    $found_kc_user_group = true;
                }
            }
            if($found_kc_user_group) {
                $res = $client->delete(env('KEYCLOAK_BASE_URL').'/admin/realms/'.env('KEYCLOAK_REALM').'/users/'.$kc_user_id.'/groups/'.$kc_group, ['headers' => $headers]);
                return redirect()->route('groups.show', $id)
                ->withSuccess(__('Keycloak-Gruppenzuordnung wurde gelöscht.'));
            }
        }
        if(!$foundKcUser) {
            return redirect()->route('groups.show', $id)
            ->withError(__('User existiert im Keycloak gar nicht.'));
        }
        return redirect()->route('groups.show', $id)
        ->withWarning(__('Nichts verändert.'));

    }

    public function toggleMembershipInMailman(request $request, string $id) {
        $groupmember = Groupmember::findOrFail($id);
        $group_id = $groupmember->group_id;
        $group = Group::findOrFail($group_id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }
        //Geht nur, wenn die Gruppe eine Mailingliste hat
        if(!$group->has_mailinglist) {
            return redirect()->route('groups.show', $group_id)
            ->withWarning(__('Die Gruppe hat keine Mailingliste.'));
        } 

        $mailmanMembers = $group->get_mailmanmembers();
        if(!in_array($groupmember->email, $mailmanMembers)) {
            $group->add_mailmanmembers([$groupmember->email]);

            return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('User wurde der Mailingliste hinzugefügt.'));
        }
        else {
            $group->remove_mailmanmembers([$groupmember->email]);
            return redirect()->route('groups.show', $group_id)
            ->withSuccess(__('User wurde aus Mailman entfernt.'));
        }

    }

    public function toggleMembershipInMailmanByEmail(request $request, string $id) {
        $group = Group::findOrFail($id);
        //Nur Administratoren dürfen diese Eigenschaft bearbeiten
        if(!Auth::hasRole('Administratoren') && !Auth::user()->hasRole($group->keycloakAdminGroup)) {
            return abort(403);
        }

        $mailmanMembers = $group->get_mailmanmembers();
        if(!in_array($request->email, $mailmanMembers)) {
            $group->add_mailmanmembers([$request->email]);

            return redirect()->route('groups.show', $id)
            ->withSuccess(__('User wurde der Mailingliste hinzugefügt.'));
        }
        else {
            $group->remove_mailmanmembers([$request->email]);
            return redirect()->route('groups.show', $id)
            ->withSuccess(__('User wurde aus Mailman entfernt.'));
        }

    }




}
