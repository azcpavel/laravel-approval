<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('title',150);
            $table->string('approvable_type',160);
            $table->string('view_route_name',150);
            $table->string('slug',150)->nullable();
            $table->text('view_route_param');
            $table->text('list_data_fields');
            $table->tinyInteger('on_create')->default(0);
            $table->tinyInteger('on_update')->default(0);
            $table->tinyInteger('on_delete')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ex_approvals');
    }
}
