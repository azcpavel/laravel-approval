<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalRequestApproverForm;

class ApprovalRequestApproverFormData extends Model
{
	use HasFactory;
	protected $table="ex_approval_request_approver_form_data";
	protected $fillable=[
		'approval_request_approver_form_id',
		'mapped_field_name',
		'mapped_field_label',
		'mapped_field_type',
		'mapped_field_relation',
		'mapped_field_value'
	];
	public $timestamps = false;

	public function form(){
		return $this->belongsTo(ApprovalRequestApproverForm::class);
	}
}
