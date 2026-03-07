@extends('layouts/contentNavbarLayout')

@section('title', 'Contenido del curso')

@section('content')
    @livewire('cursos.gestionar-contenido-del-curso', ['curso' => $curso])
@endsection
