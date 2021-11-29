<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestMappingFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_request_mapping_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id');
            $table->string('title');
            $table->foreignId('approvable_id');
            $table->string('approvable_type');
            $table->timestamps();
            $table->foreign('approval_request_id')->on('ex_approval_requests')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_request_mapping_fields');
    }
}
