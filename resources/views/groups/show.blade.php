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
        @auth
        @if (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))
        <div class="btn btn-info btn-sm"><a class="nav-link" href="mailto:@foreach($groupmembers as $groupmember){{ $groupmember->email}};@endforeach">Email an alle Mitglieder</a></div>
        @endif
        @endauth
        <div class="container mt-4">
            <div>
                Name: {{ $group->name }}
            </div>
            @auth
            <div>
                URL: @if(trim($group->url) != "" )<a href="{{ $group->url }}" target="_blank">{{ $group->url }}</a>@else - @endif
            </div>
            <div>
                Moderiert:
                @if ( $group->moderated == 1 )
                Ja
                @else
                Nein
                @endif
            </div>
            <div>
                Automatikmodus:
                @if ( $group->automatic_mode == 1 )
                Ja
                @else
                Nein
                @endif
            </div>
            @endauth
            @auth
            @if (Auth::user()->hasRole('Administratoren'))
            <div>
                KeycloakGroup: {{ $group->keycloakGroup }}
            </div>
            <div>
                KeycloakAdminGroup: {{ $group->keycloakAdminGroup }}
            </div>
            <div>
                Mailingliste: @if ( $group->has_mailinglist == 1 )ja @else nein @endif
            </div>
            @if($group->has_mailinglist)
            <div>
                URL der Mailingliste: {{ $group->mailingListURL }}
            </div>
            @endif
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
        <div class="btn btn-warning btn-sm"><a href="mailto:@foreach($groupmembers as $groupmember){{ $groupmember->email}};@endforeach">Email an alle Mitglieder</a></div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col" width="70%">E-Mail</th>
                @if($group->has_mailinglist )
                <th scope="col" width="15%">Liste</th>
                @endif
                <th scope="col" width="15%">NextCloud</th>
                <th scope="col" width="1%" colspan="3"></th>
            </tr>
            </thead>
            <tbody>
                @foreach($groupmembers as $groupmember)
                    <tr>
                        <td>
                            @if ( $groupmember->waitingForJoin && (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup)))
                            ({{ $groupmember->email }})
                            {!! Form::open(['method' => 'POST','route' => ['groups.allowJoin', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Genehmigen', ['class' => 'btn btn-success btn-sm']) !!}
                            {!! Form::close() !!}
                            @else
                            {{ $groupmember->email }} @if($groupmember->waitingForJoin)(wartet auf Beitritt)@endif
                            @endif
                        </td>
                        @if (!$groupmember->waitingForJoin && ($groupmember->email == Auth::user()->email || (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup))))
                        @if($group->has_mailinglist )
                        <td>
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleToBeInMailinglist', $groupmember->id],'style'=>'display:inline']) !!}
                            @if ( $groupmember->toBeInMailinglist == 1 )
                            {!! Form::submit('Ja', ['class' => 'btn btn-success btn-sm']) !!}
                            @else
                            {!! Form::submit('Nein', ['class' => 'btn btn-secondary btn-sm']) !!}
                            @endif
                            {!! Form::close() !!}
                            @if (((in_array($groupmember->email, $inCockpitNotInMailmans) && $groupmember->toBeInMailinglist == 1) || (in_array($groupmember->email, $notToBeInMailmans) )) && (Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup)))
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleMembershipInMailman', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Mailman korrigieren', ['class' => 'btn btn-warning btn-sm']) !!}
                            {!! Form::close() !!}
                            @endif
                        </td>
                        @endif
                        <td>
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleToBeInNextCloud', $groupmember->id],'style'=>'display:inline']) !!}
                            @if ( $groupmember->toBeInNextCloud == 1 )
                            {!! Form::submit('Ja', ['class' => 'btn btn-success btn-sm']) !!}
                            @else
                            {!! Form::submit('Nein', ['class' => 'btn btn-secondary btn-sm']) !!}
                            @endif
                            {!! Form::close() !!}
                            @if (
                                (
                                    (in_array($groupmember->email, $inCockpitNotInKeycloaks) && $groupmember->toBeInNextCloud == 1)
                                    || (in_array($groupmember->email, $notToBeInKeyCloaks) && $groupmember->toBeInNextCloud == 0)
                                ) && (
                                    Auth::user()->email == $groupmember->email
                                    || Auth::user()->hasRole('Administratoren')
                                    || Auth::user()->hasRole($group->keycloakAdminGroup)
                                )
                            )
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleMembershipInKeycloak', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('KC korrigieren', ['class' => 'btn btn-warning btn-sm']) !!}
                            {!! Form::close() !!}
                            @endif
                        </td>
                        @if((Auth::user()->hasRole('Administratoren') || Auth::user()->hasRole($group->keycloakAdminGroup)))
                        <td>
                            {!! Form::open(['method' => 'DELETE','route' => ['groups.deletemember', $groupmember->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! Form::close() !!}
                        </td>
                        @endif
                        @else
                        <td></td><td></td><td></td>
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
                @foreach($inMailmanNotInCockpits as $inMailmanNotInCockpit)
                    <tr>
                        <th scope="row"></th>
                        <td>{{ $inMailmanNotInCockpit }}</td>
                        <td colspan=3>
                            In Mailman aber fehlt hier
                            {!! Form::open(['method' => 'POST','route' => ['groups.addmember', $group->id],'style'=>'display:inline']) !!}
                            {!! Form::hidden('email', $inMailmanNotInCockpit) !!}
                            {!! Form::submit('hinzufügen', ['class' => 'btn btn-primary btn-sm']) !!}
                            {!! Form::close() !!}
                            {!! Form::open(['method' => 'POST','route' => ['groups.toggleMembershipInMailmanByEmail', $group->id],'style'=>'display:inline']) !!}
                            {!! Form::hidden('email', $inMailmanNotInCockpit) !!}
                            {!! Form::submit('Mailman löschen', ['class' => 'btn btn-warning btn-sm']) !!}
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
