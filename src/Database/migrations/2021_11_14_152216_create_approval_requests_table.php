<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id');
            $table->string('approvable_type',150);
            $table->foreignId('approvable_id');
            $table->foreignId('user_id');
            $table->tinyInteger('completed')->default(0);
            $table->timestamps();
            $table->foreign('approval_id')->on('ex_approvals')->references('id');            
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
        Schema::dropIfExists('ex_approval_requests');
    }
}
