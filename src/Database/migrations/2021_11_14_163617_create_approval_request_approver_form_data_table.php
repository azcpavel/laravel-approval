<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestApproverFormDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_request_approver_form_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_approver_form_id');
            $table->string('mapped_field_name',150);
            $table->string('mapped_field_label',150);
            $table->string('mapped_field_type',150);
            $table->string('mapped_field_relation',150)->nullable();
            $table->string('mapped_field_relation_pk',150)->nullable();
            $table->string('mapped_field_relation_show',150)->nullable();
            $table->longText('mapped_field_value');
            $table->foreign('approval_request_approver_form_id','approval_request_approver_form_data')->on('ex_approval_request_approver_forms')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_request_approver_form_data');
    }
}
