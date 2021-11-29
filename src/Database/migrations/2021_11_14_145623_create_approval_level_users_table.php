<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLevelUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_level_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_level_id');
            $table->foreignId('user_id');
            $table->tinyInteger('status')->default(1);
            $table->foreign('approval_level_id')->on('ex_approval_levels')->references('id');
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
        Schema::dropIfExists('ex_approval_level_users');
    }
}
