<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\Approval;
use Exceptio\ApprovalPermission\Models\ApprovalRequest;
use Exceptio\ApprovalPermission\Models\ApprovalRequestApproverForm;

class ApprovalRequestApprover extends Model
{
	protected $table="ex_approval_request_approvers";
	protected $fillable = [
		'approval_id',
		'approval_request_id',
		'user_id',
		'next_user_id',
		'next_level_user',
		'need_attachment',
		'title',
		'is_flexible',
		'is_form_required',
		'level',
		'action_type',
		'action_data',
		'status_fields',
		'is_data_mapped',
		'is_approved',
		'is_rejected',
		'reason',
		'reason_file',
		'status'
	];

	protected $casts = [
		'action_data' => 'object',
		'status_fields' => 'object',
		'reason_file' => 'array',
	];

	public function approval(){
		return $this->belongsTo(Approval::class);
	}

	public function approval_request(){
		return $this->belongsTo(ApprovalRequest::class);
	}

	public function forms(){
		return $this->hasMany(ApprovalRequestApproverForm::class);
	}

	public function user(){
		return $this->belongsTo(config('approval-config.user-model'));
	}

	public function next_user(){
		return $this->belongsTo(config('approval-config.user-model'));
	}
}
