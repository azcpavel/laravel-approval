<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestApproversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_request_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id');
            $table->foreignId('approval_request_id');
            $table->foreignId('user_id');
            $table->string('title',150);
            $table->tinyInteger('is_flexible')->default(0);
            $table->tinyInteger('is_form_required')->default(0);
            $table->integer('level');
            $table->integer('action_type');
            $table->text('action_data')->nullable();
            $table->text('status_fields');
            $table->tinyInteger('is_data_mapped')->default(0);            
            $table->tinyInteger('is_approved')->default(1);            
            $table->tinyInteger('is_rejected')->default(0);
            $table->text('reason')->nullable();
            $table->string('reason_file',1000)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->foreign('approval_id')->on('ex_approvals')->references('id');
            $table->foreign('approval_request_id')->on('ex_approval_requests')->references('id');
            $table->foreign('user_id')->on(config('approval-config.user-table'))->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_request_approvers');
    }
}
