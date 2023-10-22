@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>Gruppen</h1>
        <div class="lead">
            Gruppen
            @auth
            @if (Auth::user()->hasRole('Administratoren'))
            <a href="{{ route('groups.create') }}" class="btn btn-outline-success btn-sm float-right">Neue Gruppe</a>
            @endif
            @endauth
        </div>

        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col" width="15%">Name</th>
                @auth
                <th scope="col" width="15%">URL</th>
                @endauth
                <th scope="col" width="1%" colspan="3"></th>
            </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    <tr>
                        <td>{{ $group->name }}</td>
                        @auth
                        <td>@if(trim($group->url) != "" )<a href="{{ $group->url }}" target="_blank">{{ $group->url }}</a>@else - @endif</td>
                        @endauth
                        <td><a href="{{ route('groups.show', $group->id) }}" class="btn btn-outline-primary btn-sm">Details anzeigen</a></td>
                        @auth
                        @if (Auth::user()->hasRole('Administratoren'))
                        <td><a href="{{ route('groups.edit', $group->id) }}" class="btn btn-outline-secondary btn-sm">Bearbeiten</a></td>
                        <td>
                            {!! Form::open(['method' => 'DELETE','route' => ['groups.destroy', $group->id],'style'=>'display:inline']) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-outline-danger btn-sm']) !!}
                            {!! Form::close() !!}
                        </td>
                        @endif
                        @endauth
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex">
            {!! $groups->links() !!}
        </div>

    </div>
@endsection
