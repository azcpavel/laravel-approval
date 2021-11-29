<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLevelFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_level_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_level_id');
            $table->string('title',150);            
            $table->string('approvable_type',150);            
            $table->foreign('approval_level_id')->on('ex_approval_levels')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_level_forms');
    }
}
