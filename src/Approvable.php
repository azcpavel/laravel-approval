<?php 
namespace Exceptio\ApprovalPermission;

use Exceptio\ApprovalPermission\Models\{
	Approval,
	ApprovalLevel,
	ApprovalLevelForm,
	ApprovalLevelFormData,
	ApprovalLevelUser,
	ApprovalMapping,
	ApprovalMappingField,
	ApprovalRequest,
	ApprovalRequestApproval,
	ApprovalRequestApprover,
	ApprovalRequestApproverForm,
	ApprovalRequestApproverFormData,
	ApprovalRequestMappingField,
	ApprovalRequestMappingFieldData
};

use Illuminate\Support\Facades\Notification;

trait Approvable 
{
    public function notifyApprovalCreate($approvalItem, $approvalId = null, $resubmitUserId = null, $resubmitRemarks = null){
    	$approvalble = get_class($approvalItem);
    	if($approvalId){
    		$approval = Approval::where('id',$approvalId)->where('on_create',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}else{
    		$approval = Approval::where('approvable_type',$approvalble)->where('on_create',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}
    	
    	
    	try{
    		if($approval){
    			$old_request = $approval->requests->where('approvable_id',$approvalItem->id)->whereBetween('completed',[0,2])->first();    			
    			if($old_request && $old_request->completed == 0)
    				return -2;

    			if($old_request && $old_request->completed == 2){
    				$old_request->completed = 0;
    				$old_request->save();

    				$currentLevel = $old_request->currentLevel(true);
    				if($currentLevel && $currentLevel->group_notification && $currentLevel->notifiable_class){
    					$notifiableClass = $currentLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$currentLevel->approval_users->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($old_request, $approvalItem, null, $currentLevel->notifiable_params->channels));
    				}

    				if($resubmitUserId){
    					ApprovalRequestApproval::create([
	    					'approval_id' => $approval->id,
							'approval_request_id' => $old_request->id,
							'user_id' => $resubmitUserId,
							'prev_level' => '',
							'prev_level_title' => '',
							'next_level' => $currentLevel->level,
							'next_level_title' => $currentLevel->title,
							'is_approved' => 0,
							'is_rejected' => 0,
							'is_swaped' => 0,
							'is_resubmitted' => 1,
							'reason' => $resubmitRemarks,
	    				]);
    				}    				

    				return $old_request;
    			}else{
    				$approvalRequest = $approval->requests()->create([
			    		'approvable_type' => $approvalble,
			    		'approvable_id' => $approvalItem->id,
						'user_id' => auth()->user()->id,
			    	]);

			    	$firstLevel = $approval->levels->sortBy('level')->first();
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
					}
			    	return $approvalRequest;
    			}    			
    		}else{
    			return -1;
    		}
    	}catch(\Exception $e){
    		if(env('APP_DEBUG'))
    			dd($e,$approvalble,$approval);
    		return false;
    	}    	
    }

    public function notifyApprovalUpdate($approvalItem, $approvalMapping, $slug, $approvalId = null, $resubmitUserId = null, $resubmitRemarks = null){
    	$approvalble = get_class($approvalItem);
    	
    	if($approvalId){
    		$approval = Approval::where('id',$approvalId)->where('on_update',1)->where('status',1)->where('slug',$slug)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}
    	else{
    		$approval = Approval::where('approvable_type',$approvalble)->where('on_update',1)->where('status',1)->where('slug',$slug)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}
    	
    	try{
    		if($approval){
    			$old_request = $approval->requests->where('approvable_id',$approvalItem->id)->where('completed',0)->first();
    			if($old_request)
    				return -2;
				else{
    				\DB::beginTransaction();

    				$approvalRequest = $approval->requests()->create([
			    		'approvable_type' => $approvalble,
			    		'approvable_id' => $approvalItem->id,
						'user_id' => auth()->user()->id,
			    	]);

    				foreach($approvalMapping as $keyRM => $valueRM){
    					$mapping = $approval->mappings->where('approvable_type',get_class($valueRM))->first();
    					if($mapping){
    						$approvalRequestMap = $approvalRequest->mappings()->create([
    							'title' => $mapping->title,
								'approvable_id' => $valueRM->id,
								'approvable_type' => $mapping->approvable_type,
								'relation' => $mapping->relation,
				    		]);

				    		foreach($mapping->fields as $keyRMF => $valueRMF){
				    			$field = $valueRMF->field_name;
				    			$approvalRequestMap->form_data()->create([
				    				'field_name' => $valueRMF->field_name,
									'field_label' => $valueRMF->field_label,
									'field_relation' => $valueRMF->field_relation,
									'field_relation_pk' => $valueRMF->field_relation_pk,
									'field_relation_show' => $valueRMF->field_relation_show,
									'field_type' => $valueRMF->field_type,
									'field_data' => $valueRM->$field
				    			]);
				    		}
    					}    					
    				}	    	

			    	\DB::commit();

			    	$firstLevel = $approval->levels->sortBy('level')->first();			    	
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class){						
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
					}
			    	return $approvalRequest;
    			}    			
    		}else{
    			return -1;
    		}
    	}catch(\Exception $e){
    		if(env('APP_DEBUG'))
    			dd($e,$approvalble,$approval);
    		return false;
    	}   	
    }

    public function notifyApprovalDelete($approvalItem, $approvalId = null){
    	$approvalble = get_class($approvalItem);
    	
    	if($approvalId){
    		$approval = Approval::where('id',$approvalId)->where('on_delete',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}
    	else{
    		$approval = Approval::where('approvable_type',$approvalble)->where('on_delete',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	}
    	
    	try{
    		if($approval){
    			$old_request = $approval->requests->where('approvable_id',$approvalItem->id)->where('completed',0)->first();
    			if($old_request){
    				return -2;    			
    			}else{
    				$approvalRequest = $approval->requests()->create([
			    		'approvable_type' => $approvalble,
			    		'approvable_id' => $approvalItem->id,
						'user_id' => auth()->user()->id,
			    	]);

			    	$firstLevel = $approval->levels->sortBy('level')->first();
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
					}
			    	return $approvalRequest;
    			}    			
    		}else{
    			return -1;
    		}
    	}catch(\Exception $e){
    		if(env('APP_DEBUG'))
    			dd($e,$approvalble,$approval);
    		return false;
    	}    	
    }

    public function addApprovalData($approvalRequest, $approval_state, $approval_action, $user_id, $approval_next_user = null, $approval_reason = null, $files = null){
    	$currentLevel = ApprovalLevel::where('approval_id',$approvalRequest->approval_id)->where('level',$approval_state)->first();
    	$approvalRequestApprover = $approvalRequest->approvers()->create([
			'approval_id' => $currentLevel->approval_id,
			'user_id' => $user_id,
			'next_user_id' => $approval_next_user,
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
			'is_approved' => (($approval_action === 1) ? 1 : 0),
			'is_rejected' => (($approval_action === 0) ? 1 : 0),
			'is_send_back' => (($approval_action === 2) ? 1 : 0),
			'reason' => $approval_reason,
			'reason_file' => $files,
		]);

		return $approvalRequestApprover;
    }

    public function addApprovalLogData($approvalRequest, $approval_action, $user_id, $approval_reason = null){
    	$currentLevel = $approvalRequest->currentLevel(true);
    	$approvalRequestApproval = ApprovalRequestApproval::create([
			'approval_id' => $approvalRequest->approval_id,
			'approval_request_id' => $approvalRequest->id,
			'user_id' => $user_id,
			'prev_level' => '',
			'prev_level_title' => '',
			'next_level' => $currentLevel->level,
			'next_level_title' => $currentLevel->title,
			'is_approved' => (($approval_action === 1) ? 1 : 0),
			'is_rejected' => (($approval_action === 0) ? 1 : 0),
			'is_send_back' => (($approval_action === 2) ? 1 : 0),
			'is_swaped' => (($approval_action === 3) ? 1 : 0),
			'is_resubmitted' => (($approval_action === 4) ? 1 : 0),
			'reason' => $approval_reason,
		]);;

		return $approvalRequestApproval;
    }
}