<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalMappingFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approval_mapping_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_mapping_id');
            $table->string('field_name',150);
            $table->string('field_label',150);
            $table->text('field_relation')->nullable();
            $table->string('field_relation_pk',150)->nullable();
            $table->string('field_relation_show',150)->nullable();
            $table->string('field_type',150);
            $table->foreign('approval_mapping_id')->on('ex_approval_mappings')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approval_mapping_fields');
    }
}
