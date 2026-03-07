@extends('layouts/contentNavbarLayout')

@section('title', 'Campus - ' . $curso->nombre)

@section('vendor-style')
<!-- Incluir estilos si fuesen necesarios -->
<style>
    /* Ajustes específicos para el acordeón del campus para que se vea como playlist */
    #temarioAccordion .accordion-button::after {
        background-size: 1rem;
    }
    #temarioAccordion .accordion-button:not(.collapsed) {
        box-shadow: none;
        background-color: transparent;
    }


        .learning-box {
            background-color: #f6f0ff;

            border-radius: 12px;
            padding: 2rem;
        }
</style>
@endsection

@section('page-style')
@endsection

@section('content')

    <!-- Montamos el componente Livewire y el pasamos el slug de la ruta -->
    @livewire('cursos.campus-curso', ['slug' => $slug])

@endsection
