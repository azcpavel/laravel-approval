<?php
$currentRouteName = request()->route()->getName();
$menuParentEl = config('approval-config.menu-parent');
$menuChieldEl = config('approval-config.menu-child');
$menuChieldClass = config('approval-config.menu-child-class');
$menuChieldActiveClass = config('approval-config.menu-child-active-class');
$menuLinkClass = config('approval-config.menu-link-class');
$menuLinkActiveClass = config('approval-config.menu-link-active-class');

$html = '<'.$menuChieldEl.' class="'.$menuChieldClass.' '.(strpos($currentRouteName,"approvers.") !== false ? $menuChieldActiveClass : '').'">';
$html .= '<a href="javascript:void(0);" class="'.$menuLinkClass.' '.(strpos($currentRouteName,"approvers.") !== false ? $menuChieldActiveClass : '').'">Approval Management '.config('approval-config.menu-parent-icon').'</a>';

$html .= '<'.$menuParentEl.' class="'.config('approval-config.menu-parent-class').' '.(strpos($currentRouteName,"approvers.") !== false ? config('approval-config.menu-parent-active-class') : '').'">';

$html .= '<'.$menuChieldEl.' class="'.$menuChieldClass.' '.(strpos($currentRouteName,"approvers.index") !== false ? $menuChieldActiveClass : '').'">';
$html .= '<a href="'.route('approvers.index').'" class="'.$menuLinkClass.' '.(strpos($currentRouteName,"approvers.index") !== false ? $menuChieldActiveClass : '').'">Approvals</a>';
$html .= '</'.$menuChieldEl.'>';

$html .= '</'.$menuParentEl.'>';

$html .= '</'.$menuChieldEl.'>';
?>