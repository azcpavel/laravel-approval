<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id');
            $table->string('title',150);
            $table->tinyInteger('is_flexible')->default(0);
            $table->tinyInteger('is_form_required')->default(0);
            $table->integer('level');
            $table->integer('action_type');
            $table->text('action_data')->nullable();
            $table->text('status_fields');
            $table->tinyInteger('is_data_mapped')->default(0);
            $table->string('notifiable_class')->nullable();
            $table->text('notifiable_params')->nullable();
            $table->tinyInteger('group_notification')->default(1);
            $table->tinyInteger('next_level_notification')->default(1);            
            $table->tinyInteger('is_approve_reason_required')->default(1);            
            $table->tinyInteger('is_reject_reason_required')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->foreign('approval_id')->on('ex_approvals')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_levels');
    }
}
