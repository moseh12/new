<?php

use App\Models\QuizQuestion;
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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->string('question_type');
            $table->string('question');
            $table->text('answers');
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        $now  = now();

        $data = [
            [
                'quiz_id'       => 1,
                'question_type' => 'default',
                'question'      => 'write down about software development',
                'answers'       => '[{"answer":"ans 1","is_correct":0},{"answer":"ans 2","is_correct":0},{"answer":"ans 3","is_correct":0},{"answer":"ans 4","is_correct":1}]',
                'description'   => 'about software development',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'quiz_id'       => 1,
                'question_type' => 'default',
                'question'      => 'what is software development',
                'description'   => 'about software development',
                'created_at'    => $now,
                'updated_at'    => $now,
                'answers'       => '[{"answer":"ans 1","is_correct":0},{"answer":"ans 2","is_correct":1},{"answer":"ans 3","is_correct":0},{"answer":"ans 4","is_correct":0}]',
            ],
            [
                'quiz_id'       => 1,
                'question_type' => 'mcq',
                'question'      => 'best tool for software development',
                'created_at'    => $now,
                'updated_at'    => $now,
                'description'   => 'about software development',
                'answers'       => '[{"answer":"ans 1.1","is_correct":1},{"answer":"ans 1.2","is_correct":0},{"answer":"ans 1.3","is_correct":0},{"answer":"ans 1.4","is_correct":0}]',
            ],
        ];
        QuizQuestion::insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_questions');
    }
};
