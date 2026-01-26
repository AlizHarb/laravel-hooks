<?php

namespace App\Livewire;

use AlizHarb\LaravelHooks\Traits\InteractsWithLivewireHooks;
use Livewire\Component;

/**
 * --------------------------------------------------------------------------
 * LIVEWIRE INTEGRATION
 * Using the InteractsWithLivewireHooks trait for reactive extensions.
 * --------------------------------------------------------------------------
 */
class SearchUsers extends Component
{
    use InteractsWithLivewireHooks;

    public $query = '';

    public function render()
    {
        // This fires 'livewire.SearchUsers.rendering' automatically
        return view('livewire.search-users', [
            'users' => User::where('name', 'like', "%{$this->query}%")->get(),
        ]);
    }

    /**
     * You can also manually fire hooks for custom events.
     */
    public function performSearch()
    {
        $this->fireLivewireHook('search_performed', ['query' => $this->query]);
    }
}

/**
 * CONSUMING MODULE (Example)
 * Listen to the hook from another module.
 */
// Hook::addAction('livewire.SearchUsers.search_performed', function ($component, $args) {
//     Log::info("User searched for: {$args['query']}");
// });
