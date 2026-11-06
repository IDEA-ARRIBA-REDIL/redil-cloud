<div class="col-12 mt-3">
    @if ($connected)
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="text-black fw-bold mb-0">Síguenos en Instagram</h5>
            <a href="{{ 'https://www.instagram.com/'.rawurlencode($username).'/' }}" target="_blank"
                rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="ti ti-brand-instagram me-2"></i>{{ '@'.$username }}
            </a>
        </div>
        @if ($unavailable)
            <div class="card shadow-sm"><div class="card-body">
                <p class="mb-0 text-muted">Las publicaciones no están disponibles por el momento. Puedes verlas en nuestro perfil de Instagram.</p>
            </div></div>
        @elseif (count($posts) === 0)
            <div class="card shadow-sm"><div class="card-body">
                <p class="mb-0 text-muted">Aún no hay publicaciones disponibles en Instagram.</p>
            </div></div>
        @else
            <div class="row g-3">
                @foreach ($posts as $post)
                    <div class="col-6 col-md-4 col-xl-2" wire:key="instagram-{{ $post['id'] }}">
                        <a href="{{ $post['permalink'] }}" target="_blank" rel="noopener noreferrer"
                            class="card h-100 shadow-sm text-decoration-none overflow-hidden" aria-label="Ver publicación en Instagram">
                            @if ($post['image'])
                                <img src="{{ $post['image'] }}" alt="{{ \Illuminate\Support\Str::limit($post['caption'] ?: 'Publicación de Instagram', 120) }}"
                                    class="w-100 object-fit-cover" style="aspect-ratio: 1;" loading="lazy" referrerpolicy="no-referrer">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light" style="aspect-ratio: 1;">
                                    <i class="ti ti-brand-instagram fs-1" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div class="card-body p-3">
                                @if ($post['media_type'] === 'VIDEO')
                                    <span class="badge bg-label-primary mb-2">Video / Reel</span>
                                @elseif ($post['media_type'] === 'CAROUSEL_ALBUM')
                                    <span class="badge bg-label-primary mb-2">Carrusel</span>
                                @endif
                                <p class="small text-black mb-0">{{ \Illuminate\Support\Str::limit($post['caption'] ?: 'Ver en Instagram', 90) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
