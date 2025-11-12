<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_history_id')->constrained('attendance_histories')->cascadeOnDelete();
            $table->foreignId('rest_id')->nullable()->constrained()->nullOnDelete();

            $table->time('before_rest_in')->nullable();
            $table->time('before_rest_out')->nullable();

            $table->time('after_rest_in')->nullable();
            $table->time('after_rest_out')->nullable();

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
        Schema::dropIfExists('rest_histories');
    }
}
