<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\Approval;
use Exceptio\ApprovalPermission\Models\ApprovalRequest;

class ApprovalRequestApproval extends Model
{
	protected $table="ex_approval_request_approvals";
	protected $fillable = [
		'approval_id',
		'approval_request_id',
		'user_id',
		'prev_level',
		'prev_level_title',
		'next_level',
		'next_level_title',
		'is_approved',
		'is_rejected',
		'is_swaped',
		'reason'
	];

	public function approval(){
		return $this->belongsTo(Approval::class);
	}

	public function approval_request(){
		return $this->belongsTo(ApprovalRequest::class);
	}

	public function user(){
		return $this->belongsTo(config('approval-config.user-model'));
	}
}
