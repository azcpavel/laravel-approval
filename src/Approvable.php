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
    	$approval = Approval::where('approvable_type',$approvalble)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	
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
			    	if($firstLevel->group_notification){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalItem, null, $firstLevel->notifiable_params->channels));
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

    public function notifyApprovalUpdate($approvalItem, $approvalMapping){
    	$approvalble = get_class($approvalItem);
    	$approval = Approval::where('approvable_type',$approvalble)->where('status',1)->with('levels.forms.form_data','levels.users','mappings.fields')->first();
    	
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
			    	if($firstLevel->group_notification){
						$notifiableClass = $firstLevel->notifiable_class;
						$userModel = config('approval-config.user-model');
						$users = new $userModel();
						Notification::send($users->whereIn('id',$firstLevel->approval_users->where('user_id','!=',auth()->id())->where('status',1)->pluck('user_id')->all())->get(),new $notifiableClass($approvalItem, null, $firstLevel->notifiable_params->channels));
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