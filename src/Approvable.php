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
	ApprovalRequestApprover,
	ApprovalRequestApproverForm,
	ApprovalRequestApproverFormData,
	ApprovalRequestMappingField,
	ApprovalRequestMappingFieldData
};

use Illuminate\Support\Facades\Notification;

trait Approvable 
{
    public function notifyApprovalCreate($approvalItem){
    	$approvalble = get_class($approvalItem);
    	$approval = Approval::where('approvable_type',$approvalble)->where('on_create',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	
    	try{
    		if($approval){
    			$old_request = $approval->requests->where('approvable_id',$approvalItem->id)->whereBetween('completed',[0,2])->first();    			
    			if($old_request && $old_request->completed == 0)
    				return -2;

    			if($old_request && $old_request->completed == 2){
    				$old_request->completed = 0;
    				$old_request->save();

    				return $old_request;
    			}else{
    				$approvalRequest = $approval->requests()->create([
			    		'approvable_type' => $approvalble,
			    		'approvable_id' => $approvalItem->id,
						'user_id' => auth()->user()->id,
			    	]);

			    	$firstLevel = $approval->levels->sortBy('level')->first();
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class != 0){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
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

    public function notifyApprovalUpdate($approvalItem, $approvalMapping, $slug){
    	$approvalble = get_class($approvalItem);
    	$approval = Approval::where('approvable_type',$approvalble)->where('on_update',1)->where('status',1)->where('slug',$slug)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	
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
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class != 0){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
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

    public function notifyApprovalDelete($approvalItem){
    	$approvalble = get_class($approvalItem);
    	$approval = Approval::where('approvable_type',$approvalble)->where('on_delete',1)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	
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
			    	if($firstLevel->group_notification && $firstLevel->notifiable_class != 0){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalRequest, $approvalItem, null, $firstLevel->notifiable_params->channels));
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
}