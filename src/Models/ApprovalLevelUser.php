<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalLevel;

class ApprovalLevelUser extends Model
{
	use HasFactory;
	protected $table="ex_approval_level_users";
	protected $fillable=[
		'approval_level_id',
		'user_id',
		'status'
	];
	public $timestamps = false;

	public function approval_level(){
		return $this->belongsTo(ApprovalLevel::class);
	}

	public function user(){
		return $this->belongsTo(config('approval-config.user-model'));
	}
}
