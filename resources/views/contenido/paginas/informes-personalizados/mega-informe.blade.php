@extends('layouts/layoutMaster')

@section('title', $informe->nombre)

@section('content')
    @livewire('informes-personalizados.mega-informe', ['informeId' => $informe->id])
@endsection
