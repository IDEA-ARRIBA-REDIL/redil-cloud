@extends('layouts/layoutMaster')

@section('title', 'Pickers - Forms')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
  'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
  'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
  'resources/assets/vendor/libs/pickr/pickr-themes.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
  'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
  'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js',
  'resources/assets/vendor/libs/pickr/pickr.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/forms-pickers.js'])
@endsection

@section('content')
<div class="row">
  <!-- Flat Picker -->
  <div class="col-12 mb-6">
    <div class="card">
      <h5 class="card-header">Flatpickr HOLA</h5>
      <div class="card-body">
        <div class="row">
          <!-- Date Picker-->
          <div class="col-md-6 col-12 mb-6">
            <label for="flatpickr-date" class="form-label">Date Picker</label>
            <input type="text" class="form-control fecha-picker" placeholder="YYYY-MM-DD" id="flatpickr-date" />
          </div>
          <!-- /Date Picker -->

        </div>
      </div>
    </div>
  </div>
  <!-- /Flatpickr -->

</div>

@endsection
