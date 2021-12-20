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
?>

<{{$menuChieldEl}} class="{{$menuChieldClass.' '.(strpos($currentRouteName,"approvals.") !== false ? $menuChieldActiveClass : '')}}">
	<a href="javascript:void(0);" class="{{$menuLinkClass.' '.(strpos($currentRouteName,"approvals.") !== false ? $menuChieldActiveClass : '')}}">{!!config('approval-config.menu-parent-title')!!}</a>

	<{{$menuParentEl}} class="{{config('approval-config.menu-parent-class').' '.(strpos($currentRouteName,"approvals.") !== false ? config('approval-config.menu-parent-active-class') : '')}}">

		<{{$menuChieldEl}} class="{{$menuChieldClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '')}}">
		<a href="{{route('approvals.index')}}" class="{{$menuLinkClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuChieldActiveClass : '')}}">{!!$menuChieldTitlePrefix.'Approvals'.$menuChieldTitlePostfix!!}</a>
		</{{$menuChieldEl}}>

		@foreach($approvals as $keyA => $valueA)
			<{{$menuChieldEl}} class="{{$menuChieldClass.' '.(strpos($currentRouteName,"approval_request.index") !== false ? $menuChieldActiveClass : '')}}">
			<a href="{{route('approval_request.index',['approval' => $valueA->id])}}" class="{{$menuLinkClass.' '.(strpos($currentRouteName,"approval_request.index") !== false ? $menuChieldActiveClass : '')}}">{!!$menuChieldTitlePrefix.$valueA->title.$menuChieldTitlePostfix!!}</a>
			</{{$menuChieldEl}}>
		@endforeach

	</{{$menuParentEl}}>

</{{$menuChieldEl}}>
