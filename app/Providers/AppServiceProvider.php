<?php

namespace App\Providers;

use App\Contracts\RoleManagementService;
use App\Contracts\UserManagementService;
use App\Services\EloquentRoleManagementService;
use App\Services\EloquentUserManagementService;
use App\Services\Pide\Contracts\PideHttpClientInterface;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use App\Services\Pide\Contracts\SunatServiceInterface;
use App\Services\Pide\PideHttpClient;
use App\Services\Pide\ReniecService;
use App\Services\Pide\SunarpService;
use App\Services\Pide\SunatService;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserManagementService::class, EloquentUserManagementService::class);
        $this->app->bind(RoleManagementService::class, EloquentRoleManagementService::class);

        $this->app->singleton(PideHttpClientInterface::class, PideHttpClient::class);
        $this->app->bind(ReniecServiceInterface::class, ReniecService::class);
        $this->app->bind(SunatServiceInterface::class, SunatService::class);
        $this->app->bind(SunarpServiceInterface::class, SunarpService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->events->listen(RequestHandled::class, function ($handled) {
            $base = $handled->request->getBaseUrl();

            if ($base === '' || $base === '/') {
                return;
            }

            $response = $handled->response;

            if (! method_exists($response, 'getContent')) {
                return;
            }

            $content = $response->getContent();

            if (is_string($content) && str_contains($content, 'data-update-uri="/')) {
                $response->setContent(
                    str_replace('data-update-uri="/', 'data-update-uri="' . $base . '/', $content)
                );
            }
        }, -100);
    }
}
