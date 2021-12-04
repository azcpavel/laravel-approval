<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestMappingFieldDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_request_mapping_field_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_mapping_field_id');
            $table->string('field_name',150);
            $table->string('field_label',150);
            $table->string('field_type',150);
            $table->string('field_relation',150)->nullable();
            $table->string('field_relation_pk',150)->nullable();
            $table->string('field_relation_show',150)->nullable();
            $table->longText('field_data')->nullable();
            $table->foreign('approval_request_mapping_field_id','approval_request_mapping_data')->on('ex_approval_request_mapping_fields')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_request_mapping_field_data');
    }
}
