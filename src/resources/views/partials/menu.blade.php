<?php
$currentRouteName = request()->route()->getName();
$menuParentEl = config('approval-config.menu-parent');
$menuChieldEl = config('approval-config.menu-child');
$menuChieldClass = config('approval-config.menu-child-class');
$menuChieldActiveClass = config('approval-config.menu-child-active-class');
$menuChieldTitlePrefix = config('approval-config.menu-child-title-prefix');
$menuChieldTitlePostfix = config('approval-config.menu-child-title-postfix');
$menuLinkClass = config('approval-config.menu-link-class');
$menuLinkActiveClass = config('approval-config.menu-link-active-class');
$approvals = Exceptio\ApprovalPermission\Models\Approval::where('status',1)->get();
$isMenuActive = strpos($currentRouteName,"approvals.") !== false || strpos($currentRouteName,"approval_request.") !== false;
?>

<{{$menuChieldEl}} class="{{$menuChieldClass.' '.($isMenuActive ? $menuLinkActiveClass : '')}}">
	<a href="javascript:void(0);" class="{{$menuLinkClass.' '.($isMenuActive ? $menuLinkActiveClass : '')}}">{!!config('approval-config.menu-parent-title')!!}</a>

	<{{$menuParentEl}} class="{{config('approval-config.menu-parent-class').' '.($isMenuActive ? config('approval-config.menu-parent-active-class') : '')}}">

		<{{$menuChieldEl}} class="{{$menuChieldClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '')}}">
		<a href="{{route('approvals.index')}}" class="{{$menuLinkClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '')}}">{!!$menuChieldTitlePrefix.'Approvals'.$menuChieldTitlePostfix!!}</a>
		</{{$menuChieldEl}}>

		@foreach($approvals as $keyA => $valueA)
			<?php
			$isChildActive = strpos($currentRouteName,"approval_request.index") !== false && request()->route('approval')->id == $valueA->id;
			?>
			<{{$menuChieldEl}} class="{{$menuChieldClass.' '.($isChildActive ? $menuChieldActiveClass : '')}}">
			<a href="{{route('approval_request.index',['approval' => $valueA->id])}}" class="{{$menuLinkClass.' '.( $isChildActive ? $menuChieldActiveClass : '')}}">{!!$menuChieldTitlePrefix.$valueA->title.$menuChieldTitlePostfix!!}</a>
			</{{$menuChieldEl}}>
		@endforeach

	</{{$menuParentEl}}>

</{{$menuChieldEl}}>
