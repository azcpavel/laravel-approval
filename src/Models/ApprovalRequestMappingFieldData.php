<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalRequestMappingField;

class ApprovalRequestMappingFieldData extends Model
{
	use HasFactory;
	protected $table="ex_approval_request_mapping_field_data";
	protected $fillable=[
		'approval_request_mapping_field_id',
		'field_name',
		'field_label',
		'field_relation',
		'field_type',
		'field_data'
	];
	public $timestamps = false;

	public function field(){
		return $this->belongsTo(ApprovalRequestMappingField::class);
	}
}
