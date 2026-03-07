<!-- Checkout Wizard -->
<div id="wizard-checkout" class="bs-stepper wizard-icons wizard-icons-example">
  <div class="bs-stepper-header m-lg-auto border-0">
    <div class="step" data-target="#checkout-cart">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 60 60">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-cart.svg#wizardCart')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Carrito</span>
      </button>
    </div>
    <div class="line">
      <i class="ti ti-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-address">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 60 60">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-address.svg#wizardCheckoutAddress')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Formulario</span>
      </button>
    </div>
    <div class="line">
      <i class="ti ti-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-payment">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 60 60">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-payment.svg#wizardPayment')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Checkout</span>
      </button>
    </div>
    <div class="line">
      <i class="ti ti-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-confirmation">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 60 60">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-confirmation.svg#wizardConfirm')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Compra Finalizada</span>
      </button>
    </div>
  </div>
  <div class="bs-stepper-content border-top">
    <form id="wizard-checkout-form" onSubmit="return false">

      <!-- Cart -->
      <div id="checkout-cart" class="content">
        <div class="row">
          <!-- Cart left -->
          <div class="col-xl-9  col-lg-9  col-md-12 col-sm-12 mb-6 mb-xl-0">

           

            <!-- Shopping bag -->
            <div class="row">
              <h5>My Shopping Bag (2 Items)</h5>
              <ul class="list-group mb-4">
                <li class="list-group-item p-6">
                  <div class="d-flex gap-4">
                    <div class="flex-shrink-0 d-flex align-items-center">
                      <img src="{{asset('assets/img/products/1.png')}}" alt="google home" class="w-px-100">
                    </div>
                    <div class="flex-grow-1">
                      <div class="row">
                        <div class="col-md-8">
                          <p class="me-3 mb-2"><a href="javascript:void(0)" class="fw-medium"> <span class="text-heading">Google - Google Home - White</span></a></p>
                          
                          <div class="read-only-ratings mb-2" data-rateyo-read-only="true"></div>
                          <input type="number" class="form-control form-control-sm w-px-100" value="1" min="1" max="5">
                        </div>
                        <div class="col-md-4">
                          <div class="text-md-end">
                            <button type="button" class="btn-close btn-pinned" aria-label="Close"></button>
                            <div class="my-2 mt-md-6 mb-md-4"><span class="text-primary">$299</span></div>
                            
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              
              </ul>
            </div>

            <div class="row">
              <h5> Contenidos Extras </h5>

              <div class="col-lg-12">
                <label>Talla de camsita</label>
                <input class="form-control form-control-sm w-px-100">
              </div>
              <div class="col-lg-12">
                <label>Color camiseta</label>
                <input class="form-control form-control-sm w-px-100">
              </div>
            </div>
           
          </div>

          <!-- Cart right -->
          <div  class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
            <div style="position: sticky; top: 100px;" class="border rounded p-6 mb-4">

              
              <!-- Price Details -->
              <h6>Detalles de la compra</h6>
              <dl class="row mb-0 text-heading">
                <dt class="col-6 fw-normal">Bag Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

               
                <dt class="col-6 fw-normal">Order Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

                <dt class="col-6 fw-normal">Delivery Charges</dt>
                <dd class="col-6 text-end"><s class="text-muted">$5.00</s> <span class="badge bg-label-success ms-1">Free</span></dd>

                <h6> Contenidos Extras </h6>
                <dt class="col-6 fw-normal">Talla camiseta</dt>
                <dd class="col-6 text-end"> M</dd>

                <dt class="col-6 fw-normal">Color camiseta</dt>
                <dd class="col-6 text-end"> Azul</dd>
              </dl>

              <hr class="mx-n6 my-6">
              <dl class="row mb-0">
                <dt class="col-6 text-heading">Total</dt>
                <dd class="col-6 fw-medium text-end text-heading mb-0">$1198.00</dd>
              </dl>
            </div>

           
            <div class="d-grid">
              <button class="btn btn-primary btn-next">Continuar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Address -->
      <div id="checkout-address" class="content">
        <div class="row">
        
          <!-- Address left -->
          <div class="col-xl-9  col-lg-9  col-md-12 col-sm-12 mb-6 mb-xl-0">

            <!-- Select address -->
            <p class="fw-medium text-heading">Cuestionario</p>
            <div class="row mb-6 g-6">
              @foreach($elementosFormulario as $elemento)

              @if($elemento->tipoElemento->clase == 'encabezado')
              <div style="border:solid 1px #e4e4e4 !important" class="card">
                <div class="card-header">

                  <h5>{{$elemento->titulo}}</h5>
                  <p>{{$elemento->descripcion}}</p>
                </div>
              </div>
              @endif

              @if($elemento->tipoElemento->clase == 'corta')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                  <input @if($elemento->required == true) required @endif class="form-control w-90" type="text" max="{{$elemento->long_max}}" id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}">
                </div>
              </div>
              @endif
              
              
              @if($elemento->tipoElemento->clase == 'larga')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                  <textarea @if($elemento->required == true) required @endif class="form-control w-90" type="text" max="{{$elemento->long_max}}" id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}">
                  </textarea>
                </div>
              </div>
              @endif

              @if($elemento->tipoElemento->clase == 'si_no')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                  <label class="switch switch-lg">
                    <input @if($elemento->required == true) required @endif type="checkbox"  id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}"  class="switch-input SiNo" />
                    <span class="switch-toggle-slider">
                      <span class="switch-on">SI</span>
                      <span class="switch-off">NO</span>
                    </span>
                    <span class="switch-label"></span>
                  </label>
                </div>
              </div>
              @endif


              @if($elemento->tipoElemento->clase == 'unica_respuesta')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                  <select @if($elemento->required == true) required @endif class="form-select select2 w-90"   id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}">
                    @foreach($elemento->opciones as $opcion)
                    <option id="opcion-{{$opcion->id}}-elemento-{{$elemento->id}}" value="{{$opcion->id}}"> {{$opcion->valor_texto}}</option>
                    @endforeach
                  </select>
                  </div>
              </div>
          
              @endif

              @if($elemento->tipoElemento->clase == 'multiple_respuesta')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                  <select multiple @if($elemento->required == true) required @endif class="form-select select2 w-90"   id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}">
                    @foreach($elemento->opciones as $opcion)
                    <option id="opcion-{{$opcion->id}}-elemento-{{$elemento->id}}" value="{{$opcion->id}}"> {{$opcion->valor_texto}}</option>
                    @endforeach
                  </select>
                  </div>
              </div>
          
              @endif

              @if($elemento->tipoElemento->clase == 'fecha')
              <div class="card pb-0">
                <div class="card-header pb-0 pt-0">
                  <h5>{{$elemento->titulo}}</h5>                 
                </div>
                <div class="card-body">
                  <p class="mb-1">{{$elemento->descripcion}}</p>
                 

                  <input id="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}" name="respuesta-elemento-{{$elemento->id}}-usuario-{{$usuario->id}}                 
                  placeholder="YYYY-MM-DD" 
                  class="fecha form-control fecha-picker" type="text" />
                  </div>
              </div>
          
              @endif
              
             




              @endforeach
            </div>
     

           
          </div>

          <!-- Address right -->
          <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
            <div style="position: sticky; top: 100px;"  class="border rounded p-6 mb-4">

              <!-- Estimated Delivery -->
              <h6>Estimated Delivery Date</h6>
              <ul class="list-unstyled">
                <li class="d-flex gap-4 align-items-center py-2 mb-4">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/1.png')}}" alt="google home" class="w-px-50">
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0"><a class="text-body" href="javascript:void(0)">Google - Google Home - White</a></p>
                    <p class="fw-medium mb-0">18th Nov 2021</p>
                  </div>
                </li>
                <li class="d-flex gap-4 align-items-center py-2">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/2.png')}}" alt="google home" class="w-px-50">
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0"><a class="text-body" href="javascript:void(0)">Apple iPhone 11 (64GB, Black)</a></p>
                    <p class="fw-medium mb-0">20th Nov 2021</p>
                  </div>
                </li>
              </ul>

              <hr class="mx-n6 my-6">

              <!-- Price Details -->
              <h6>Price Details</h6>
              <dl class="row mb-0 text-heading">

                <dt class="col-6 fw-normal">Order Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

                <dt class="col-6 fw-normal">Delivery Charges</dt>
                <dd class="col-6 text-end"><s class="text-muted">$5.00</s> <span class="badge bg-label-success ms-2">Free</span></dd>

              </dl>
              <hr class="mx-n6 my-6">
              <dl class="row mb-0">
                <dt class="col-6 text-heading">Total</dt>
                <dd class="col-6 fw-medium text-end text-heading mb-0">$1198.00</dd>
              </dl>
              <div class="d-grid">
                <button class="btn btn-primary btn-next">Place Order</button>
              </div>
            </div>
           
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div id="checkout-payment" class="content">
        <div class="row">
          <!-- Payment left -->
          <div class="col-xl-8 mb-6 mb-xl-0">
            

            <!-- Payment Tabs -->
            <div class="col-xxl-6 col-lg-8">
              <div class="nav-align-top">
                <ul class="nav nav-pills card-header-pills row-gap-2" id="paymentTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-cc-tab" data-bs-toggle="pill" data-bs-target="#pills-cc" type="button" role="tab" aria-controls="pills-cc" aria-selected="true">Card</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-cod-tab" data-bs-toggle="pill" data-bs-target="#pills-cod" type="button" role="tab" aria-controls="pills-cod" aria-selected="false">Cash On Delivery</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-gift-card-tab" data-bs-toggle="pill" data-bs-target="#pills-gift-card" type="button" role="tab" aria-controls="pills-gift-card" aria-selected="false">Gift Card</button>
                  </li>
                </ul>
              </div>
              <div class="tab-content px-0 pb-0" id="paymentTabsContent">
                <!-- Credit card -->
                <div class="tab-pane fade show active" id="pills-cc" role="tabpanel" aria-labelledby="pills-cc-tab">
                  <div class="row g-6">
                    <div class="col-12">
                      <label class="form-label w-100" for="paymentCard">Card Number</label>
                      <div class="input-group input-group-merge">
                        <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask" type="text" placeholder="1356 3215 6548 7898" aria-describedby="paymentCard2" />
                        <span class="input-group-text cursor-pointer p-1" id="paymentCard2"><span class="card-type"></span></span>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="paymentCardName">Name</label>
                      <input type="text" id="paymentCardName" class="form-control" placeholder="John Doe" />
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label" for="paymentCardExpiryDate">Exp. Date</label>
                      <input type="text" id="paymentCardExpiryDate" class="form-control expiry-date-mask" placeholder="MM/YY" />
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label" for="paymentCardCvv">CVV Code</label>
                      <div class="input-group input-group-merge">
                        <input type="text" id="paymentCardCvv" class="form-control cvv-code-mask" maxlength="3" placeholder="654" />
                        <span class="input-group-text cursor-pointer" id="paymentCardCvv2"><i class="ti ti-help text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Card Verification Value"></i></span>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-check form-switch mt-2">
                        <input type="checkbox" class="form-check-input" id="cardFutureBilling" />
                        <label for="cardFutureBilling" class="form-check-label">Save card for future billing?</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <button type="button" class="btn btn-primary btn-next me-3">Save Changes</button>
                      <button type="reset" class="btn btn-label-secondary">Reset</button>
                    </div>
                  </div>
                </div>

                <!-- COD -->
                <div class="tab-pane fade" id="pills-cod" role="tabpanel" aria-labelledby="pills-cod-tab">
                  <p>Cash on Delivery is a type of payment method where the recipient make payment for the order at the time of delivery rather than in advance.</p>
                  <button type="button" class="btn btn-primary btn-next">Pay On Delivery</button>
                </div>

                <!-- Gift card -->
                <div class="tab-pane fade" id="pills-gift-card" role="tabpanel" aria-labelledby="pills-gift-card-tab">
                  <h6>Enter Gift Card Details</h6>
                  <div class="row g-5">
                    <div class="col-12">
                      <label for="giftCardNumber" class="form-label">Gift card number</label>
                      <input type="number" class="form-control" id="giftCardNumber" placeholder="Gift card number">
                    </div>
                    <div class="col-12">
                      <label for="giftCardPin" class="form-label">Gift card pin</label>
                      <input type="number" class="form-control" id="giftCardPin" placeholder="Gift card pin">
                    </div>
                    <div class="col-12">
                      <button type="button" class="btn btn-primary btn-next">Redeem Gift Card</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- Address right -->
          <div class="col-xl-4">
            <div  style="position: sticky; top: 100px;"  class="border rounded p-6">

              <!-- Price Details -->
              <h6>Price Details</h6>
              <dl class="row text-heading">

                <dt class="col-6 fw-normal">Order Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

                <dt class="col-6 fw-normal">Delivery Charges</dt>
                <dd class="col-6 text-end"><s class="text-muted">$5.00</s> <span class="badge bg-label-success ms-1">Free</span></dd>
              </dl>
              <hr class="mx-n6 my-6">
              <dl class="row">
                <dt class="col-6 text-heading mb-3">Total</dt>
                <dd class="col-6 fw-medium text-end text-heading mb-0">$1198.00</dd>

                <dt class="col-6 fw-medium text-heading">Deliver to:</dt>
                <dd class="col-6 fw-medium text-end mb-0"><span class="badge bg-label-primary">Home</span></dd>
              </dl>
              <!-- Address Details -->
              <address>
                <span class="text-heading fw-medium"> John Doe (Default),</span><br />
                4135 Parkway Street, <br />
                Los Angeles, CA, 90017. <br />
                Mobile : +1 906 568 2332
              </address>
              <a href="javascript:void(0)" class="fw-medium">Change address</a>
            </div>
          </div>
        </div>
      </div>
    </form>
      <!-- Confirmation -->
      <div id="checkout-confirmation" class="content">
        <div class="row mb-6">
          <div class="col-12 col-lg-8 mx-auto text-center mb-2">
            <h4>Thank You! 😇</h4>
            <p>Your order <a href="javascript:void(0)" class="text-heading fw-medium">#1536548131</a> has been placed!</p>
            <p>We sent an email to <a href="mailto:john.doe@example.com" class="text-heading fw-medium">john.doe@example.com</a> with your order confirmation and receipt. If the email hasn't arrived within two minutes, please check your spam folder to see if the email was routed there.</p>
            <p><span><i class="ti ti-clock me-1 text-heading"></i> Time placed:&nbsp;</span> 25/05/2020 13:35pm</p>
          </div>
          <!-- Confirmation details -->
          <div class="col-12">
            <ul class="list-group list-group-horizontal-md">
              <li class="list-group-item flex-fill p-6 text-body">
                <h6 class="d-flex align-items-center gap-2"><i class="ti ti-map-pin"></i> Shipping</h6>
                <address class="mb-0">
                  John Doe <br />
                  4135 Parkway Street,<br />
                  Los Angeles, CA 90017,<br />
                  USA
                </address>
                <p class="mb-0 mt-4">
                  +123456789
                </p>
              </li>
              <li class="list-group-item flex-fill p-6 text-body">
                <h6 class="d-flex align-items-center gap-2"><i class="ti ti-credit-card"></i> Billing Address</h6>
                <address class="mb-0">
                  John Doe <br />
                  4135 Parkway Street,<br />
                  Los Angeles, CA 90017,<br />
                  USA
                </address>
                <p class="mb-0 mt-4">
                  +123456789
                </p>
              </li>
              <li class="list-group-item flex-fill p-6 text-body">
                <h6 class="d-flex align-items-center gap-2"><i class="ti ti-ship"></i> Shipping Method</h6>
                <p class="fw-medium mb-4">Preferred Method:</p>
                Standard Delivery<br />
                (Normally 3-4 business days)
              </li>
            </ul>
          </div>
        </div>

        <div class="row">
          <!-- Confirmation items -->
          <div class="col-xl-9 mb-6 mb-xl-0">
            <ul class="list-group">
              <li class="list-group-item p-6">
                <div class="d-flex gap-4">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/1.png')}}" alt="google home" class="w-px-75">
                  </div>
                  <div class="flex-grow-1">
                    <div class="row">
                      <div class="col-md-8">
                        <a href="javascript:void(0)">
                          <h6 class="mb-2">Google - Google Home - White</h6>
                        </a>
                        <div class="text-body mb-2 d-flex flex-wrap"><span class="me-1">Sold by:</span> <a href="javascript:void(0)" class="me-3">Google</a> <span class="badge bg-label-success">In Stock</span></div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-md-end">
                          <div class="my-2 my-lg-6"><span class="text-primary">$299/</span><s class="text-muted">$359</s></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
              <li class="list-group-item p-6">
                <div class="d-flex gap-4">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/2.png')}}" alt="google home" class="w-px-75">
                  </div>
                  <div class="flex-grow-1">
                    <div class="row">
                      <div class="col-md-8">
                        <a href="javascript:void(0)">
                          <h6 class="mb-2">Apple iPhone 11 (64GB, Black)</h6>
                        </a>
                        <div class="text-body mb-2 d-flex flex-wrap"><span class="me-1">Sold by:</span> <a href="javascript:void(0)">Apple</a></div>
                      </div>
                      <div class="col-md-4">
                        <div class="text-md-end">
                          <div class="my-2 my-lg-6"><span class="text-primary">$299/</span><s class="text-muted">$359</s></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
          <!-- Confirmation total -->
          <div class="col-xl-3">
            <div class="border rounded p-6">
              <!-- Price Details -->
              <h6>Price Details</h6>
              <dl class="row mb-0 text-heading">

                <dt class="col-6 fw-normal">Order Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

                <dt class="col-sm-6 text-heading fw-normal">Delivery Charges</dt>
                <dd class="col-sm-6 text-end"><s class="text-muted">$5.00</s> <span class="badge bg-label-success ms-1">Free</span></dd>
              </dl>
              <hr class="mx-n6 mb-6">
              <dl class="row mb-0">
                <dt class="col-6 text-heading">Total</dt>
                <dd class="col-6 fw-medium text-end text-heading mb-0">$1198.00</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<!--/ Checkout Wizard -->
