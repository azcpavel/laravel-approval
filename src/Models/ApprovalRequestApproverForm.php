<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalRequestApprover;
use Exceptio\ApprovalPermission\Models\ApprovalRequestApproverFormData;

class ApprovalRequestApproverForm extends Model
{
    protected $table="ex_approval_request_approver_forms";
    protected $fillable = [
        'approval_request_approver_id',
        'approvable_id',
        'approvable_type',
        'relation',
        'title'
    ];
    public $timestamps = false;

    public function approver(){
        return $this->belongsTo(ApprovalRequestApprover::class);
    }

    public function form_data(){
        return $this->hasMany(ApprovalRequestApproverFormData::class);
    }

    public function approvable(){
        return $this->morphTo();
    }
}
