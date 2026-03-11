<div class="col-12">

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {!! session('success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(session('status'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {!! session('status') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(session('danger'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {!! session('danger') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {!! session('error') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(session('warning'))
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    {!! session('warning') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(session('info'))
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    {!! session('info') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

  @if(!$errors->isEmpty())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
  </div>
  @endif

</div>
