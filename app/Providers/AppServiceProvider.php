<?php

namespace App\Providers;

use App\Interfaces\RepositoryProvider;
use App\Services\GitHubRepositoryService;
use App\Services\GitLabRepositoryService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RepositoryProvider::class, function ($app) {
            return [
                new GitLabRepositoryService(new Client()),
                new GitHubRepositoryService(new Client())
            ];
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
