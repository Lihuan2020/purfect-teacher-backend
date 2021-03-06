<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTeacherApplyElectiveCourses2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher_apply_elective_courses', function (Blueprint $table) {
            $table->unsignedInteger('start_year')->nullable(false)
                ->comment('课程开始年度');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teacher_apply_elective_courses', function (Blueprint $table) {
            $table->dropColumn('start_year');
        });
    }
}
