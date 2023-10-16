@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>re@di Cockpit</h1>
    </div>
    <br>

    <div class="card" style="width: 18rem;">
        <div class="card-body">
            <h5 class="card-title">Projektgruppen</h5>
            <p class="card-text">
                Verwaltung der Projektgruppen<br>
                <ul>
                    <li>Mitgliedschaften von Projektgruppen verwalten</li>
                    <li>Ermöglicht auch die Steuerung der Mitgliedschaften in Keycloak-Gruppen und Mailman-Listen</li>
                </ul>
            </p>
            <a href="{{ URL::route("groups.index") }}" class="btn btn-outline-primary">Projektgruppen</a>
        </div>
    </div>
@endsection
