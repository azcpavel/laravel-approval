<?php

namespace Exceptio\ApprovalPermission\Http\Controllers;

use Exceptio\ApprovalPermission\Models\{
	Approval,    
	ApprovalRequest,    
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ApprovalRequestController extends Controller
{
	private $is_dofinal = false;

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, Approval $approval)
	{
		if(!$approval->status)
			abort(404);
		$approval = Approval::with('levels.forms.form_data','levels.users','mappings.fields')->where('id',$approval->id)->first();
		if($request->wantsJson()){
			$approvalRequest = new ApprovalRequest();
			$limit = 10;
			$offset = 0;
			$search = '';
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
				$search = $request->input('search')['value'];                
			}

			if($request->input('where')){
				$where = $request->input('where');
			}
			
			$where[] = ['approval_id',$approval->id];
			$fields = $approval->list_data_fields;
			$relations = [];
			foreach($fields as $keyR => $relation){
				if(strpos($relation,":") !== false){
					$relations[] = 'approvable.'.explode(":",$relation)[0];
					unset($fields[$keyR]);
				}
			}
			$with[] = 'approvable:id'.(count($fields) > 0 ? ','.implode(',', $fields) : '');
			$with = array_merge($with, $relations);

			if($request->input('approval_level') != ''){
				$approval_level = $request->input('approval_level');
				if($approval_level == 0){
					$where[] = ['completed', 0];
				}else if($approval_level == -1){
					$where[] = ['completed', 1];
				}else if($approval_level == -2){
					$where[] = ['completed', 2];
				}else if($approval_level == -3){
					$where[] = ['completed', 3];
				}else
					$where[] = ['approval_state', $request->input('approval_level')];
			}

			$user_selection = null;

			if($approval->properties != ''){
				$user_selection = (object)json_decode($approval->properties);
				if(isset($user_selection->user_selection)){
					$user_selection = $user_selection->user_selection;
				}else{
					$user_selection = null;
				}
			}
			
			$approvalRequest = $approvalRequest->getDataForDataTable($limit, $offset, $search, $where, $with, $join, $orderBy, $request->all(), null, $approval->list_data_fields, [$approval->approvable_type],$user_selection);

			return response()->json($approvalRequest);
		}
		
		return view('laravel-approval::request.index',['approval' => $approval]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function show(ApprovalRequest $approvalRequest)
	{
		if(!$approvalRequest->approval->status)
			abort(404);
		$user_selection = null;
		if($approvalRequest->approval->properties != ''){
			$user_selection = (object)json_decode($approvalRequest->approval->properties);
			if(isset($user_selection->user_selection)){
				$user_selection = $user_selection->user_selection;
			}else{
				$user_selection = null;
			}
		}
		$approvalRequestSql = ApprovalRequest::where('id',$approvalRequest->id)
					->with('approval.levels.forms.form_data',
						'approval.levels.users',
						'approval.mappings.fields',
						'approvers.forms.form_data',
						'mappings.form_data',
						'approvable',
						'approvals');

		if($user_selection){        	
        	$user = auth()->user();
        	foreach($user_selection as $usKey => $usValue){
        		if($usValue->type == 'model'){
        			$approvalRequestSql->hasMorph('approvable', [$approvalRequest->approval->approvable_type], '>=', 1, 'and', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});
        		}else if($usValue->type == 'value'){
        			foreach($usValue->items as $usValueKey => $usValueValue){
        				$approvalRequestSql->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			           	});       				
        			}
        		}
        	}
        }

		$approvalRequest = $approvalRequestSql->first();

		if(!$approvalRequest)
			abort(404);

		return view('laravel-approval::request.show',['approvalRequest' => $approvalRequest, 'user_selection' => $user_selection]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function edit(ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Show the form for submit approval the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function submit(Request $request, ApprovalRequest $approvalRequest)
	{
		if(!$approvalRequest->approval->status)
			abort(404);

		$user_selection = null;
		if($approvalRequest->approval->properties != ''){
			$user_selection = (object)json_decode($approvalRequest->approval->properties);
			if(isset($user_selection->user_selection)){
				$user_selection = $user_selection->user_selection;
			}else{
				$user_selection = null;
			}
		}

		$currentLevel = $approvalRequest->currentLevel(true);
		$approvalRequestSql = ApprovalRequest::where('id',$approvalRequest->id);

		if($user_selection){        	
        	$user = auth()->user();
        	foreach($user_selection as $usKey => $usValue){
        		if($usValue->type == 'model'){
        			$approvalRequestSql->hasMorph('approvable', [$approvalRequest->approval->approvable_type], '>=', 1, 'and', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});
        		}else if($usValue->type == 'value'){
        			foreach($usValue->items as $usValueKey => $usValueValue){
        				$approvalRequestSql->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			           	});       				
        			}

        			$approvalRequestSql->whereExists(function ($query) use($user, $currentLevel, $usValue){
		               $level_column = $usValue->level_column;
		               $query->select(\DB::raw(1))
		                     ->from(config('approval-config.user-table'))
		                     ->where('id',$user->id)
		                     ->whereNotNull($level_column)
		                     ->where($level_column,$currentLevel->level);
		                
		           	});
        		}
        	}
        }

		$userApprover = (($currentLevel) ? in_array(auth()->id(),$currentLevel->approval_users->pluck('user_id')->all()) : false);
		if($approvalRequest->completed == 0 && ($userApprover !== false || $approvalRequestSql->first()) && $request->has('approval_option')){
			try{
				\DB::beginTransaction();

				$message['msg_type'] = 'success';

				$approvalItem = $approvalRequest->approvable;

				$uploadDirsBase = public_path();
				$uploadDirs = explode('/', config('approval-config.upload-dir'));                
				foreach($uploadDirs as $keyUD => $valueUD){
					$uploadDirsBase .= DIRECTORY_SEPARATOR.$valueUD;
					if(!file_exists($uploadDirsBase)){                        
						mkdir($uploadDirsBase);
					}
				}

				if(!file_exists($uploadDirsBase.DIRECTORY_SEPARATOR.'approvals')){                    
					mkdir($uploadDirsBase.DIRECTORY_SEPARATOR.'approvals');
				}
				$uploadDir = config('approval-config.upload-dir').DIRECTORY_SEPARATOR.'approvals';
				$files = [];
				if($request->hasFile('approval_file')){                    
					foreach($request->approval_file as $keyAF => $valueAF){
						$tempName = approvalFileName($valueAF->getClientOriginalName(), 1, $approvalRequest->id);                        
						$filePath = $valueAF->storeAs($uploadDir,$tempName);                        
						$files[] = $filePath;
					}
				}

				if($request->approval_option == 1){                    

					$approvalRequestApprover = $approvalRequest->approvers()->create([
						'approval_id' => $currentLevel->approval_id,
						'user_id' => auth()->id(),
						'next_user_id' => ($request->approval_next_user) ? $request->approval_next_user : null,
						'next_level_user' => $currentLevel->next_level_user,
						'need_attachment' => $currentLevel->need_attachment,
						'title' => $currentLevel->title,
						'is_flexible' => $currentLevel->is_flexible,
						'is_form_required' => $currentLevel->is_form_required,
						'level' => $currentLevel->level,
						'action_type' => $currentLevel->action_type,
						'action_data' => $currentLevel->action_data,
						'action_frequency' => $currentLevel->action_frequency,
						'status_fields' => $currentLevel->status_fields,
						'is_data_mapped' => $currentLevel->is_data_mapped,
						'is_approved' => 1,
						'is_rejected' => 0,
						'is_send_back' => 0,
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
					$message['msg_data'] = 'Your approval has been submitted';
				}else if($request->approval_option == 0){                    

					$approvalRequestApprover = $approvalRequest->approvers()->create([
						'approval_id' => $currentLevel->approval_id,
						'user_id' => auth()->id(),
						'next_user_id' => ($request->approval_next_user) ? $request->approval_next_user : null,
						'next_level_user' => $currentLevel->next_level_user,
						'need_attachment' => $currentLevel->need_attachment,
						'title' => $currentLevel->title,
						'is_flexible' => $currentLevel->is_flexible,
						'is_form_required' => $currentLevel->is_form_required,
						'level' => $currentLevel->level,
						'action_type' => $currentLevel->action_type,
						'action_data' => $currentLevel->action_data,
						'action_frequency' => $currentLevel->action_frequency,
						'status_fields' => $currentLevel->status_fields,
						'is_data_mapped' => $currentLevel->is_data_mapped,
						'is_approved' => 0,
						'is_rejected' => 1,
						'is_send_back' => 0,
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
					$message['msg_data'] = 'Your rejection has been submitted';
				}else if($request->approval_option == 2){                    

					$approvalRequestApprover = $approvalRequest->approvers()->create([
						'approval_id' => $currentLevel->approval_id,
						'user_id' => auth()->id(),
						'next_user_id' => ($request->approval_next_user) ? $request->approval_next_user : null,
						'next_level_user' => $currentLevel->next_level_user,
						'need_attachment' => $currentLevel->need_attachment,
						'title' => $currentLevel->title,
						'is_flexible' => $currentLevel->is_flexible,
						'is_form_required' => $currentLevel->is_form_required,
						'level' => $currentLevel->level,
						'action_type' => $currentLevel->action_type,
						'action_data' => $currentLevel->action_data,
						'action_frequency' => $currentLevel->action_frequency,
						'status_fields' => $currentLevel->status_fields,
						'is_data_mapped' => $currentLevel->is_data_mapped,
						'is_approved' => 0,
						'is_rejected' => 0,
						'is_send_back' => 1,
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
					$message['msg_data'] = 'Your send back has been submitted';
				}

				$finalLevel = $approvalRequest->approval->levels->sortByDesc('level')->first();
				$prevLevel = $approvalRequest->approval->levels->where('level',$currentLevel->level-1)->where('status',1)->first();
				$nextLevel = $approvalRequest->approval->levels->where('level',$currentLevel->level+1)->where('status',1)->first();
				
				$complete = true;                    
				foreach($currentLevel->users as $keyAU => $valueAU){
					$isSubmitted = $approvalRequest->approvers->where('level',$currentLevel->level)->where('user_id',$valueAU->id)->where('status',0)->first();
					if(!$isSubmitted){
						$complete = false;
						break;
					}
				}				

				if($complete){			
					$approveCount = 0;
					$rejectCount = 0;
					foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
						$valueASD->update([
							'status' => 1
						]);

						if($valueASD->is_approved)
							$approveCount++;
						if($valueASD->is_rejected)
							$rejectCount++;
					}

					if($currentLevel->is_flexible == 0){
						if($rejectCount == 0){
							if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
								$actionClassPath = $currentLevel->action_data->before->class;
								$actionClassMethod = $currentLevel->action_data->before->method;
								$actionClass = new $actionClassPath();
								$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
							}

							if($request->approval_option == 1 && $currentLevel->status_fields && property_exists($currentLevel->status_fields, 'approve') && $currentLevel->status_fields->approve){								
								foreach($currentLevel->status_fields->approve as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							if($request->approval_option == 2 && $currentLevel->status_fields && property_exists($currentLevel->status_fields, 'send_back') && $currentLevel->status_fields->send_back){								
								foreach($currentLevel->status_fields->send_back as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}							
							
							if($request->approval_option == 1 && $finalLevel->id == $currentLevel->id){
								$approvalRequest->completed = 1;
							}elseif($request->approval_option == 1 && $nextLevel){
								$approvalRequest->approval_state = $nextLevel->level;
							}
							$approvalRequest->save();
						}elseif($request->approval_option == 0){							
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){							
								foreach($currentLevel->status_fields->reject as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 2;
							$approvalRequest->save();
						}elseif($request->approval_option == 2){							
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'send_back') && $currentLevel->status_fields->send_back){							
								foreach($currentLevel->status_fields->send_back as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 3;
							$approvalRequest->save();
						}
					}else{
						if($approveCount >= $currentLevel->is_flexible){
							if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
								$actionClassPath = $currentLevel->action_data->before->class;
								$actionClassMethod = $currentLevel->action_data->before->method;
								$actionClass = new $actionClassPath();
								$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
							}

							if($request->approval_option == 1 && $currentLevel->status_fields && property_exists($currentLevel->status_fields, 'approve') && $currentLevel->status_fields->approve){
								foreach($currentLevel->status_fields->approve as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							if($request->approval_option == 2 && $currentLevel->status_fields && property_exists($currentLevel->status_fields, 'send_back') && $currentLevel->status_fields->send_back){								
								foreach($currentLevel->status_fields->send_back as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							if($request->approval_option == 1 && $finalLevel->id == $currentLevel->id){
								$approvalRequest->completed = 1;
							}elseif($request->approval_option == 1 && $nextLevel){
								$approvalRequest->approval_state = $nextLevel->level;
							}
							$approvalRequest->save();
						}elseif($request->approval_option == 0){
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){
								foreach($currentLevel->status_fields->reject as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 2;
							$approvalRequest->save();
						}elseif($request->approval_option == 2){							
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'send_back') && $currentLevel->status_fields->send_back){							
								foreach($currentLevel->status_fields->send_back as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 3;
							$approvalRequest->save();
						}
					}					
					
					$approvalRequestApproval = $this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

					if($currentLevel->group_notification && $currentLevel->notifiable_class){
						$notifiableClass = $currentLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
					}

					if($currentLevel->next_level_notification && $approveCount > $rejectCount){
						if($nextLevel && $nextLevel->notifiable_class){
							$notifiableClass = $nextLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$nextLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $nextLevel->notifiable_params->channels, $approvalRequestApproval));
						}						
					}					

					$this->doApprovalFinal($currentLevel, $approvalRequest, $approvalRequestApprover, $approvalItem, $request);

					if($currentLevel->action_type != 0){						
						return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);
					}

				}elseif($currentLevel->is_flexible){
					//Flexible
					$approveCount = 0;
					$rejectCount = 0;
					foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){						

						if($valueASD->is_approved)
							$approveCount++;
						if($valueASD->is_rejected)
							$rejectCount++;
					}
					
					if($approveCount != 0 && $approveCount >= $currentLevel->is_flexible){
						//Approve
						foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
							$valueASD->update([
								'status' => 1
							]);						
						}

						if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
							$actionClassPath = $currentLevel->action_data->before->class;
							$actionClassMethod = $currentLevel->action_data->before->method;
							$actionClass = new $actionClassPath();
							$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
						}

						if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'approve') && $currentLevel->status_fields->approve){
							foreach($currentLevel->status_fields->approve as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
						}

						if($finalLevel->id == $currentLevel->id){
							$approvalRequest->completed = 1;
						}elseif($nextLevel){
							$approvalRequest->approval_state = $nextLevel->level;
						}
						$approvalRequest->save();

						$approvalRequestApproval = $this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

						$this->doApprovalFinal($currentLevel, $approvalRequest, $approvalRequestApprover, $approvalItem, $request);

						if($currentLevel->group_notification && $currentLevel->notifiable_class){
							$notifiableClass = $currentLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
						}

						if($currentLevel->next_level_notification && $approveCount > $rejectCount){
							if($nextLevel && $nextLevel->notifiable_class){
								$notifiableClass = $nextLevel->notifiable_class;
								$userModel = config('approval-config.user-model');
								$users = new $userModel();
								Notification::send($users->whereIn('id',$nextLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $nextLevel->notifiable_params->channels, $approvalRequestApproval));
							}						
						}
					}elseif($rejectCount > ($currentLevel->approval_users->count() - $currentLevel->is_flexible)){
						//Reject
						foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
							$valueASD->update([
								'status' => 1
							]);						
						}

						if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
							$actionClassPath = $currentLevel->action_data->before->class;
							$actionClassMethod = $currentLevel->action_data->before->method;
							$actionClass = new $actionClassPath();
							$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
						}

						if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){
							foreach($currentLevel->status_fields->reject as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
						}

						$approvalRequest->completed = 2;
						$approvalRequest->save();

						$approvalRequestApproval = $this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

						if($currentLevel->group_notification && $currentLevel->notifiable_class){
							$notifiableClass = $currentLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
						}
					}elseif($request->approval_option == 2){
						//Send Back
						foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
							$valueASD->update([
								'status' => 1
							]);						
						}

						if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
							$actionClassPath = $currentLevel->action_data->before->class;
							$actionClassMethod = $currentLevel->action_data->before->method;
							$actionClass = new $actionClassPath();
							$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
						}

						if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'send_back') && $currentLevel->status_fields->send_back){
							foreach($currentLevel->status_fields->send_back as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
						}

						$approvalRequest->completed = 3;
						$approvalRequest->save();

						$approvalRequestApproval = $this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

						if($currentLevel->group_notification && $currentLevel->notifiable_class){
							$notifiableClass = $currentLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
						}
					}
					else{
						$this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

						if($currentLevel->group_notification && $currentLevel->notifiable_class){
							$notifiableClass = $currentLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
						}
					}					

					if($currentLevel->action_type != 0)
						return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);

				}elseif($request->approval_option == 0 && $currentLevel->is_flexible == 0){
					//Reject and Not Flexible
					foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
						$valueASD->update([
							'status' => 1
						]);						
					}

					if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){
						foreach($currentLevel->status_fields->reject as $keyA => $valueA){
							$approvalItem->$keyA = $valueA;
						}
						$approvalItem->save();
					}

					$approvalRequest->completed = 2;
					$approvalRequest->save();

					if($currentLevel->group_notification && $currentLevel->notifiable_class){
						$notifiableClass = $currentLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
					}

					$this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

					if($currentLevel->action_type != 0)
						return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);

				}else{
					if($currentLevel->group_notification && $currentLevel->notifiable_class){
						$notifiableClass = $currentLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
					}
				}

				if($request->approval_option == 1 && !$this->is_dofinal)
					$this->doApprovalFinal($currentLevel, $approvalRequest, $approvalRequestApprover, $approvalItem, $request);

				if($currentLevel->action_type != 0 && $currentLevel->action_frequency == 1)
					return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);
				
				\DB::commit();
			}catch(\Exception $e){
				if(env('APP_DEBUG'))
					dd($e);
				\DB::rollback();
				$message['msg_type'] = 'danger';
				$message['msg_data'] = 'Something went wrong, please try again';
			}            
			return redirect()->back()->with($message);
		}elseif(!$userApprover){
			$message['msg_type'] = 'danger';
			$message['msg_data'] = 'Application state is not valid!';
			return redirect()->back()->with($message);
		}
		else{
			$message['msg_type'] = 'danger';
			$message['msg_data'] = 'Application state is not valid!';
			return redirect()->back()->with($message);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function swapLevel(Request $request, ApprovalRequest $approvalRequest)
	{
		if(!$approvalRequest->approval->status)
			abort(404);
		$level = $approvalRequest->approval->levels->where('level',$request->do_swap)->first();
		$currentLevel = $approvalRequest->currentLevel(true);
		$userApprover = (($currentLevel) ? in_array(auth()->id(),$currentLevel->approval_users->pluck('user_id')->all()) : false);
		
		$user_selection = null;
		if($approvalRequest->approval->properties != ''){
			$user_selection = (object)json_decode($approvalRequest->approval->properties);
			if(isset($user_selection->user_selection)){
				$user_selection = $user_selection->user_selection;
			}else{
				$user_selection = null;
			}
		}
		$approvalRequestSql = ApprovalRequest::where('id',$approvalRequest->id);

		if($user_selection){        	
        	$user = auth()->user();
        	foreach($user_selection as $usKey => $usValue){
        		if($usValue->type == 'model'){
        			$approvalRequestSql->hasMorph('approvable', [$approvalRequest->approval->approvable_type], '>=', 1, 'and', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});
        		}else if($usValue->type == 'value'){
        			foreach($usValue->items as $usValueKey => $usValueValue){
        				$approvalRequestSql->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			           	});       				
        			}

        			$approvalRequestSql->whereExists(function ($query) use($user, $currentLevel, $usValue){
		               $level_column = $usValue->level_column;
		               $query->select(\DB::raw(1))
		                     ->from(config('approval-config.user-table'))
		                     ->where('id',$user->id)
		                     ->whereNotNull($level_column)
		                     ->where($level_column,$currentLevel->level);
		                
		           	});
        		}
        	}
        }

		if($level && ($userApprover !== false || $approvalRequestSql->first()) && $request->do_swap != $currentLevel->level){
			
			$message['msg_type'] = 'success';
			$message['msg_data'] = 'Approval level changed to '.$level->title;

			$approvalRequest->approvers()->update(['status'=>1]);
			
			$approvalRequestApproval = $approvalRequest->approvals()->create([
				'approval_id' => $approvalRequest->approval_id,
				'user_id' => auth()->id(),
				'prev_level' => $currentLevel->level,
				'prev_level_title' => $currentLevel->title,
				'next_level' => $level->level,
				'next_level_title' => $level->title,
				'is_swaped' => 1,
				'reason' => $request->swap_reason,
			]);

	    	if($currentLevel->group_notification && $currentLevel->notifiable_class){
				$notifiableClass = $currentLevel->notifiable_class;
				$userModel = config('approval-config.user-model');
				$users = new $userModel();
				Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalRequest->approvable, null, $currentLevel->notifiable_params->channels, $approvalRequestApproval));
			}

			if($level->group_notification && $level->notifiable_class){
				$notifiableClass = $level->notifiable_class;
				$userModel = config('approval-config.user-model');
				$users = new $userModel();
				Notification::send($users->whereIn('id',$level->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalRequest->approvable, null, $level->notifiable_params->channels, $approvalRequestApproval));
			}

			$approvalRequest->approval_state = $level->level;
			$approvalRequest->save();
		}else{
			$message['msg_type'] = 'danger';
			$message['msg_data'] = 'Approval level not valid!';
		}
		return redirect()->back()->with($message);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function commentLevel(Request $request, ApprovalRequest $approvalRequest)
	{
		if(!$approvalRequest->approval->status)
			abort(404);		
		$currentLevel = $approvalRequest->currentLevel(true);
		$userApprover = (($currentLevel) ? in_array(auth()->id(),$currentLevel->approval_users->pluck('user_id')->all()) : false);

		$user_selection = null;
		if($approvalRequest->approval->properties != ''){
			$user_selection = (object)json_decode($approvalRequest->approval->properties);
			if(isset($user_selection->user_selection)){
				$user_selection = $user_selection->user_selection;
			}else{
				$user_selection = null;
			}
		}
		$approvalRequestSql = ApprovalRequest::where('id',$approvalRequest->id);

		if($user_selection){        	
        	$user = auth()->user();
        	foreach($user_selection as $usKey => $usValue){
        		if($usValue->type == 'model'){
        			$approvalRequestSql->hasMorph('approvable', [$approvalRequest->approval->approvable_type], '>=', 1, 'and', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});
        		}else if($usValue->type == 'value'){
        			foreach($usValue->items as $usValueKey => $usValueValue){
        				$approvalRequestSql->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			           	});       				
        			}

        			$approvalRequestSql->whereExists(function ($query) use($user, $currentLevel, $usValue){
		               $level_column = $usValue->level_column;
		               $query->select(\DB::raw(1))
		                     ->from(config('approval-config.user-table'))
		                     ->where('id',$user->id)
		                     ->whereNotNull($level_column)
		                     ->where($level_column,$currentLevel->level);
		                
		           	});
        		}
        	}
        }

		if($currentLevel->level && ($userApprover !== false || $approvalRequestSql->first())){
			
			$message['msg_type'] = 'success';
			$message['msg_data'] = 'Approval comments submitted for '.$currentLevel->title;
			
			$approvalRequestApproval = $approvalRequest->approvals()->create([
				'approval_id' => $approvalRequest->approval_id,
				'user_id' => auth()->id(),
				'prev_level' => $currentLevel->level,
				'prev_level_title' => $currentLevel->title,
				'next_level' => $currentLevel->level,
				'next_level_title' => $currentLevel->title,
				'is_commented' => 1,
				'reason' => $request->level_comment,
			]);

	    	if($currentLevel->group_notification && $currentLevel->notifiable_class){
				$notifiableClass = $currentLevel->notifiable_class;
				$userModel = config('approval-config.user-model');
				$users = new $userModel();
				Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalRequest->approvable, null, $currentLevel->notifiable_params->channels, $approvalRequestApproval));
			}

		}else{
			$message['msg_type'] = 'danger';
			$message['msg_data'] = 'Approval level not valid!';
		}
		return redirect()->back()->with($message);
	}

	private function doApprovalFinal($currentLevel, $approvalRequest, $approvalRequestApprover, $approvalItem, $request){
		if(!$this->is_dofinal)
			$this->is_dofinal = true;
		else
			return false;
		if($currentLevel->is_form_required && $request->approval_option == 1){
			foreach($currentLevel->forms as $keyAFR => $valueAFR){									
				if($valueAFR->approvable_type == $approvalRequest->approval->approvable_type){					
					$approvalRequestApproverForm = $approvalRequestApprover->forms()->create([
						'approvable_id' => $approvalItem->id,
				        'approvable_type' => $valueAFR->approvable_type,
				        'title' => $valueAFR->title
					]);
					
					foreach($valueAFR->form_data as $keyAFRF => $valueAFRF){
						$fieldItem = $valueAFR->id.'_'.$valueAFRF->mapped_field_name;
						$fieldRelation = json_decode($valueAFRF->mapped_field_relation);
						if($request->has($fieldItem)){
							if(gettype($request->$fieldItem) == 'array'){
								$keyAFRFFileItemValue = implode(',', $request->$fieldItem);
							}else{
								$keyAFRFFileItemValue = $request->$fieldItem;
							}													
							$approvalRequestApproverForm->form_data()->create([
								'mapped_field_name' => $valueAFRF->mapped_field_name,
								'mapped_field_label' => $valueAFRF->mapped_field_label,
								'mapped_field_type' => $valueAFRF->mapped_field_type,
								'mapped_field_relation' => $valueAFRF->mapped_field_relation,
								'mapped_field_relation_pk' => $valueAFRF->mapped_field_relation_pk,
								'mapped_field_relation_show' => $valueAFRF->mapped_field_relation_show,
								'mapped_field_value' => $keyAFRFFileItemValue,
							]);

							if($valueAFRF->mapped_field_type != 'select'){
								$approvalItem->update([
									$valueAFRF->mapped_field_name => $request->$fieldItem
								]);
							}else{
								if(is_object($fieldRelation) && property_exists($fieldRelation, 'type') && property_exists($fieldRelation, 'values')){
									if($fieldRelation->type == "single"){
										$approvalItem->update([
											$valueAFRF->mapped_field_name => $request->$fieldItem
										]);
									}elseif($fieldRelation->type == "multiple" && property_exists($fieldRelation, 'relation') && $valueAFRF->mapped_field_relation_pk != '' && $valueAFRF->mapped_field_relation_show != ''){
										// $approvalItem->$fieldRelation()->delete();
										// foreach($request->$fieldItem as $keyMRC => $valueMRC){
										// 	$approvalItem->$fieldRelation()->create([

										// 	]);
										// }
									}
								}elseif($valueAFRF->mapped_field_relation != '' && $valueAFRF->mapped_field_relation_pk != '' && $valueAFRF->mapped_field_relation_show != ''){
									$itemModel = $valueAFR->approvable_type;
									$itemField = $valueAFRF->mapped_field_name;
									$itemRelation = $valueAFRF->mapped_field_relation;
									$itemRelationPK = $valueAFRF->mapped_field_relation_pk;
									$itemRelationShow = $valueAFRF->mapped_field_relation_show;
									$itemObject = new $itemModel();
									$itemRelationObject = $itemObject->$itemRelation();
									$itemRelationObjectType = strtolower(basename(get_class($itemRelationObject)));
									$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
									if(!$input_multiple){
										$approvalItem->update([
											$valueAFRF->mapped_field_name => $request->$fieldItem
										]);
									}else{
										if($itemRelationObjectType == 'belongstomany'){

										}
										// $approvalItem->$itemRelation()->delete();
										// foreach($request->$fieldItem as $keyMRC => $valueMRC){
										// 	$approvalItem->$itemRelation()->create([
										// 		$valueAFRF->mapped_field_name => $valueMRC
										// 	]);
										// }
									}
								}
							}
						}
					}
				}else{
					$approvable_typeR = $valueAFR->approvable_type;
					$approvalItemR = $approvalRequest->approvable->{$valueAFR->relation};
					if(!$approvalItemR){
						$approvalItemR = new $approvable_typeR();
						$approvalItemR->id = $valueAFR->approvable_id;
					}

					$approvalRequestApproverForm = $approvalRequestApprover->forms()->create([
						'approvable_id' => $approvalItemR->id,
				        'approvable_type' => $valueAFR->approvable_type,
				        'title' => $valueAFR->title
					]);				
					
					foreach($valueAFR->form_data as $keyAFRF => $valueAFRF){
						$fieldItem = $valueAFR->id.'_'.$valueAFRF->mapped_field_name;
						$fieldRelation = json_decode($valueAFRF->mapped_field_relation);
						if($request->has($fieldItem)){
							if(gettype($request->$fieldItem) == 'array'){
								$keyAFRFFileItemValue = implode(',', $request->$fieldItem);
							}else{
								$keyAFRFFileItemValue = $request->$fieldItem;
							}													
							$approvalRequestApproverForm->form_data()->create([
								'mapped_field_name' => $valueAFRF->mapped_field_name,
								'mapped_field_label' => $valueAFRF->mapped_field_label,
								'mapped_field_type' => $valueAFRF->mapped_field_type,
								'mapped_field_relation' => $valueAFRF->mapped_field_relation,
								'mapped_field_relation_pk' => $valueAFRF->mapped_field_relation_pk,
								'mapped_field_relation_show' => $valueAFRF->mapped_field_relation_show,
								'mapped_field_value' => $keyAFRFFileItemValue,
							]);

							if($valueAFRF->mapped_field_type != 'select'){
								$approvalItemR->{$valueAFRF->mapped_field_name} = $request->$fieldItem;
							}else{
								if(is_object($fieldRelation) && property_exists($fieldRelation, 'type') && property_exists($fieldRelation, 'values')){
									if($fieldRelation->type == "single"){
										$approvalItemR->{$valueAFRF->mapped_field_name} = $request->$fieldItem;
									}elseif($fieldRelation->type == "multiple" && property_exists($fieldRelation, 'relation') && $valueAFRF->mapped_field_relation_pk != '' && $valueAFRF->mapped_field_relation_show != ''){
										// $approvalItem->$fieldRelation()->delete();
										// foreach($request->$fieldItem as $keyMRC => $valueMRC){
										// 	$approvalItem->$fieldRelation()->create([

										// 	]);
										// }
									}
								}elseif($valueAFRF->mapped_field_relation != '' && $valueAFRF->mapped_field_relation_pk != '' && $valueAFRF->mapped_field_relation_show != ''){
									$itemModel = $valueAFR->approvable_type;
									$itemField = $valueAFRF->mapped_field_name;
									$itemRelation = $valueAFRF->mapped_field_relation;
									$itemRelationPK = $valueAFRF->mapped_field_relation_pk;
									$itemRelationShow = $valueAFRF->mapped_field_relation_show;
									$itemObject = new $itemModel();
									$itemRelationObject = $itemObject->$itemRelation();
									$itemRelationObjectType = strtolower(basename(get_class($itemRelationObject)));
									$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
									if(!$input_multiple){
										$approvalItemR->{$valueAFRF->mapped_field_name} = $request->$fieldItem;
									}else{
										if($itemRelationObjectType == 'belongstomany'){
											
										}
										// $approvalItem->$itemRelation()->delete();
										// foreach($request->$fieldItem as $keyMRC => $valueMRC){
										// 	$approvalItem->$itemRelation()->create([
										// 		$valueAFRF->mapped_field_name => $valueMRC
										// 	]);
										// }
									}
								}
							}
						}
					}

					$approvalItemR->save();
				}
			}			
		}

		if($approvalRequest->completed == 1 && $request->approval_option == 1 && $approvalRequest->approval->on_update){
			if($approvalRequest->mappings){
				foreach($approvalRequest->mappings as $keyM => $valueM){
					if($valueM->relation != "")
						$currentItem = $approvalRequest->approvable->{$valueM->relation};
					else
						$currentItem = $valueM->approvable;

					if(!$currentItem){
						$itemModelPath = $valueM->approvable_type;
						$currentItem = new $itemModelPath();
						if($valueM->approvable_id && $valueM->relation == ""){
							$currentItem->id = $valueM->approvable_id;
						}						
					}
					
					foreach($valueM->form_data as $keyMF => $valueMF){
						$fieldName = $valueMF->field_name;
						$currentItem->$fieldName = $valueMF->field_data;
					}

					$currentItem->save();
				}
			}
		}

		if($approvalRequest->completed == 1 && $request->approval_option == 1 && $approvalRequest->approval->on_delete && $approvalRequest->approval->do_delete){
			$approvalRequest->approvable->delete();
		}
	}

	private function doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message){
		\DB::commit();

		if($currentLevel->action_type == 1){
			if($request->approval_option == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'approve') && $currentLevel->action_data->approve){
				$actionClassPath = $currentLevel->action_data->approve->class;
				$actionClassMethod = $currentLevel->action_data->approve->method;
				$actionClass = new $actionClassPath();
				return $actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
			}elseif($request->approval_option == 0 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'reject') && $currentLevel->action_data->reject){
				$actionClassPath = $currentLevel->action_data->reject->class;
				$actionClassMethod = $currentLevel->action_data->reject->method;
				$actionClass = new $actionClassPath();
				return $actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
			}elseif($request->approval_option == 2 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'send_back') && $currentLevel->action_data->send_back){
				$actionClassPath = $currentLevel->action_data->send_back->class;
				$actionClassMethod = $currentLevel->action_data->send_back->method;
				$actionClass = new $actionClassPath();
				return $actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover,$request->all());
			}

			return redirect()->back()->with($message);
		}
		elseif($currentLevel->action_type == 2){
			if($request->approval_option == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'approve') && $currentLevel->action_data->approve){
				$routeParams = [];
				foreach($currentLevel->action_data->approve->param as $keyRP => $valueRP){
					$routeParams[$keyRP] = $approvalItem->$valueRP;
				}
				$routeParams['approver_id'] = $approvalRequestApprover->id;
				return redirect()->route($currentLevel->action_data->approve->route,$routeParams);
			}elseif($request->approval_option == 0 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'reject') && $currentLevel->action_data->reject){
				$routeParams = [];
				foreach($currentLevel->action_data->reject->param as $keyRP => $valueRP){
					$routeParams[$keyRP] = $approvalItem->$valueRP;
				}
				$routeParams['approver_id'] = $approvalRequestApprover->id;
				return redirect()->route($currentLevel->action_data->reject->route,$routeParams);
			}elseif($request->approval_option == 2 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'send_back') && $currentLevel->action_data->send_back){
				$routeParams = [];
				foreach($currentLevel->action_data->send_back->param as $keyRP => $valueRP){
					$routeParams[$keyRP] = $approvalItem->$valueRP;
				}
				$routeParams['approver_id'] = $approvalRequestApprover->id;
				return redirect()->route($currentLevel->action_data->send_back->route,$routeParams);
			}

			return redirect()->back()->with($message);
		}		
	}

	private function doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel){
		$approvalRequestApproval = $approvalRequest->approvals()->create([
			'approval_id' => $approvalRequestApprover->approval_id,
			'approval_request_id' => $approvalRequestApprover->approval_request_id,
			'user_id' => $approvalRequestApprover->user_id,
			'prev_level' => ($prevLevel) ? $prevLevel->level : null,
			'prev_level_title' => ($prevLevel) ? $prevLevel->title : null,
			'next_level' => $approvalRequestApprover->level,
			'next_level_title' => $approvalRequestApprover->title,
			'is_approved' => $approvalRequestApprover->is_approved,
			'is_rejected' => $approvalRequestApprover->is_rejected,
			'is_send_back' => $approvalRequestApprover->is_send_back,
			'reason' => $approvalRequestApprover->reason,
		]);

		return $approvalRequestApproval;
	}	
}
