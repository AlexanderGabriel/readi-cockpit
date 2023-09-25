@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>Gruppe bearbeiten</h1>
        <div class="lead">

        </div>

        <div class="container mt-4">
            <form method="post" action="{{ route('groups.update', $group->id) }}">
                @method('patch')
                @csrf

                @auth
                @if (Auth::user()->hasRole('Administratoren'))
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input value="{{ $group->name }}"
                        type="text"
                        class="form-control"
                        name="name"
                        placeholder="Name" required>

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                @endif
                @endauth
                <div class="mb-3">
                    <label for="description" class="form-label">Beschreibung</label>
                    <input value="{{ $group->description }}"
                        type="text"
                        class="form-control"
                        name="description"
                        placeholder="description" required>

                    @if ($errors->has('description'))
                        <span class="text-danger text-left">{{ $errors->first('description') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    {!! Form::checkbox('moderated', "1", $group->moderated, ['class' => 'form-check-input', "id" => "moderated" ]) !!}

                    {!! Form::label('moderated', 'Ist moderiert', ['class' => 'form-check-label', 'for' => 'moderated']) !!}

                    @if ($errors->has('moderated'))
                        <span class="text-danger text-left">{{ $errors->first('moderated') }}</span>
                    @endif
                </div>
                @auth
                @if (Auth::user()->hasRole('Administratoren'))
                <div class="mb-3">
                    <label for="keycloakGroup" class="form-label">Keycloak-Gruppe</label>
                    <input value="{{ $group->keycloakGroup }}"
                        type="text"
                        class="form-control"
                        name="keycloakGroup"
                        placeholder="keycloakGroup" required>

                    @if ($errors->has('keycloakGroup'))
                        <span class="text-danger text-left">{{ $errors->first('keycloakGroup') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="keycloakAdminGroup" class="form-label">Keycloak-Admin-Gruppe</label>
                    <input value="{{ $group->keycloakAdminGroup }}"
                        type="text"
                        class="form-control"
                        name="keycloakAdminGroup"
                        placeholder="keycloakAdminGroup" required>

                    @if ($errors->has('keycloakAdminGroup'))
                        <span class="text-danger text-left">{{ $errors->first('keycloakAdminGroup') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    {!! Form::checkbox('has_mailinglist', "1", $group->has_mailinglist, ['class' => 'form-check-input', "id" => "has_mailinglist" ]) !!}

                    {!! Form::label('has_mailinglist', 'Hat eine Mailingliste', ['class' => 'form-check-label', 'for' => 'has_mailinglist']) !!}

                    @if ($errors->has('has_mailinglist'))
                        <span class="text-danger text-left">{{ $errors->first('has_mailinglist') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="mailingListURL" class="form-label">URL zur Mailingliste</label>
                    <input value="{{ $group->mailingListURL }}"
                        type="text"
                        class="form-control"
                        name="mailingListURL"
                        placeholder="mailingListURL">

                    @if ($errors->has('mailingListURL'))
                        <span class="text-danger text-left">{{ $errors->first('mailingListURL') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="mailingListAdmin" class="form-label">Admin-User der Mailingliste</label>
                    <input value="{{ $group->mailingListAdmin }}"
                        type="text"
                        class="form-control"
                        name="mailingListAdmin"
                        placeholder="mailingListAdmin">
                    @if ($errors->has('mailingListAdmin'))
                        <span class="text-danger text-left">{{ $errors->first('mailingListAdmin') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="mailingListPassword" class="form-label">Admin-Passwort der Mailing-Liste (leer lassen, um nicht zu ändern)</label>
                    <input
                        type="password"
                        class="form-control"
                        name="mailingListPassword"
                        placeholder="mailingListPassword">

                    @if ($errors->has('mailingListPassword'))
                        <span class="text-danger text-left">{{ $errors->first('mailingListPassword') }}</span>
                    @endif
                </div>
                @endif
                @endauth
                <button type="submit" class="btn btn-primary">Gruppe aktualisieren</button>
                <a href="{{ route('groups.index') }}" class="btn btn-default">Abbrechen</button>
            </form>
        </div>

    </div>
@endsection
