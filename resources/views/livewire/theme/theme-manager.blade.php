<div>
    {{-- In work, do what you enjoy. --}}
    <div class="card">
      <div class="card-body">
          @if($showSuccessMessage)
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  Color actualizado correctamente
                  <button type="button" class="btn-close" wire:click="hideMessage"></button>
              </div>
          @endif

          <div class="accordion row" id="themeAccordion">
            @foreach($categories as $index => $category)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $activeCategory === $category ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#category{{ $index }}"
                                aria-expanded="{{ $activeCategory === $category ? 'true' : 'false' }}"
                                aria-controls="category{{ $index }}">
                            <span class="primary ">{{ ucfirst($category) }}  </span>
                        </button>
                    </h2>
                    <div id="category{{ $index }}" class="accordion-collapse collapse {{ $activeCategory === $category ? 'show' : '' }}" data-bs-parent="#themeAccordion">
                          <div class="accordion-body">
                              <div class="table-responsive">
                                  <table class="table">
                                      <thead>
                                          <tr>
                                              <th>Nombre Variable</th>
                                              <th>Color Actual</th>
                                              <th>Tipo</th>
                                              <th>Gradient</th>
                                              <th>Color 2 Gradient</th>
                                              <th>Acciones</th>

                                          </tr>
                                      </thead>
                                      <tbody>
                                          @foreach($settings[$category] as $setting)
                                              <tr>
                                                  <td>
                                                      <code>{{ $setting['nombre'] }}</code>
                                                  </td>
                                                  <td>
                                                      <div class="d-flex align-items-center">
                                                          <div class="color-box me-2"
                                                              style="width: 30px; height: 30px; border-radius: 4px; border: 1px solid #ddd; background-color: {{ $setting['value'] }}">
                                                          </div>
                                                          <code>{{ $setting['value'] }}</code>
                                                      </div>
                                                  </td>
                                                  <td>
                                                    <code>{{ $setting['category'] }}</code>
                                                  </td>

                                                  <td>
                                                  @if($setting['gradient'] == 'true' )

                                                    SI

                                                  @endif
                                                </td>
                                                <td>
                                                  @if($setting['gradient'] == 'true' )
                                                  <div class="d-flex align-items-center">
                                                    <div class="color-box me-2"
                                                    style="width: 30px; height: 30px; border-radius: 4px; border: 1px solid #ddd; background-color: {{ $setting['value2'] }}">
                                                    </div>
                                                    <code>{{ $setting['value2'] }}</code>
                                                  </div>
                                                  @endif
                                                </td>

                                                <td>
                                                  @if($editingId === $setting['id'])
                                                      <div class="d-flex align-items-center">

                                                          <input id="value1-{{$setting['id']}}" type="text"  class="form-control me-2" wire:model="editingValue"  placeholder="#000000">

                                                          <input id="value2-{{$setting['id']}}" type="text"  class="form-control me-2  {{ $setting['gradient'] == false ? 'd-none' : '' }} " wire:model="editingValue2"  placeholder="#000000">
                                                          <button class="btn btn-primary btn-sm me-2"  wire:click="updateColor">
                                                              <i class="fas fa-save"></i>
                                                          </button>
                                                          <button class="btn btn-secondary btn-sm"  wire:click="cancelEditing">
                                                              <i class="fas fa-times"></i>
                                                          </button>
                                                      </div>
                                                      @error('editingValue')
                                                          <div class="text-danger mt-1">
                                                              {{ $message }}
                                                          </div>
                                                      @enderror
                                                  @else
                                                      <button class="btn btn-primary btn-sm"
                                                              wire:key="btn-editar{{$setting['id']}}" wire:click="startEditing({{ $setting['id'] }}, '{{ $setting['value'] }}','{{ $setting['value2'] }}' )">
                                                          <i class="fas fa-edit"></i> Editar
                                                      </button>
                                                  @endif
                                              </td>

                                              </tr>
                                          @endforeach
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                    </div>
                  </div>
              @endforeach
          </div>
      </div>
    </div>
</div>
