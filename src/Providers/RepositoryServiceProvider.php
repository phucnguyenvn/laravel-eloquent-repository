<?php

namespace phucnguyenvn\EloquentRepository\Providers;

use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(phucnguyenvn\EloquentRepository\Repositories\RepositoryInterface::class, function ($app){
            return $app->make(phucnguyenvn\EloquentRepository\Repositories\EloquentRepository::class);
        });
    }
}
