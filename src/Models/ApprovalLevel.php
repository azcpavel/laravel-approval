<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\Approval;
use Exceptio\ApprovalPermission\Models\ApprovalLevelForm;
use Exceptio\ApprovalPermission\Models\ApprovalLevelUser;

class ApprovalLevel extends Model
{
	protected $table="ex_approval_levels";
	protected $fillable = [
		'approval_id',
		'title',
		'is_flexible',
		'is_form_required',
		'level',
		'action_type',
		'action_data',
		'action_frequency',
		'status_fields',
		'is_data_mapped',
		'notifiable_class',
		'notifiable_params',
		'group_notification',
		'next_level_notification',
		'next_level_user',
		'need_attachment',
		'is_approve_reason_required',
		'is_reject_reason_required',
		'status'
	];

	protected $casts = [
		'action_data' => 'object',
		'status_fields' => 'object',
		'notifiable_params' => 'object'
	];

	public function approval(){
		return $this->belongsTo(Approval::class);
	}

	public function forms(){
		return $this->hasMany(ApprovalLevelForm::class);
	}

	public function approval_users(){
		return $this->hasMany(ApprovalLevelUser::class);
	}

	public function users(){
		return $this->hasManyThrough(config('approval-config.user-model'),ApprovalLevelUser::class,'approval_level_id','id','id','user_id');
	}
}
