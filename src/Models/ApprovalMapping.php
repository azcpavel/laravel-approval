<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\Approval;
use Exceptio\ApprovalPermission\Models\ApprovalMappingField;

class ApprovalMapping extends Model
{
	use HasFactory;
	protected $table="ex_approval_mappings";
	protected $fillable=[
		'approval_id',
		'approvable_type',
		'relation',
		'title'
	];
	public $timestamps = false;

	public function approval(){
		return $this->belongsTo(Approval::class);
	}

	public function fields(){
		return $this->hasMany(ApprovalMappingField::class);
	}
}
