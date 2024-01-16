<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Touixitb extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 550],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 550],
            'password'      => ['type' => 'VARCHAR', 'constraint' => 550],
            'phone_number'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'picture'       => ['type' => 'VARCHAR', 'constraint' => 550],
            'school'        => ['type' => 'VARCHAR', 'constraint' => 550],
            'role_id'       => ['type' => 'INT'],
            'status'        => ['type' => 'INT'],
            'created_at'    => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('accounts');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 550],
            'description'       => ['type' => 'TEXT'],
            'attempt_open_at'   => ['type' => 'INT'],
            'attempt_closed_at' => ['type' => 'INT'],
            'attempt_duration'  => ['type' => 'INT'],
            'published_at'      => ['type' => 'INT'],
            'created_at'        => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exams');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'subtest_id'    => ['type' => 'INT'],
            'subattempt_id' => ['type' => 'INT'],
            'user_id'       => ['type' => 'INT'],
            'question_id'   => ['type' => 'INT'],
            'key_id'        => ['type' => 'INT'],
            'selected_id'   => ['type' => 'INT', 'default' => 0],
            'is_correct'    => ['type' => 'INT', 'default' => 0],
            'marks'         => ['type' => 'INT', 'default' => 0],
            'time_duration' => ['type' => 'INT', 'default' => 0],
            'created_at'    => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exam_answers');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'exam_id'           => ['type' => 'INT'],
            'user_id'           => ['type' => 'INT'],
            'started_at'        => ['type' => 'INT', 'default' => 0],
            'ended_at'          => ['type' => 'INT', 'default' => 0],
            'score_agregate'    => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exam_attempts');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'subtest_id'        => ['type' => 'INT'],
            'question_id'       => ['type' => 'INT'],
            'total_correct'     => ['type' => 'INT', 'default' => 0],
            'total_incorrect'   => ['type' => 'INT', 'default' => 0],
            'total_null'        => ['type' => 'INT', 'default' => 0],
            'total_attempt'     => ['type' => 'INT', 'default' => 0],
            'marks'             => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exam_questions');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'exam_id'       => ['type' => 'INT'],
            'subtest_id'    => ['type' => 'INT'],
            'user_id'       => ['type' => 'INT'],
            'attempt_id'    => ['type' => 'INT'],
            'started_at'    => ['type' => 'INT', 'default' => 0],
            'ended_at'      => ['type' => 'INT', 'default' => 0],
            'score'         => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exam_subattempts');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'exam_id'           => ['type' => 'INT'],
            'type'              => ['type' => 'INT'],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 550],
            'attempt_duration'  => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('exam_subtests');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 550],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('qbank_packages');

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'package_id'        => ['type' => 'INT'],
            'question_text'     => ['type' => 'TEXT'],
            'question_img'      => ['type' => 'VARCHAR', 'constraint' => 550],
            'option_text_1'     => ['type' => 'TEXT'],
            'option_text_2'     => ['type' => 'TEXT'],
            'option_text_3'     => ['type' => 'TEXT'],
            'option_text_4'     => ['type' => 'TEXT'],
            'option_text_5'     => ['type' => 'TEXT'],
            'correct_option_id' => ['type' => 'INT'],
            'created_by'        => ['type' => 'INT'],
            'created_at'        => ['type' => 'INT'],
            'updated_at'        => ['type' => 'INT'],
            'deleted_at'        => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('qbank_questions');
    }

    public function down()
    {
        $this->forge->dropTable('accounts', true);
        $this->forge->dropTable('exams', true);
        $this->forge->dropTable('exam_answers', true);
        $this->forge->dropTable('exam_attempts', true);
        $this->forge->dropTable('exam_questions', true);
        $this->forge->dropTable('exam_subattempts', true);
        $this->forge->dropTable('exam_subtests', true);
        $this->forge->dropTable('qbank_packages', true);
        $this->forge->dropTable('qbank_questions', true);
    }
}
