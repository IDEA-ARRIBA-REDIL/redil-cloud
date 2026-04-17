@extends('layouts/layoutMaster')

@section('title', 'Mis Planes Lectores')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])
<style>
  .nav-tabs .nav-link.active {
    border-bottom: 2px solid #5a8dee; 
    color: #5a8dee;
    font-weight: 600;
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<livewire:plan-lector.mis-planes />

@endsection
