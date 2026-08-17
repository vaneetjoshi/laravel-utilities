<?php

namespace Vaneetjoshi\LaravelUtilities;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vaneetjoshi\LaravelUtilities\Contracts\OptionsStoreInterface;
use Vaneetjoshi\LaravelUtilities\Services\FormattingService;
use Vaneetjoshi\LaravelUtilities\Services\HookService;
use Vaneetjoshi\LaravelUtilities\Services\OptionsService;
use Vaneetjoshi\LaravelUtilities\Widgets\WidgetManager;
use Vaneetjoshi\LaravelUtilities\Navbars\NavbarManager;
use Vaneetjoshi\LaravelUtilities\Settings\SettingsRegistry;

class LaravelUtilitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/utilities.php', 'utilities');
        $this->mergeConfigFrom(__DIR__ . '/../config/widgets.php', 'widgets');

        $this->app->singleton(OptionsStoreInterface::class, function ($app) {
            $storeClass = config('utilities.options_store', \Vaneetjoshi\LaravelUtilities\Stores\DatabaseOptionsStore::class);
            return new $storeClass();
        });

        // Register the new Settings Registry Singleton
        $this->app->singleton(SettingsRegistry::class, function () {
            return new SettingsRegistry();
        });

        $this->app->singleton('utilities.options', fn ($app) => new OptionsService($app->make(OptionsStoreInterface::class)));
        $this->app->singleton('utilities.hooks', fn () => new HookService());
        $this->app->singleton('utilities.formatting', fn () => new FormattingService());
        
        $this->app->singleton('utilities.widgets', fn ($app) => new WidgetManager($app->make('utilities.hooks')));
        $this->app->singleton('utilities.navbars', fn ($app) => new NavbarManager($app->make('utilities.hooks')));

        $this->app->alias('utilities.options', OptionsService::class);
        $this->app->alias('utilities.hooks', HookService::class);
        $this->app->alias('utilities.formatting', FormattingService::class);
        $this->app->alias('utilities.widgets', WidgetManager::class);
        $this->app->alias('utilities.navbars', NavbarManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'utilities');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register Global Blade Components
        Blade::component('utilities::components.icon', 'icon');
        Blade::component('utilities::components.settings-panel', 'utilities::settings-panel');

        // Register Custom Blade Directives for Hook Engine
        Blade::directive('getOption', function (string $key, mixed $default = null) {
            return "<?php echo getOption($key, $default); ?>";
        });

        Blade::directive('renderView', function (string $expression) {
            return "<?php echo renderView({$expression}); ?>";
        });

        Blade::directive('applyFilters', function (string $hook, mixed $value, mixed ...$args) {
            return "<?php echo applyFilters($hook, $value, ...$args); ?>";
        });

        // Always load the headless PUT routing
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/utilities.php' => config_path('utilities.php'),
                __DIR__ . '/../config/settings.php' => config_path('settings.php'),
                __DIR__ . '/../config/widgets.php' => config_path('widgets.php'),
            ], 'utilities-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/utilities'),
            ], 'utilities-views');
        }

        if ($this->app->bound('events')) {
            $events = $this->app['events'];

            if (class_exists(\Laravel\Octane\Events\RequestReceived::class)) {
                $events->listen(\Laravel\Octane\Events\RequestReceived::class, function () {
                    $this->app->make(OptionsStoreInterface::class)->flushCache();
                });
            }

            if (class_exists(\Illuminate\Queue\Events\Looping::class)) {
                $events->listen(\Illuminate\Queue\Events\Looping::class, function () {
                    $this->app->make(OptionsStoreInterface::class)->flushCache();
                });
            }
        }

        // Auto-discover and register all Host Application Hooks
        $hooksDirectory = app_path('Hooks');
        if (is_dir($hooksDirectory)) {
            $hookFiles = glob($hooksDirectory . '/*.php');
            if ($hookFiles !== false) {
                foreach ($hookFiles as $file) {
                    require_once $file;
                }
            }
        }
    }
}