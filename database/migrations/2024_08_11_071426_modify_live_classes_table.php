<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropColumn('class_date');
            $table->dropColumn('end_at');
            $table->dropColumn('start_at');
            $table->dropColumn('is_free');
            $table->dropColumn('meeting_id');
            $table->dropColumn('meeting_password');
            $table->text('meeting_method')->change();
            $table->text('meeting_link')->change();
            $table->dateTime('start_time')->after('meeting_method');
            $table->integer('duration')->after('start_time')->comment('Duration in minutes');
            $table->text('data')->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
