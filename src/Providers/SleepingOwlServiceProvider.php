<?php

namespace SleepingOwl\Admin\Providers;

use Illuminate\Http\Request;
use SleepingOwl\Admin\Admin;
use SleepingOwl\Admin\Contracts\Template\TemplateInterface;

class SleepingOwlServiceProvider extends AdminSectionsServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/sleeping_owl.php', 'sleeping_owl');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'sleeping_owl');

        $this->registerCore();
        $this->registerCommands();
    }

    /**
     * @param Admin $admin
     */
    public function boot(Admin $admin)
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'sleeping_owl');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../public' => public_path('packages/sleepingowl/'),
            ], 'assets');

            $this->publishes([
                __DIR__.'/../../config/sleeping_owl.php' => config_path('sleeping_owl.php'),
            ], 'config');
        }

        parent::boot($admin);
    }

    protected function registerCore()
    {
        $this->app->instance('sleeping_owl', $admin = new Admin($this->app));

        $this->app->resolving('sleeping_owl.template', function(TemplateInterface $template, $app) use($admin) {
            $admin->setTemplate($template);
            $template->initialize();
        });

        $this->app->rebinding('request', function($app, Request $request) use($admin) {
            $admin->detectContext($request);
        });
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \SleepingOwl\Admin\Console\Commands\InstallCommand::class,
                \SleepingOwl\Admin\Console\Commands\UpdateCommand::class,
                \SleepingOwl\Admin\Console\Commands\UserManagerCommand::class,
                \SleepingOwl\Admin\Console\Commands\SectionGenerate::class,
                \SleepingOwl\Admin\Console\Commands\SectionMake::class,
                \SleepingOwl\Admin\Console\Commands\SectionProvider::class,
            ]);
        }
    }
}
