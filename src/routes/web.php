<?php


Route::group(['prefix' => config('approval-config.route-prefix'),'namespace' => 'Exceptio\ApprovalPermission\Http\Controllers', 
              'middleware' => config('approval-config.route-middleware')], function(){
    Route::resource('approvals','ApprovalController',['names' => config('approval-config.route-name-prefix')]);
    Route::get('approval-model','ApprovalController@modelColumn')->name(config('approval-config.route-name-prefix').'.model_info');
    Route::get('approval-level-form','ApprovalController@approvelLevelForm')->name(config('approval-config.route-name-prefix').'.model_level_form');
    Route::get('approval-change-status/{approval}','ApprovalController@changeStatus')->name(config('approval-config.route-name-prefix').'.change_status');
    
    Route::get('approval-request/{approval}','ApprovalRequestController@index')->name('approval_request.index');
    Route::get('approval-request/show/{approvalRequest}','ApprovalRequestController@show')->name('approval_request.show');
    Route::post('approval-request/submit/{approvalRequest}','ApprovalRequestController@submit')->name('approval_request.submit');
    Route::post('approval-request/swap/{approvalRequest}','ApprovalRequestController@swapLevel')->name('approval_request.swap_level');
});
