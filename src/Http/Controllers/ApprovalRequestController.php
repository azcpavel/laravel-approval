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
			
			$with[] = 'approvable:id,'.implode(',', $fields);
			$approvalRequest = $approvalRequest->getDataForDataTable($limit, $offset, $search, $where, $with, $join, $orderBy, $request->all(), null, $approval->list_data_fields);

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
		$approvalRequest = ApprovalRequest::where('id',$approvalRequest->id)
					->with('approval.levels.forms.form_data',
						'approval.levels.users',
						'approval.mappings.fields',
						'approvers.forms.form_data',
						'mappings.form_data',
						'approvable',
						'approvals')->first();
		return view('laravel-approval::request.show',['approvalRequest' => $approvalRequest]);
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
		$currentLevel = $approvalRequest->currentLevel(true);
		$userApprover = (($currentLevel) ? in_array(auth()->id(),$currentLevel->approval_users->pluck('user_id')->all()) : false);
		if($approvalRequest->completed == 0 && $userApprover !== false && $request->has('approval_option')){
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
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
					$message['msg_data'] = 'Your approval has been submitted';
				}else if($request->approval_option == 0){                    

					$approvalRequestApprover = $approvalRequest->approvers()->create([
						'approval_id' => $currentLevel->approval_id,
						'user_id' => auth()->id(),
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
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
					$message['msg_data'] = 'Your rejection has been submitted';
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

				if($currentLevel->group_notification){
					$notifiableClass = $currentLevel->notifiable_class;
					$userModel = config('approval-config.user-model');
					$users = new $userModel();
					Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $currentLevel->notifiable_params->channels));
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
								$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover);
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
						}else{							
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){							
								foreach($currentLevel->status_fields->reject as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 2;
							$approvalRequest->save();
						}
					}else{
						if($approveCount >= $currentLevel->is_flexible){
							if($currentLevel->action_type == 1 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'before') && $currentLevel->action_data->before){
								$actionClassPath = $currentLevel->action_data->before->class;
								$actionClassMethod = $currentLevel->action_data->before->method;
								$actionClass = new $actionClassPath();
								$actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover);
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
						}else{
							if($currentLevel->status_fields && property_exists($currentLevel->status_fields, 'reject') && $currentLevel->status_fields->reject){
								foreach($currentLevel->status_fields->reject as $keyA => $valueA){
									$approvalItem->$keyA = $valueA;
								}
								$approvalItem->save();
							}

							$approvalRequest->completed = 2;
							$approvalRequest->save();
						}
					}					
					
					if($currentLevel->next_level_notification && $approveCount > $rejectCount){
						if($nextLevel && $nextLevel->notifiable_class){
							$notifiableClass = $nextLevel->notifiable_class;
							$userModel = config('approval-config.user-model');
							$users = new $userModel();
							Notification::send($users->whereIn('id',$nextLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, $approvalRequestApprover, $nextLevel->notifiable_params->channels));
						}						
					}

					$this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

					$this->doApprovalFinal($currentLevel, $approvalRequest, $approvalRequestApprover, $approvalItem, $request);

					if($currentLevel->action_type != 0){						
						return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);
					}

				}elseif($request->approval_option == 0 && $currentLevel->is_flexible == 0){
					foreach($approvalRequest->approvers->where('level',$currentLevel->level)->where('status',0)->all() as $keyASD => $valueASD){
						$valueASD->update([
							'status' => 1
						]);						
					}

					foreach($currentLevel->status_fields->reject as $keyA => $valueA){
						$approvalItem->$keyA = $valueA;
					}
					$approvalItem->save();

					$approvalRequest->completed = 2;
					$approvalRequest->save();

					$this->doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel);

					if($currentLevel->action_type != 0)
						return $this->doApprovalAction($currentLevel, $approvalItem, $approvalRequestApprover, $request, $message);

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
		if($level){
			$currentLevel = $approvalRequest->currentLevel(true);
			$message['msg_type'] = 'success';
			$message['msg_data'] = 'Approval level changed to '.$level->title;
			
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
				Notification::send($users->whereIn('id',$currentLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $currentLevel->notifiable_params->channels, $approvalRequestApproval));
			}

			if($level->group_notification && $level->notifiable_class){
				$notifiableClass = $level->notifiable_class;
				$userModel = config('approval-config.user-model');
				$users = new $userModel();
				Notification::send($users->whereIn('id',$level->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $level->notifiable_params->channels, $approvalRequestApproval));
			}

			$approvalRequest->approval_state = $level->level;
			$approvalRequest->save();
		}else{
			$message['msg_type'] = 'denger';
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
							$approvalRequestApproverForm->form_data()->create([
								'mapped_field_name' => $valueAFRF->mapped_field_name,
								'mapped_field_label' => $valueAFRF->mapped_field_label,
								'mapped_field_type' => $valueAFRF->mapped_field_type,
								'mapped_field_relation' => $valueAFRF->mapped_field_relation,
								'mapped_field_relation_pk' => $valueAFRF->mapped_field_relation_pk,
								'mapped_field_relation_show' => $valueAFRF->mapped_field_relation_show,
								'mapped_field_value' => $request->$fieldItem,
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
									$itemRelationObject = $itemModel->$itemRelation();
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
					$approvalRequestApproverForm = $approvalRequestApprover->forms()->create([
						'approvable_id' => $valueAFR->approvable_id,
				        'approvable_type' => $valueAFR->approvable_type,
				        'title' => $valueAFR->title
					]);

					$approvable_typeR = $valueAFR->approvable_type;
					$approvalItemR = $approvable_typeR()::where('id',$valueAFR->approvable_id)->first();
					if(!$approvalItemR){
						$approvalItemR = new $approvable_typeR();
						$approvalItemR->id = $valueAFR->approvable_id;
					}
					
					foreach($valueAFR->form_data as $keyAFRF => $valueAFRF){
						$fieldItem = $valueAFR->id.'_'.$valueAFRF->mapped_field_name;
						$fieldRelation = json_decode($valueAFRF->mapped_field_relation);
						if($request->has($fieldItem)){													
							$approvalRequestApproverForm->form_data()->create([
								'mapped_field_name' => $valueAFRF->mapped_field_name,
								'mapped_field_label' => $valueAFRF->mapped_field_label,
								'mapped_field_type' => $valueAFRF->mapped_field_type,
								'mapped_field_relation' => $valueAFRF->mapped_field_relation,
								'mapped_field_relation_pk' => $valueAFRF->mapped_field_relation_pk,
								'mapped_field_relation_show' => $valueAFRF->mapped_field_relation_show,
								'mapped_field_value' => $request->$fieldItem,
							]);

							if($valueAFRF->mapped_field_type != 'select'){
								$approvalItemR->$fieldItem = $request->$fieldItem;
							}else{
								if(is_object($fieldRelation) && property_exists($fieldRelation, 'type') && property_exists($fieldRelation, 'values')){
									if($fieldRelation->type == "single"){
										$approvalItemR->$fieldItem = $request->$fieldItem;
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
									$itemRelationObject = $itemModel->$itemRelation();
									$itemRelationObjectType = strtolower(basename(get_class($itemRelationObject)));
									$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
									if(!$input_multiple){
										$approvalItemR->$fieldItem = $request->$fieldItem;
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
					$currentItem = $valueM->approvable;
					if(!$currentItem){
						$itemModelPath = $valueM->approvable_type;
						$currentItem = new $itemModelPath();
						if($valueM->approvable_id){
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
				return $actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover);
			}elseif($request->approval_option == 0 && $currentLevel->action_data && property_exists($currentLevel->action_data, 'reject') && $currentLevel->action_data->reject){
				$actionClassPath = $currentLevel->action_data->reject->class;
				$actionClassMethod = $currentLevel->action_data->reject->method;
				$actionClass = new $actionClassPath();
				return $actionClass->$actionClassMethod($approvalItem, $approvalRequestApprover);
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
			}

			return redirect()->back()->with($message);
		}		
	}

	private function doApprovalLog($approvalRequest, $approvalRequestApprover, $prevLevel){
		$approvalRequest->approvals()->create([
			'approval_id' => $approvalRequestApprover->approval_id,
			'approval_request_id' => $approvalRequestApprover->approval_request_id,
			'user_id' => $approvalRequestApprover->user_id,
			'prev_level' => ($prevLevel) ? $prevLevel->level : null,
			'prev_level_title' => ($prevLevel) ? $prevLevel->title : null,
			'next_level' => $approvalRequestApprover->level,
			'next_level_title' => $approvalRequestApprover->title,
			'is_approved' => $approvalRequestApprover->is_approved,
			'is_rejected' => $approvalRequestApprover->is_rejected,
			'reason' => $approvalRequestApprover->reason,
		]);
	}	
}
