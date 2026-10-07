<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;


use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use App\Listeners\StoreEmailMessageID;

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

        tap($this->app, function ($app) {
            $app->singleton(
                'storage',
                fn ($app) => config('spectacase.app.disk')
            );
        })->singleton(
            'default_image',
            fn ($app) => config('spectacase.app.default_image')
        );
        $listModule = self::getViews();
        foreach ($listModule as $module) {
            $this->loadViewsFrom($module, ucfirst(basename($module)));
        }



        Event::listen(MessageSent::class, StoreEmailMessageID::class);

    }

    public static function getViews()
    {
        return array_filter(glob(base_path() . '/resources/views/*'), 'is_dir');
    }
}
