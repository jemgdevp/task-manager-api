<?php

namespace App\Providers;

use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::routes(static function (IlluminateRoute $route): bool {
            $uri = ltrim($route->uri, '/');

            return str_starts_with($uri, 'api/')
                && ! in_array($uri, ['api/documentation', 'api/oauth2-callback'], true);
        });

        Scramble::configure()->withOperationTransformers(static function (Operation $operation, RouteInfo $routeInfo): void {
            $requiresAuth = collect($routeInfo->route->gatherMiddleware())
                ->contains(static fn ($middleware) => is_string($middleware) && ($middleware === 'auth' || str_starts_with($middleware, 'auth:')));

            if (! $requiresAuth) {
                return;
            }

            $operation->addSecurity(new SecurityRequirement([
                'bearerAuth' => [],
            ]));
        });

        Scramble::afterOpenApiGenerated(static function (OpenApi $openApi, OpenApiContext $context): void {
            $openApi->components->addSecurityScheme(
                'bearerAuth',
                SecurityScheme::http('bearer', 'Token')
                    ->as('bearerAuth')
                    ->setDescription('Use Authorization header with: Bearer <token>')
            );
        });

        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Verify your email address')
                ->view('user.verify.email', [
                    'user' => $notifiable,
                    'verificationUrl' => $url,
                ]);
        });
    }
}
