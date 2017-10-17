<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}


	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		if ($this->app->environment('local') || $this->app['config']->get('app.debug')) {
			$this->registerDevProviders();
		}
	}


	/**
	 * Register package used only for developing
	 */
	protected function registerDevProviders()
	{
		$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		$this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
	}
}
