@extends('layouts/layoutMaster')

@section('title', 'Informes Personalizados')

@section('content')
    <!-- Cargamos el componente Livewire que gestiona el listado y los roles -->
    @livewire('informes-personalizados.index')
@endsection
