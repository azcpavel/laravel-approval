<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalMapping;

class ApprovalMappingField extends Model
{
	protected $table="ex_approval_mapping_fields";
	protected $fillable=[
		'approval_mapping_id',
		'field_name',
		'field_label',
		'field_relation',
		'field_relation_pk',
		'field_relation_show',
		'field_type',
	];
	public $timestamps = false;

	public function mapping(){
		return $this->belongsTo(ApprovalMapping::class);
	}
}
