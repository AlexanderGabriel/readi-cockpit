@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded border">
        <h1>Gruppendetails</h1>
        <div class="lead">
            Gruppendetails und Mitglieder
        </div>
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>
        <div class="container mt-4">
            <div>
                Name: {{ $group->name }}
            </div>
            <div>
                Beschreibung: {{ $group->description }}
            </div>
            <div>
                Moderiert:
                @if ( $group->moderated == 1 )
                Ja
                @else
                Nein
                @endif
            </div>
            @auth
            @if (Auth::user()->hasRole('Administratoren'))
            <div>
                KeycloakGroup: {{ $group->keycloakGroup }}
            </div>
            <div>
                KeycloakAdminGroup: {{ $group->keycloakAdminGroup }}
            </div>
            <div>
                Mailingliste: {{ $group->has_mailinglist }}
            </div>
            <div>
                URL der Mailingliste: {{ $group->mailingListURL }}
            </div>
            <div>
                Admin-Account der Mailing-Liste: {{ $group->mailingListAdmin }}
            </div>
            <div>
                Admin-Passwort der Mailingliste: geht dich gar nichts an!
            </div>
            @endif
            @endauth
        </div>
    </div>
    @auth
    <div class="bg-light p-4 rounded border">
        <h2>Gruppenmitglieder</h2>
        {!! Form::open(['method' => 'POST','route' => ['groups.toggleToBeInGroup', $group->id],'style'=>'display:inline']) !!}
        @if ( $canJoinGroup )
        {!! Form::submit('Gruppe beitreten', ['class' => 'btn btn-success btn-sm']) !!}
        @else
        {!! Form::submit('Gruppe verlassen', ['class' => 'btn btn-warning btn-sm']) !!}
        @endif
        {!! Form::close() !!}
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col" width="1%">#</th>
                <th scope="col" width="70%">E-Mail</th>
                @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
                <th scope="col" width="15%">Liste</th>
                <th scope="col" width="15%">NextCloud</th>
                @endif
                <th scope="col" width="1%" colspan="3"></th>
            </tr>
            </thead>
            <tbody>
                @foreach($groupmembers as $groupmember)
                    <tr>
                        <th scope="row">{{ $groupmember->id }}</th>
                        <td>
                            @if ( $groupmember->waitingForJoin )
                            ({{ $groupmember->email }})
                            {!! Form::open(['method' => 'POST','route' => ['groups.allowJoin', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Genehmigen', ['class' => 'btn btn-success btn-sm']) !!}
                            {!! Form::close() !!}
                            @else
                            {{ $groupmember->email }}
                            @endif
                        </td>
                        @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
                        <td>
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleToBeInMailinglist', $groupmember->id],'style'=>'display:inline']) !!}
                            @if ( $groupmember->toBeInMailinglist == 1 )
                            {!! Form::submit('Ja', ['class' => 'btn btn-success btn-sm']) !!}
                            @else
                            {!! Form::submit('Nein', ['class' => 'btn btn-secondary btn-sm']) !!}
                            @endif
                            {!! Form::close() !!}
                        </td>
                        <td>
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleToBeInNextCloud', $groupmember->id],'style'=>'display:inline']) !!}
                            @if ( $groupmember->toBeInNextCloud == 1 )
                            {!! Form::submit('Ja', ['class' => 'btn btn-success btn-sm']) !!}
                            @else
                            {!! Form::submit('Nein', ['class' => 'btn btn-secondary btn-sm']) !!}
                            @endif
                            {!! Form::close() !!}
                            @if ( (in_array($groupmember->email, $inCockpitNotInKeycloaks) && $groupmember->toBeInNextCloud == 1) || (in_array($groupmember->email, $notToBeInKeyCloaks) ) )
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleMembershipInKeycloak', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('KC korrigieren', ['class' => 'btn btn-warning btn-sm']) !!}
                            {!! Form::close() !!}
                            @endif
                        </td>
                        <td>
                            {!! Form::open(['method' => 'DELETE','route' => ['groups.deletemember', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! Form::close() !!}
                        </td>
                        @endif
                    </tr>
                @endforeach
                @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
                @foreach($inKeyCloakNotInCockpits as $inKeyCloakNotInCockpit)
                    <tr>
                        <th scope="row"></th>
                        <td>{{ $inKeyCloakNotInCockpit }}</td>
                        <td colspan=3>
                            In Keycloak aber fehlt hier
                            {!! Form::open(['method' => 'POST','route' => ['groups.addmember', $group->id],'style'=>'display:inline']) !!}
                            {!! Form::hidden('email', $inKeyCloakNotInCockpit) !!}
                            {!! Form::submit('hinzufügen', ['class' => 'btn btn-primary btn-sm']) !!}
                            {!! Form::close() !!}
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleMembershipInKeycloakByEmail', $group->id],'style'=>'display:inline']) !!}
                            {!! Form::hidden('email', $inKeyCloakNotInCockpit) !!}
                            {!! Form::submit('Keycloak löschen', ['class' => 'btn btn-warning btn-sm']) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
    @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
    <div class="container mt-4">
        <div>
            {!! Form::open(['method' => 'POST','route' => ['groups.addmember', $group->id],'style'=>'display:inline']) !!}
            {!! Form::text('email') !!}
            {!! Form::submit('Add', ['class' => 'btn btn-primary btn-sm']) !!}
            {!! Form::close() !!}
        </div>
    </div>
    @endif
    @endauth
    <div class="mt-4">
        @auth
        @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
        <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-info">Bearbeiten</a>
        @endif
        @endauth
        <a href="{{ route('groups.index') }}" class="btn btn-outline-primary">Zurück</a>
    </div>
@endsection
