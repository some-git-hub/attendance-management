<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained('attendance_corrections')->onDelete('cascade');
            $table->foreignId('rest_id')->nullable()->constrained('rests')->nullOnDelete();  // 休憩時間の追加申請をするときは null
            $table->time('rest_in')->nullable();   // 休憩時間の削除申請をするときは null
            $table->time('rest_out')->nullable();  // 休憩時間の削除申請をするときは null
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
        Schema::dropIfExists('rest_corrections');
    }
}
