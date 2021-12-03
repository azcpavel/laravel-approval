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
				$currentRouteName = request()->route()->getName();
				$menuParentEl = config('approval-config.menu-parent');
				$menuChieldEl = config('approval-config.menu-child');
				$menuChieldClass = config('approval-config.menu-child-class');
				$menuChieldActiveClass = config('approval-config.menu-child-active-class');
				$menuChieldTitlePrefix = config('approval-config.menu-child-title-prefix');
				$menuChieldTitlePostfix = config('approval-config.menu-child-title-postfix');
				$menuLinkClass = config('approval-config.menu-link-class');
				$menuLinkActiveClass = config('approval-config.menu-link-active-class');
				
				$html = '<'.$menuChieldEl.' class="'.$menuChieldClass.' '.(strpos($currentRouteName,"approvals.") !== false ? $menuChieldActiveClass : '').'">';
				$html .= '<a href="javascript:void(0);" class="'.$menuLinkClass.' '.(strpos($currentRouteName,"approvals.") !== false ? $menuChieldActiveClass : '').'">'.config('approval-config.menu-parent-title').'</a>';

				$html .= '<'.$menuParentEl.' class="'.config('approval-config.menu-parent-class').' '.(strpos($currentRouteName,"approvals.") !== false ? config('approval-config.menu-parent-active-class') : '').'">';

				$html .= '<'.$menuChieldEl.' class="'.$menuChieldClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '').'">';
				$html .= '<a href="'.route('approvals.index').'" class="'.$menuLinkClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '').'">'.$menuChieldTitlePrefix.'Approvals'.$menuChieldTitlePostfix.'</a>';
				$html .= '</'.$menuChieldEl.'>';

				$approvals = Approval::where('status',1)->get();

				foreach($approvals as $keyA => $valueA){
					$html .= '<'.$menuChieldEl.' class="'.$menuChieldClass.' '.(strpos($currentRouteName,"approval_request.index") !== false ? $menuChieldActiveClass : '').'">';
					$html .= '<a href="'.route('approval_request.index',['approval' => $valueA->id]).'" class="'.$menuLinkClass.' '.(strpos($currentRouteName,"approval_request.index") !== false ? $menuChieldActiveClass : '').'">'.$menuChieldTitlePrefix.$valueA->title.$menuChieldTitlePostfix.'</a>';
					$html .= '</'.$menuChieldEl.'>';
				}

				$html .= '</'.$menuParentEl.'>';

				$html .= '</'.$menuChieldEl.'>';

				return $html;
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