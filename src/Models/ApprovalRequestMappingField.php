<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalRequest;
use Exceptio\ApprovalPermission\Models\ApprovalRequestMappingFieldData;

class ApprovalRequestMappingField extends Model
{
	use HasFactory;
	protected $table="ex_approval_request_mapping_fields";
	protected $fillable=[
		'approval_request_id',
		'approvable_id',
		'approvable_type',
		'relation',
		'title'
	];
	public $timestamps = false;

	public function approval_request(){
		return $this->belongsTo(ApprovalRequest::class);
	}

	public function form_data(){
        return $this->hasMany(ApprovalRequestMappingFieldData::class);
    }

    public function approvable(){
		return $this->morphTo();
	}
}
