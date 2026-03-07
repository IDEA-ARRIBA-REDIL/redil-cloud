<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\Configuracion;
use Livewire\Attributes\Renderless;

class NavbarPostsStatus extends Component
{
    public $posts = [];
    public $configuracion;
    public $perPage = 5;
    public $hasMore = true;
    public $cargandoMas = false;

    protected $listeners = ['loadMoreNavbarPosts' => 'loadMore'];

    public function mount()
    {
        $this->configuracion = Configuracion::first() ?? new Configuracion();
        $this->loadInitialPosts();
    }

    public function loadInitialPosts()
    {
        $this->posts = $this->queryPosts()->take($this->perPage)->get();
        // Verificar si hay más
        $this->hasMore = $this->queryPosts()->count() > $this->perPage;
    }

    public function loadMore()
    {
        if (!$this->hasMore) return;

        $this->cargandoMas = true;
        $skip = $this->posts->count();
        
        $nuevosPosts = $this->queryPosts()
            ->skip($skip)
            ->take($this->perPage)
            ->get();

        if ($nuevosPosts->isEmpty()) {
            $this->hasMore = false;
        } else {
            // Unir a la colección existente
            $this->posts = $this->posts->concat($nuevosPosts);
            if ($nuevosPosts->count() < $this->perPage) {
                $this->hasMore = false;
            }
        }
        
        $this->cargandoMas = false;
        
        // Despachar evento para actualizar Swiper
        $this->dispatch('navbar-posts-loaded');
    }

    #[Renderless]
    public function toggleLike($postId)
    {
        if (!auth()->check()) {
            return;
        }

        $post = Post::find($postId);
        if ($post) {
            $post->likes()->toggle(auth()->id());
        }
    }

    private function queryPosts()
    {
        // Misma lógica que el Dashboard/PostsWidget
        return Post::with('user', 'likes')
            ->forUser(auth()->user())
            ->where(function($q) {
                $q->where('visualizar_siempre', true)
                  ->orWhere(function($q2) {
                      $q2->whereDate('fecha_inicio', '<=', now())
                         ->where(function($q3) {
                             $q3->whereNull('fecha_fin')
                                ->orWhereDate('fecha_fin', '>=', now());
                         });
                  });
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.navbar-posts-status');
    }
}
