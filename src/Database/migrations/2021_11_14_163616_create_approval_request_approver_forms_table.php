<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestApproverFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_request_approver_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_approver_id');
            $table->foreignId('approvable_id');
            $table->string('title',150);
            $table->string('approvable_type');
            $table->string('relation',150)->nullable();
            $table->foreign('approval_request_approver_id','approval_request_approver_form')->on('ex_approval_request_approvers')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_request_approver_forms');
    }
}
