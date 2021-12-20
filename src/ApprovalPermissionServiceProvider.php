<?php
	namespace Exceptio\ApprovalPermission;

	use Illuminate\Support\ServiceProvider;
	use Illuminate\Support\Facades\Gate;

	use Exceptio\ApprovalPermission\Models\Approval;
	
	include_once(__DIR__.'/Helpers.php');

	class ApprovalPermissionServiceProvider extends ServiceProvider
	{
		public function boot()
		{
			$this->loadRoutesFrom(__DIR__.'/routes/web.php');
			
			if(config('approval-config.do-migration')){
				$this->loadMigrationsFrom(__DIR__.'/Database/migrations');
			}

			$this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-approval');

			$this->publishes([
				__DIR__.'/resources/views' => resource_path('views/vendor/laravel-approval')
			], 'views');
			$this->publishes([
				__DIR__.'/config/approval-config.php' => config_path('approval-config.php'),
			], 'config');
			$this->publishes([
				__DIR__.'/Database/migrations' => database_path('migrations'),
			], 'migration');
			$this->publishes([
				__DIR__.'/Notifications' => app_path(config('approval-config.notification-dir')),
			], 'notification');
			$this->publishes([
				__DIR__.'/Database/seeders' => database_path('seeders'),
			], 'seeder');

			$this->registerBladeDirectives();

			$this->registerGates();

		}

		public function register()
		{
			$this->mergeConfigFrom(
				__DIR__.'/config/approval-config.php', 'approval-config'
			);
		}

		/**
		 * Register Blade Directives.
		 *
		 * @return void
		 */
		protected function registerBladeDirectives()
		{
			$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
			
			$blade->directive('approvalMenu', function ($expression) {
				return "<?php echo view('laravel-approval::partials.menu')->render(); ?>";
			});
		}

		/**
		 * Register Gates.
		 *
		 * @return void
		 */
		protected function registerGates()
		{
			
		}

	}