<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Exceptio\ApprovalPermission\Models\Approval;

class ApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $approval = Approval::create([
            'title' => 'Membership Approval',
            'approvable_type' => 'App\Models\Member',
            'view_route_name' => 'members.show',
            'view_route_param' => ["member"=>"id"],
            'list_data_fields' => ["company_name","serial_number"],
            'on_create' => 1,
            'on_update' => 0,
            'on_delete' => 0
        ]);

        $user = \DB::table(config('approval-config.user-table'))->first();

        $firstLevel = $approval->levels()->create([
                    'title' => 'Level 1',
                    'is_flexible' => 0,
                    'is_form_required' => 0,
                    'level' => 1,
                    'action_type' => 1,
                    'action_data' => ["approve" => ["class" => "App\Http\Controllers\MemberController","method" => "handelApproval"],"reject" => ["class" => "App\Http\Controllers\MemberController","method" => "handelApproval"]],
                    'status_fields' => ["approve" => ["status" => 2,"completion" => 0],"reject" => ["completion" => 2],"pending" => ["status" => 1,"completion" => 0]],
                    'is_data_mapped' => 0,
                    'notifiable_class' => 0,
                    'notifiable_params' => null,
                    'group_notification' => 0,
                    'next_level_notification' => 0,
                    'is_approve_reason_required' => 0,
                    'is_reject_reason_required' => 1,
                ]);

        $firstLevel->approval_users()->create([
                        'user_id' => $user->id,
                    ]);

        $finalLevel = $approval->levels()->create([
                    'title' => 'Level 2',
                    'is_flexible' => 0,
                    'is_form_required' => 0,
                    'level' => 2,
                    'action_type' => 1,
                    'action_data' => ["approve" => ["class" => "App\Http\Controllers\MemberController","method" => "handelApproval"],"reject" => ["class" => "App\Http\Controllers\MemberController","method" => "handelApproval"]],
                    'status_fields' => ["approve" => ["status" => 3,"completion" => 1],"reject" => ["completion" => 2],"pending" => ["status" => 2,"completion" => 0]],
                    'is_data_mapped' => 0,
                    'notifiable_class' => 0,
                    'notifiable_params' => null,
                    'group_notification' => 0,
                    'next_level_notification' => 0,
                    'is_approve_reason_required' => 1,
                    'is_reject_reason_required' => 1,                
                ]);
        
        $finalLevel->approval_users()->create([
                        'user_id' => $user->id,
                    ]);
    }
}
