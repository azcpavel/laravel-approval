<?php
$currentRouteName = request()->route()->getName();
$menuParentEl = config('approval-config.menu-parent');
$menuParentActiveClass = config('approval-config.menu-parent-active-class');
$menuChildEl = config('approval-config.menu-child');
$menuChildClass = config('approval-config.menu-child-class');
$menuChildActiveClass = config('approval-config.menu-child-active-class');
$menuLinkClass = config('approval-config.menu-link-class');
$menuLinkActiveClass = config('approval-config.menu-link-active-class');
$menuLinkTitlePrefix = config('approval-config.menu-link-title-prefix');
$menuLinkTitlePostfix = config('approval-config.menu-link-title-postfix');
$approvals = Exceptio\ApprovalPermission\Models\Approval::where('status',1)->get();
$isMenuActive = strpos($currentRouteName,"approvals.") !== false || strpos($currentRouteName,"approval_request.") !== false;
?>

<{{$menuChildEl}} class="{{$menuChildClass.' '.($isMenuActive ? $menuParentActiveClass : '')}}">
	<a href="javascript:void(0);" class="{{$menuLinkClass.' '.($isMenuActive ? $menuParentActiveClass : '')}}">{!!config('approval-config.menu-parent-title')!!}</a>

	<{{$menuParentEl}} class="{{config('approval-config.menu-parent-class').' '.($isMenuActive ? $menuChildActiveClass : '')}}">

		<{{$menuChildEl}} class="{{$menuChildClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuLinkActiveClass : '')}}">
		<a href="{{route('approvals.index')}}" class="{{$menuLinkClass.' '.(strpos($currentRouteName,"approvals.index") !== false ? $menuLinkActiveClass : '')}}">{!!$menuLinkTitlePrefix.'Approvals'.$menuLinkTitlePostfix!!}</a>
		</{{$menuChildEl}}>

		@foreach($approvals as $keyA => $valueA)
			<?php
			$isChildActive = strpos($currentRouteName,"approval_request.index") !== false && request()->route('approval')->id == $valueA->id;
			?>
			<{{$menuChildEl}} class="{{$menuChildClass.' '.($isChildActive ? $menuLinkActiveClass : '')}}">
			<a href="{{route('approval_request.index',['approval' => $valueA->id])}}" class="{{$menuLinkClass.' '.( $isChildActive ? $menuLinkActiveClass : '')}}">{!!$menuLinkTitlePrefix.$valueA->title.$menuLinkTitlePostfix!!}</a>
			</{{$menuChildEl}}>
		@endforeach

	</{{$menuParentEl}}>

</{{$menuChildEl}}>
