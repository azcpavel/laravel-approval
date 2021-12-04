<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalLevel;
use Exceptio\ApprovalPermission\Models\ApprovalLevelFormData;

class ApprovalLevelForm extends Model
{
	use HasFactory;
	protected $table="ex_approval_level_forms";
	protected $fillable = [
		'approval_level_id',
		'approvable_type',
		'relation',
		'title'
	];
	public $timestamps = false;

	public function approval_level(){
		return $this->belongsTo(ApprovalLevel::class);
	}

	public function form_data(){
		return $this->hasMany(ApprovalLevelFormData::class);
	}
}
