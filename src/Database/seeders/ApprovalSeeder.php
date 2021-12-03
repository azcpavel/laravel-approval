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
            'view_route_param' => ["member" => "id"],
            'list_data_fields' => ["company_name", "serial_number"],
            'on_create' => 1,
            'on_update' => 0,
            'on_delete' => 0
        ]);

        $user = \DB::table(config('approval-config.user-table'))->first();

        //level 1
        $firstLevel = $approval->levels()->create([
            'title' => 'Checker Level',
            'is_flexible' => 0,
            'is_form_required' => 0,
            'level' => 1,
            'action_type' => 1,
            'action_data' => ["approve" => ["class" => "App\Http\Controllers\MemberController", "method" => "approvedMembership"], "reject" => ["class" => "App\Http\Controllers\MemberController", "method" => "rejectedMembership"]],
            'status_fields' => ["approve" => ["status" => 2, "completion" => 0], "reject" => ["completion" => 2]],
            'is_data_mapped' => 0,
            'notifiable_class' => 'App\Notifications\MembershipApproveNotification',
            'notifiable_params' => ["channels" => ["mail", "database"]],
            'group_notification' => 1,
            'next_level_notification' => 1,
            'is_approve_reason_required' => 0,
            'is_reject_reason_required' => 1,
            'action_frequency' => 2,
        ]);

        $firstLevel->approval_users()->create([
            'user_id' => $user->id,
        ]);

        //level 2
        $secondLevel = $approval->levels()->create([
            'title' => 'Company Visit Level',
            'is_flexible' => 0,
            'is_form_required' => 0,
            'level' => 2,
            'action_type' => 1,
            'action_data' => ["approve" => null, "reject" => ["class" => "App\Http\Controllers\MemberController", "method" => "rejectedMembership"]],
            'status_fields' => ["approve" => ["status" => 3, "completion" => 0], "reject" => ["completion" => 2]],
            'is_data_mapped' => 0,
            'notifiable_class' => 'App\Notifications\MembershipApproveNotification',
            'notifiable_params' => ["channels" => ["mail", "database"]],
            'group_notification' => 1,
            'next_level_notification' => 1,
            'is_approve_reason_required' => 0,
            'is_reject_reason_required' => 1,
            'action_frequency' => 0,
        ]);

        $secondLevel->approval_users()->create([
            'user_id' => $user->id,
        ]);

        //level 3
        $thirdLevel = $approval->levels()->create([
            'title' => 'Sub Committee Level',
            'is_flexible' => 1,
            'is_form_required' => 0,
            'level' => 3,
            'action_type' => 1,
            'action_data' => ["approve" => null, "reject" => ["class" => "App\Http\Controllers\MemberController", "method" => "rejectedMembership"]],
            'status_fields' => ["approve" => ["status" => 4, "completion" => 0], "reject" => ["completion" => 2]],
            'is_data_mapped' => 0,
            'notifiable_class' => 'App\Notifications\MembershipApproveNotification',
            'notifiable_params' => ["channels" => ["mail", "database"]],
            'group_notification' => 1,
            'next_level_notification' => 1,
            'is_approve_reason_required' => 0,
            'is_reject_reason_required' => 1,
            'action_frequency' => 0,
        ]);

        $thirdLevel->approval_users()->create([
            'user_id' => $user->id,
        ]);

        //final level
        $finalLevel = $approval->levels()->create([
            'title' => 'Final Approval Level',
            'is_flexible' => 0,
            'is_form_required' => 1,
            'level' => 4,
            'action_type' => 1,
            'action_data' => ["approve" => ["class" => "App\Http\Controllers\MemberController", "method" => "approvedMembership"], "reject" => ["class" => "App\Http\Controllers\MemberController", "method" => "rejectedMembership"]],
            'status_fields' => ["approve" => ["status" => 5, "completion" => 1], "reject" => ["completion" => 2]],
            'is_data_mapped' => 0,
            'notifiable_class' => 'App\Notifications\MembershipApproveNotification',
            'notifiable_params' => ["channels" => ["mail", "database"]],
            'group_notification' => 1,
            'next_level_notification' => 0,
            'is_approve_reason_required' => 1,
            'is_reject_reason_required' => 1,
            'action_frequency' => 2,
        ]);

        $finalLevel->approval_users()->create([
            'user_id' => $user->id,
        ]);

        $form = $finalLevel->forms()->create([
           'title' => 'Membership Information',
           'approvable_type' => 'App\Models\Member',
        ]);

        $form->form_data()->create([
            'mapped_field_name' => 'mid',
            'mapped_field_label' => 'Membership ID',
            'mapped_field_type' => 'text',
        ]);

        $form->form_data()->create([
            'mapped_field_name' => 'branch_id',
            'mapped_field_label' => 'Branch',
            'mapped_field_relation' => 'branch',
            'mapped_field_relation_pk' => 'id',
            'mapped_field_relation_show' => 'name',
            'mapped_field_type' => 'select',
        ]);

    }
}
