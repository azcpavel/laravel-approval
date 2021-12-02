<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\ApprovalLevelForm;
class ApprovalLevelFormData extends Model
{
    use HasFactory;
    protected $table="ex_approval_level_form_data";
    protected $fillable = [
        'approval_level_form_id',
        'mapped_field_name',
        'mapped_field_label',
        'mapped_field_relation',
        'mapped_field_type',
    ];
    public $timestamps = false;

    public function approval_level_form(){
        return $this->belongsTo(ApprovalLevelForm::class);
    }
}
