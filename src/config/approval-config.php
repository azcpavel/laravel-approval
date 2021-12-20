<?php 

    return [
        'approvalpermission-enable' => env('APPROVALPERMISSION_ENABLE',true),
        'do-migration' => true,
        'model-dir' => 'Models',
        'user-model' => 'App\Models\User',
        'user-table' => 'users',
        'user-primary-key' => 'id',
        'user-type-column' => 'user_type',
        'user-type-value' => 1,
        'name' => 'name',
        'login-route' => 'login',
        'dashboard-url' => env('APP_URL'),
        'view-layout' => 'layouts.app',
        'view-section' => 'content',
        'route-middleware' => ['web', 'auth'],
        'route-prefix' => 'admin',
        'menu-parent' => 'ul',
        'menu-parent-class' => 'nav nav-treeview',
        'menu-parent-active-class' => 'active',
        'menu-parent-title' => '<i class="nav-icon fas fa-cog left"></i><p> Approval Management<i class="fas fa-angle-left right"></i></p>',
        'menu-child' => 'li',
        'menu-child-class' => 'nav-item',
        'menu-child-active-class' => 'active',        
        'menu-link-class' => 'nav-link',
        'menu-link-active-class' => 'active',
        'menu-link-title-prefix' => '<i class="far fa-circle nav-icon"></i><p>',
        'menu-link-title-postfix' => '</p>',
        'load-script' => true,
        'script-stack' => 'script',
        'notification-dir' => 'Notifications',
        'upload-dir' => 'uploads',
        'dev-mode' => env('APPROVALPERMISSION_DEV',false)
    ];