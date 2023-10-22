@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>Neue Gruppe hinzufügen</h1>
        <div class="lead">
            Neue Gruppe hinzufügen
        </div>

        <div class="container mt-4">
            <form method="POST" action="">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input value="{{ old('name') }}"
                        type="text"
                        class="form-control"
                        name="name"
                        placeholder="Name" required>

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="url" class="form-label">URL</label>
                    <input value="{{ old('url') }}"
                        type="url"
                        class="form-control"
                        name="url"
                        placeholder="Beschreibung" required>
                    @if ($errors->has('url'))
                        <span class="text-danger text-left">{{ $errors->first('url') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    {!! Form::checkbox('moderated', "1", old('moderated'), ['class' => 'form-check-input', "id" => "moderated" ]) !!}
                    {!! Form::label('moderated', 'ist moderiert (kein automatischer Beitritt)', ['class' => 'form-check-label', 'for' => 'moderated']) !!}
                    @if ($errors->has('moderated'))
                        <span class="text-danger text-left">{{ $errors->first('moderated') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    {!! Form::checkbox('automatic_mode', "1", old('automatic_mode'), ['class' => 'form-check-input', "id" => "automatic_mode" ]) !!}
                    {!! Form::label('automatic_mode', 'Automatikmodus', ['class' => 'form-check-label', 'for' => 'automatic_mode']) !!}
                    @if ($errors->has('automatic_mode'))
                        <span class="text-danger text-left">{{ $errors->first('automatic_mode') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="keycloakGroup" class="form-label">Keycloak-Gruppe</label>
                    <input value="{{ old('keycloakGroup') }}"
                        type="text"
                        class="form-control"
                        name="keycloakGroup"
                        placeholder="Keycloak-Gruppe" required>
                    @if ($errors->has('keycloakGroup'))
                        <span class="text-danger text-left">{{ $errors->first('keycloakGroup') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="keycloakAdminGroup" class="form-label">Keycloak-Admin-Gruppe</label>
                    <input value="{{ old('keycloakAdminGroup') }}"
                        type="text"
                        class="form-control"
                        name="keycloakAdminGroup"
                        placeholder="Keycloak Admin-Gruppe" required>
                    @if ($errors->has('keycloakAdminGroup'))
                        <span class="text-danger text-left">{{ $errors->first('keycloakAdminGroup') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    {!! Form::checkbox('has_mailinglist', "1", old('has_mailinglist'), ['class' => 'form-check-input', "id" => "has_mailinglist" ]) !!}
                    {!! Form::label('has_mailinglist', 'hat eine Mailing-Liste', ['class' => 'form-check-label', 'for' => 'has_mailinglist']) !!}
                    @if ($errors->has('has_mailinglist'))
                        <span class="text-danger text-left">{{ $errors->first('has_mailinglist') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="mailingListURL" class="form-label">URL zur Mailingliste</label>
                    <input value="{{ old('mailingListURL') }}"
                        type="text"
                        class="form-control"
                        name="mailingListURL"
                        placeholder="mailingListURL">
                    @if ($errors->has('mailingListURL'))
                        <span class="text-danger text-left">{{ $errors->first('mailingListURL') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="mailingListPassword" class="form-label">Admin-Passwort der Mailing-Liste (leer lassen, um nicht zu ändern)</label>
                    <input value="{{ old('mailingListPassword') }}"
                        type="text"
                        class="form-control"
                        name="mailingListPassword"
                        placeholder="mailingListPassword">
                    @if ($errors->has('mailingListPassword'))
                        <span class="text-danger text-left">{{ $errors->first('mailingListPassword') }}</span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Gruppe anlegen</button>
                <a href="{{ route('groups.index') }}" class="btn btn-default">Zurück</a>
            </form>
        </div>

    </div>
@endsection
