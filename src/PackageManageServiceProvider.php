<?php
/**
 * Created by PhpStorm.
 * User: basu
 * Date: 12/13/17
 * Time: 3:12 PM
 */

namespace olivemediapackage\PackageManage;

use Illuminate\Support\ServiceProvider;

class PackageManageServiceProvider extends ServiceProvider
{
    protected $commands = [
        'olivemediapackage\PackageManage\PackageManageNewCommand'
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/packager.php' => config_path('packager.php'),
        ]);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['packager'];
    }


}