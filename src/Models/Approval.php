<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalLevel;
use Exceptio\ApprovalPermission\Models\ApprovalMapping;
use Exceptio\ApprovalPermission\Models\ApprovalRequest;
use Exceptio\ApprovalPermission\Models\ApprovalRequestApprover;

class Approval extends Model
{
	use HasFactory,ModelCommonMethodTrait;
	protected $table="ex_approvals";
	protected $fillable=[
		'title',
		'approvable_type',
		'view_route_name',
		'view_route_param',
		'list_data_fields',
		'on_create',
		'on_update',
		'on_delete',
		'status',
	];

	protected $casts = [
		'view_route_param' => 'json',
		'list_data_fields' => 'array'
	];

	public function levels(){
		return $this->hasMany(ApprovalLevel::class);
	}

	public function mappings(){
		return $this->hasMany(ApprovalMapping::class);
	}

	public function requests(){
		return $this->hasMany(ApprovalRequest::class);
	}

	public function request_approver(){
		return $this->hasMany(ApprovalRequestApprover::class);
	}
}