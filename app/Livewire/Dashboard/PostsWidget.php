<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Post;
use App\Models\Configuracion;
use Livewire\Attributes\Renderless;

class PostsWidget extends Component
{
    public $posts = [];
    public $configuracion;
    public $claseColumnas;
    public $perPage = 5;
    public $cargandoMas = false;
    public $hasMore = true;

    protected $listeners = ['loadMorePosts' => 'loadMore'];

    public function mount($claseColumnas = 'col-12 text-nowrap')
    {
        $this->claseColumnas = $claseColumnas;
        $this->configuracion = Configuracion::first();
        $this->loadInitialPosts();
        $this->titulo = $this->configuracion->version == 2 ? 'Vive Manantial' : 'Feed';
    }

    public function loadInitialPosts()
    {
        $this->posts = $this->queryPosts()->take($this->perPage)->get();
        // Verificamos si hay más
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
            // Unir a coleccion existente respetando la compatibilidad de Livewire
            $this->posts = $this->posts->concat($nuevosPosts);
            if ($nuevosPosts->count() < $this->perPage) {
                $this->hasMore = false;
            }
        }
        
        $this->cargandoMas = false;
        
        // Despachamos evento al navagedor para actualizar Swiper
        $this->dispatch('posts-loaded');
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
        return view('livewire.dashboard.posts-widget');
    }
}
