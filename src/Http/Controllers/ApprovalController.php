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
		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.model-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['models'] = [];

		foreach($Regex as $file){
			$data['models'][]=str_replace(app_path(), 'App', $file[0]);
		}

		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.notification-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['notifications'] = [];

		foreach($Regex as $file){
			$data['notifications'][]=str_replace(app_path(), 'App', $file[0]);
		}

		$userModel = config('approval-config.user-model');
		$users = new $userModel();
		
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
		DB::beginTransaction();
		try{
			$approval = Approval::create([
				'title' => $request->title,
				'approvable_type' => $request->model_namespace,
				'view_route_name' => $request->view_route_name,
				'view_route_param' => array_combine($request->view_route_param_key,$request->view_route_param_value),
				'list_data_fields' => $request->list_data_fields,
				'on_create' => $request->approval_type == 1 ? 1 : 0,
				'on_update' => $request->approval_type == 2 ? 1 : 0,
				'on_delete' => $request->approval_type == 3 ? 1 : 0,
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
				        'field_type' => $request->model_namespace_relation_tbody_type[$valueR][$valueRD]
				    ]);
				}
			}

			if($request->approval_title)
			foreach($request->approval_title as $keyL => $valueL){
				$approvalLevel = $approval->levels()->create([
					'title' => $request->approval_title[$keyL],
					'is_flexible' => $request->approval_flex[$keyL],
					'is_form_required' => $request->approval_form[$keyL],
					'level' => $request->approval_level[$keyL],
					'action_type' => $request->approval_action_type[$keyL],
					'action_data' => json_decode($request->approval_action_data[$keyL]),
					'action_frequency' => $request->approval_action_frequency[$keyL],
					'status_fields' => json_decode($request->approval_status_fields[$keyL]),
					'is_data_mapped' => $request->approval_data_mapped[$keyL],
					'notifiable_class' => $request->approval_notifiable_namespace[$keyL],
					'notifiable_params' => json_decode($request->approval_notifiable_params[$keyL]),
					'group_notification' => $request->approval_group_notification[$keyL],
					'next_level_notification' => $request->approval_next_notification[$keyL],
					'is_approve_reason_required' => $request->approval_approve_reason[$keyL],
					'is_reject_reason_required' => $request->approval_reject_reason[$keyL],
				]);

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
						]);

						$formKey = $request->approval_form_key[$keyL][$keyF];
						foreach($request->approval_form_tbody_check[$keyL][$formKey] as $keyFK => $valueFK){
							$approvalLevelForm->form_data()->create([
								'mapped_field_name' => $request->approval_form_tbody_name[$keyL][$formKey][$valueFK],
						        'mapped_field_label' => $request->approval_form_tbody_label[$keyL][$formKey][$valueFK],
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
			return redirect()->route('approvals.index')->with(['msg_data' => 'Approval Submission Error', 'msg_type' => 'danger']);
		}

		return redirect()->route('approvals.index')->with(['msg_data' => 'Approval Added', 'msg_type' => 'success']);
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
		//
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
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Approval  $approval
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Approval $approval)
	{
		//
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
			$data['models'][]=str_replace(app_path(), 'App', $file[0]);
		}

		$Directory = new \RecursiveDirectoryIterator(app_path(config('approval-config.notification-dir')));
		$Iterator = new \RecursiveIteratorIterator($Directory);
		$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
		$data['notifications'] = [];

		foreach($Regex as $file){
			$data['notifications'][]=str_replace(app_path(), 'App', $file[0]);
		}

		$userModel = config('approval-config.user-model');
		$users = new $userModel();
		
		$data['users'] = $users->where(config('approval-config.user-type-column'),config('approval-config.user-type-value'))->get();

		return view("laravel-approval::partials.approval_level",$data);
	}

	public function changeStatus(Request $request, Approval $approval){
		$approval->status = !$approval->status;
		$approval->update();
		Artisan::call('view:clear');
	}
}
