<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id');
            $table->string('title',150);
            $table->string('approvable_type',150);
            $table->string('relation',150)->nullable();
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
        Schema::dropIfExists('ex_approval_mappings');
    }
}
