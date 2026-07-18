<?php

namespace App\Providers;

use App\Repositories\Contracts\TodoRepositoryInterface;
use App\Repositories\Contracts\ClientRepositoryInterface;

use App\Repositories\TodoRepository;
use App\Repositories\ClientRepository;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TodoRepositoryInterface::class, TodoRepository::class);
        $this->app->bind(ClientRepositoryInterface::class,ClientRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
