<?php

namespace Exceptio\ApprovalPermission\Http\Controllers;

use Exceptio\ApprovalPermission\Models\{
	Approval,
	ApprovalLevel,
	ApprovalLevelForm,
	ApprovalLevelFormData,
	ApprovalLevelUser,
	ApprovalMapping,
	ApprovalMappingField,
	ApprovalRequest,
	ApprovalRequestApprover,
	ApprovalRequestApproverForm,
	ApprovalRequestApproverFormData,
	ApprovalRequestMappingField,
	ApprovalRequestMappingFieldData
};

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Artisan;

class ApprovalController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		
		if($request->wantsJson()){
			$approval = new Approval();
			$limit = 10;
			$offset = 0;
			$search = [];
			$where = [];
			$with = [];
			$join = [];
			$orderBy = [];

			if($request->input('length')){
				$limit = $request->input('length');
			}

			if ($request->input('order')[0]['column'] != 0) {
				$column_name = $request->input('columns')[$request->input('order')[0]['column']]['name'];
				$sort = $request->input('order')[0]['dir'];
				$orderBy[$column_name] = $sort;
			}

			if($request->input('start')){
				$offset = $request->input('start');
			}

			if($request->input('search') && $request->input('search')['value'] != ""){
				$search['title'] = $request->input('search')['value'];
			}

			if($request->input('where')){
				$where = $request->input('where');
			}

			$approval = $approval->getDataForDataTable($limit, $offset, $search, $where, $with, $join, $orderBy,  $request->all());

			return response()->json($approval);
		}
		
		return view('laravel-approval::index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		if(!config('approval-config.dev-mode'))
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Invalid Action', 'msg_type' => 'danger']);
		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.model-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['models'] = [];

		foreach($Regex as $file){
			$data['models'][]=namespacePath($file[0]);
		}

		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.notification-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['notifications'] = [];

		foreach($Regex as $file){
			$data['notifications'][]=namespacePath($file[0]);
		}

		$userModel = config('approval-config.user-model');
		$users = new $userModel();
		if(is_array(config('approval-config.user-type-value')))
			$data['users'] = $users->whereIn(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();
		else
			$data['users'] = $users->where(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();

		return view("laravel-approval::create",$data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(!config('approval-config.dev-mode'))
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Invalid Action', 'msg_type' => 'danger']);
		DB::beginTransaction();
		try{
			$approval = Approval::create([
				'title' => $request->title,
				'approvable_type' => $request->model_namespace,
				'view_route_name' => $request->view_route_name,
				'view_route_param' => array_combine($request->view_route_param_key,$request->view_route_param_value),
				'slug' => $request->slug,
				'list_data_fields' => $request->list_data_fields,
				'on_create' => $request->approval_type == 1 ? 1 : 0,
				'on_update' => $request->approval_type == 2 ? 1 : 0,
				'on_delete' => $request->approval_type == 3 ? 1 : 0,
				'do_delete' => $request->do_delete,
				'do_swap' => $request->do_swap,
			]);

			if($request->model_namespace_relation_key)
			foreach($request->model_namespace_relation_key as $keyR => $valueR){
				$approvalMap = $approval->mappings()->create([
					'title' => $request->model_namespace_relation_title[$keyR],
					'approvable_type' => $request->model_namespace_relation_path[$keyR],
					'relation' => $request->model_relation_path[$keyR],
				]);

				foreach($request->model_namespace_relation_tbody_check[$valueR] as $keyRD => $valueRD){
					$approvalMap->fields()->create([
						'field_name' => $request->model_namespace_relation_tbody_name[$valueR][$valueRD],
				        'field_label' => $request->model_namespace_relation_tbody_label[$valueR][$valueRD],
				        'field_relation' => $request->model_namespace_relation_tbody_relation[$valueR][$valueRD],
				        'field_relation_pk' => $request->model_namespace_relation_tbody_relation_pk[$valueR][$valueRD],
				        'field_relation_show' => $request->model_namespace_relation_tbody_relation_show[$valueR][$valueRD],
				        'field_type' => $request->model_namespace_relation_tbody_type[$valueR][$valueRD]
				    ]);
				}
			}

			if($request->approval_title)
			foreach($request->approval_title as $keyL => $valueL){
				$action_data = null;
				$status_fields = null;
				if($request->approval_action_type[$keyL] == 1){
					if($request->approval_action_class_before_path[$keyL] != ''){
						$action_data['before'] = [
							'class' => $request->approval_action_class_before_path[$keyL],
							'method' => $request->approval_action_class_before_method[$keyL]
						];
					}

					if($request->approval_action_class_approve_path[$keyL] != ''){
						$action_data['approve'] = [
							'class' => $request->approval_action_class_approve_path[$keyL],
							'method' => $request->approval_action_class_approve_method[$keyL]
						];
					}

					if($request->approval_action_class_reject_path[$keyL] != ''){
						$action_data['reject'] = [
							'class' => $request->approval_action_class_reject_path[$keyL],
							'method' => $request->approval_action_class_reject_method[$keyL]
						];
					}
				}

				if($request->approval_action_type[$keyL] == 2){
					if($request->approval_action_url_approve_route[$keyL] != ''){
						$action_data['approve'] = [
							'route' => $request->approval_action_url_approve_route[$keyL],
							'param' => json_decode($request->approval_action_url_approve_param[$keyL])
						];
					}

					if($request->approval_action_url_reject_route[$keyL] != ''){
						$action_data['reject'] = [
							'route' => $request->approval_action_url_reject_route[$keyL],
							'param' => json_decode($request->approval_action_url_reject_param[$keyL])
						];
					}
				}

				if($request->approval_status_fields_approve_column && $request->approval_status_fields_approve_column[$keyL]){
					foreach($request->approval_status_fields_approve_column[$keyL] as $keySF => $valueSF){
						$status_fields['approve'][$request->approval_status_fields_approve_column[$keyL][$keySF]] = $request->approval_status_fields_approve_value[$keyL][$keySF];
					}
				}

				if($request->approval_status_fields_reject_column && $request->approval_status_fields_reject_column[$keyL]){
					foreach($request->approval_status_fields_reject_column[$keyL] as $keySF => $valueSF){
						$status_fields['reject'][$request->approval_status_fields_reject_column[$keyL][$keySF]] = $request->approval_status_fields_reject_value[$keyL][$keySF];						
					}
				}

				$approvalLevel = $approval->levels()->create([
					'title' => $request->approval_title[$keyL],
					'is_flexible' => $request->approval_flex[$keyL],
					'is_form_required' => $request->approval_form[$keyL],
					'level' => $request->approval_level[$keyL],
					'action_type' => $request->approval_action_type[$keyL],
					'action_data' => $action_data,
					'action_frequency' => $request->approval_action_frequency[$keyL],
					'status_fields' => $status_fields,
					'is_data_mapped' => $request->approval_data_mapped[$keyL],
					'notifiable_class' => $request->approval_notifiable_namespace[$keyL],
					'notifiable_params' => ['channels' => json_decode($request->approval_notifiable_params[$keyL])],
					'group_notification' => $request->approval_group_notification[$keyL],
					'next_level_notification' => $request->approval_next_notification[$keyL],
					'is_approve_reason_required' => $request->approval_approve_reason[$keyL],
					'is_reject_reason_required' => $request->approval_reject_reason[$keyL],
				]);

				if(isset($request->approval_user[$keyL]))
				foreach($request->approval_user[$keyL] as $keyU => $valueU){
					$approvalLevel->approval_users()->create([
						'user_id' => $valueU,
					]);
				}

				if($request->approval_form[$keyL] == 1){
					foreach($request->approval_form_title[$keyL] as $keyF => $valueF){
						$approvalLevelForm = $approvalLevel->forms()->create([
							'title' => $request->approval_form_title[$keyL][$keyF],
							'approvable_type' => $request->approval_form_path[$keyL][$keyF],
							'relation' => $request->approval_form_relation[$keyL][$keyF],
						]);

						$formKey = $request->approval_form_key[$keyL][$keyF];
						foreach($request->approval_form_tbody_check[$keyL][$formKey] as $keyFK => $valueFK){
							$approvalLevelForm->form_data()->create([
								'mapped_field_name' => $request->approval_form_tbody_name[$keyL][$formKey][$valueFK],
						        'mapped_field_label' => $request->approval_form_tbody_label[$keyL][$formKey][$valueFK],
						        'mapped_field_relation' => $request->approval_form_tbody_relation[$keyL][$formKey][$valueFK],
						        'mapped_field_relation_pk' => $request->approval_form_tbody_relation_pk[$keyL][$formKey][$valueFK],
						        'mapped_field_relation_show' => $request->approval_form_tbody_relation_show[$keyL][$formKey][$valueFK],
						        'mapped_field_type' => $request->approval_form_tbody_type[$keyL][$formKey][$valueFK],
							]);
						}						
					}
				}
			}

			Artisan::call('view:clear');
			DB::commit();			
		}catch(\Exception $e){
			DB::rollBack();
			if(env('APP_DEBUG'))
				dd($request->all(),$e);
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Approval Submission Error', 'msg_type' => 'danger']);
		}

		return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Approval Added', 'msg_type' => 'success']);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Approval  $approval
	 * @return \Illuminate\Http\Response
	 */
	public function show(Approval $approval)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\Approval  $approval
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Approval $approval)
	{
		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.model-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['models'] = [];

		foreach($Regex as $file){
			$data['models'][]=namespacePath($file[0]);
		}

		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.notification-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['notifications'] = [];

		foreach($Regex as $file){
			$data['notifications'][]=namespacePath($file[0]);
		}

		$userModel = config('approval-config.user-model');
		$users = new $userModel();
		
		if(is_array(config('approval-config.user-type-value')))
			$data['users'] = $users->whereIn(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();
		else
			$data['users'] = $users->where(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();
		
		$data['approval'] = $approval;
		return view("laravel-approval::edit",$data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Approval  $approval
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Approval $approval)
	{
		DB::beginTransaction();
		try{
			if(config('approval-config.dev-mode')){
				$approval->update([
					'title' => $request->title,
					'approvable_type' => $request->model_namespace,
					'view_route_name' => $request->view_route_name,
					'view_route_param' => array_combine($request->view_route_param_key,$request->view_route_param_value),
					'slug' => $request->slug,
					'list_data_fields' => $request->list_data_fields,
					'on_create' => $request->approval_type == 1 ? 1 : 0,
					'on_update' => $request->approval_type == 2 ? 1 : 0,
					'on_delete' => $request->approval_type == 3 ? 1 : 0,
					'do_delete' => $request->do_delete,
					'do_swap' => $request->do_swap,
				]);

				$approval->levels()->delete();
				$approval->mappings()->delete();

				if($request->model_namespace_relation_key)
				foreach($request->model_namespace_relation_key as $keyR => $valueR){
					$approvalMap = $approval->mappings()->create([
						'title' => $request->model_namespace_relation_title[$keyR],
						'approvable_type' => $request->model_namespace_relation_path[$keyR],
						'relation' => $request->model_relation_path[$keyR],
					]);

					foreach($request->model_namespace_relation_tbody_check[$valueR] as $keyRD => $valueRD){
						$approvalMap->fields()->create([
							'field_name' => $request->model_namespace_relation_tbody_name[$valueR][$valueRD],
					        'field_label' => $request->model_namespace_relation_tbody_label[$valueR][$valueRD],
					        'field_relation' => $request->model_namespace_relation_tbody_relation[$valueR][$valueRD],
					        'field_relation_pk' => $request->model_namespace_relation_tbody_relation_pk[$valueR][$valueRD],
					        'field_relation_show' => $request->model_namespace_relation_tbody_relation_show[$valueR][$valueRD],
					        'field_type' => $request->model_namespace_relation_tbody_type[$valueR][$valueRD]
					    ]);
					}
				}

				if($request->approval_title)
				foreach($request->approval_title as $keyL => $valueL){
					$action_data = null;
					$status_fields = null;
					if($request->approval_action_type[$keyL] == 1){
						if($request->approval_action_class_before_path[$keyL] != ''){
							$action_data['before'] = [
								'class' => $request->approval_action_class_before_path[$keyL],
								'method' => $request->approval_action_class_before_method[$keyL]
							];
						}

						if($request->approval_action_class_approve_path[$keyL] != ''){
							$action_data['approve'] = [
								'class' => $request->approval_action_class_approve_path[$keyL],
								'method' => $request->approval_action_class_approve_method[$keyL]
							];
						}

						if($request->approval_action_class_reject_path[$keyL] != ''){
							$action_data['reject'] = [
								'class' => $request->approval_action_class_reject_path[$keyL],
								'method' => $request->approval_action_class_reject_method[$keyL]
							];
						}
					}

					if($request->approval_action_type[$keyL] == 2){
						if($request->approval_action_url_approve_route[$keyL] != ''){
							$action_data['approve'] = [
								'route' => $request->approval_action_url_approve_route[$keyL],
								'param' => json_decode($request->approval_action_url_approve_param[$keyL])
							];
						}

						if($request->approval_action_url_reject_route[$keyL] != ''){
							$action_data['reject'] = [
								'route' => $request->approval_action_url_reject_route[$keyL],
								'param' => json_decode($request->approval_action_url_reject_param[$keyL])
							];
						}
					}

					if($request->approval_status_fields_approve_column[$keyL]){
						foreach($request->approval_status_fields_approve_column[$keyL] as $keySF => $valueSF){
							if($request->approval_status_fields_approve_value[$keyL][$keySF])
								$status_fields['approve'][$request->approval_status_fields_approve_column[$keyL][$keySF]] = $request->approval_status_fields_approve_value[$keyL][$keySF];
						}
					}

					if($request->approval_status_fields_reject_column[$keyL]){
						foreach($request->approval_status_fields_reject_column[$keyL] as $keySF => $valueSF){
							if($request->approval_status_fields_reject_value[$keyL][$keySF])
								$status_fields['reject'][$request->approval_status_fields_reject_column[$keyL][$keySF]] = $request->approval_status_fields_reject_value[$keyL][$keySF];						
						}
					}

					$approvalLevel = $approval->levels()->create([
						'title' => $request->approval_title[$keyL],
						'is_flexible' => $request->approval_flex[$keyL],
						'is_form_required' => $request->approval_form[$keyL],
						'level' => $request->approval_level[$keyL],
						'action_type' => $request->approval_action_type[$keyL],
						'action_data' => $action_data,
						'action_frequency' => $request->approval_action_frequency[$keyL],
						'status_fields' => $status_fields,
						'is_data_mapped' => $request->approval_data_mapped[$keyL],
						'notifiable_class' => $request->approval_notifiable_namespace[$keyL],
						'notifiable_params' => ['channels' => json_decode($request->approval_notifiable_params[$keyL])],
						'group_notification' => $request->approval_group_notification[$keyL],
						'next_level_notification' => $request->approval_next_notification[$keyL],
						'is_approve_reason_required' => $request->approval_approve_reason[$keyL],
						'is_reject_reason_required' => $request->approval_reject_reason[$keyL],
					]);

					if(isset($request->approval_user[$keyL]))
					foreach($request->approval_user[$keyL] as $keyU => $valueU){
						$approvalLevel->approval_users()->create([
							'user_id' => $valueU,
						]);
					}

					if($request->approval_form[$keyL] == 1){
						foreach($request->approval_form_title[$keyL] as $keyF => $valueF){
							$approvalLevelForm = $approvalLevel->forms()->create([
								'title' => $request->approval_form_title[$keyL][$keyF],
								'approvable_type' => $request->approval_form_path[$keyL][$keyF],
								'relation' => $request->approval_form_relation[$keyL][$keyF],
							]);

							$formKey = $request->approval_form_key[$keyL][$keyF];
							foreach($request->approval_form_tbody_check[$keyL][$formKey] as $keyFK => $valueFK){
								$approvalLevelForm->form_data()->create([
									'mapped_field_name' => $request->approval_form_tbody_name[$keyL][$formKey][$valueFK],
							        'mapped_field_label' => $request->approval_form_tbody_label[$keyL][$formKey][$valueFK],
							        'mapped_field_relation' => $request->approval_form_tbody_relation[$keyL][$formKey][$valueFK],
							        'mapped_field_relation_pk' => $request->approval_form_tbody_relation_pk[$keyL][$formKey][$valueFK],
							        'mapped_field_relation_show' => $request->approval_form_tbody_relation_show[$keyL][$formKey][$valueFK],
							        'mapped_field_type' => $request->approval_form_tbody_type[$keyL][$formKey][$valueFK],
								]);
							}						
						}
					}
				}

				Artisan::call('view:clear');
				DB::commit();
			}else{
				$approval->update([
					'title' => $request->title,					
					'do_swap' => $request->do_swap,
				]);

				if($request->approval_title)
				foreach($request->approval_title as $keyL => $valueL){
					$approvalLevel = $approval->levels->where('level',$request->approval_level[$keyL])->first();
					
					$approvalLevel->update([
						'title' => $request->approval_title[$keyL],
						'is_flexible' => $request->approval_flex[$keyL],						
						'action_frequency' => $request->approval_action_frequency[$keyL],						
						'group_notification' => $request->approval_group_notification[$keyL],
						'next_level_notification' => $request->approval_next_notification[$keyL],
						'is_approve_reason_required' => $request->approval_approve_reason[$keyL],
						'is_reject_reason_required' => $request->approval_reject_reason[$keyL]
					]);
					$approvalLevel->approval_users()->delete();
					if(isset($request->approval_user[$keyL]))
					foreach($request->approval_user[$keyL] as $keyU => $valueU){
						$approvalLevel->approval_users()->create([
							'user_id' => $valueU,
						]);
					}
				}
				Artisan::call('view:clear');
				DB::commit();
				return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Approval Level User Updated', 'msg_type' => 'success']);
			}
		}catch(\Exception $e){
			DB::rollBack();
			if(env('APP_DEBUG'))
				dd($request->all(),$e);
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Approval Submission Error', 'msg_type' => 'danger']);		
		}

		return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Approval Updated', 'msg_type' => 'success']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Approval  $approval
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Approval $approval)
	{
		if(!config('approval-config.dev-mode'))
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Invalid Action', 'msg_type' => 'danger']);
		if($approval->requests->count() > 0){
			$message = ['msg_data' => 'Deleted Fail, Data Exist!', 'msg_type' => 'danger'];
		}
		else{
			$approval->levels()->delete();
			$approval->levels()->mappings();
			$approval->delete();
			$message = ['msg_data' => 'Approval Deleted', 'msg_type' => 'success'];
			Artisan::call('view:clear');
		}
		return redirect()->route(config('approval-config.route-name-prefix').'.index')->with($message);
	}

	public function modelColumn(Request $request){
		$model = new $request->model_namespace();
		return \DB::select('SHOW COLUMNS FROM '.$model->getTable());        
	}

	public function approvelLevelForm(Request $request){
		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.model-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['models'] = [];

		foreach($Regex as $file){
			$data['models'][]=namespacePath($file[0]);
		}

		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.notification-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['notifications'] = [];

		foreach($Regex as $file){
			$data['notifications'][]=namespacePath($file[0]);
		}

		$userModel = config('approval-config.user-model');
		$users = new $userModel();
		
		if(is_array(config('approval-config.user-type-value')))
			$data['users'] = $users->whereIn(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();
		else
			$data['users'] = $users->where(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();

		return view("laravel-approval::partials.approval_level",$data);
	}

	public function changeStatus(Request $request, Approval $approval){
		if(!config('approval-config.dev-mode'))
			return redirect()->route(config('approval-config.route-name-prefix').'.index')->with(['msg_data' => 'Invalid Action', 'msg_type' => 'danger']);
		$approval->status = !$approval->status;
		$approval->update();
		Artisan::call('view:clear');
	}
}
