<?php

use yii\db\Migration;

/**
 * Handles the creation for table `student`.
 */
class m161005_011155_create_student_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('student', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'gender' => $this->char(1),
            'acad' => $this->string(),
            'uuid' => $this->string(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-student-user_id',
            'student',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->batchInsert('student',
            ['acad', 'gender', 'name', 'uuid', 'user_id', 'action'],
        [
            ['AE','1',NULL,'ADRIAN YOO',NULL,53,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','2',NULL,'MICHAEL YOO',NULL,57,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','3',NULL,'YOO YOO',NULL,58,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','4',NULL,'LEE YOO',NULL,55,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE', "10156135E",NULL, "TAN GUAN SENG",NULL,50,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['ECE', "10159710G",NULL, "PENG YONG HUI",NULL,51,'0000-00-00 00:00:00','2016-04-26 10:49:37'),

            ['AE','22222222B',NULL,'AIK YU CHE',NULL,2,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','33333333B',NULL,'AKAASH SIN',NULL,5,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','44444444B',NULL,'ANTHONY CHEN',NULL,4,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
            ['AE','55555555B',NULL,'MICHAEL CHEN',NULL,NULL,'0000-00-00 00:00:00','2016-04-26 10:49:37')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey(
            'fk-student-user_id',
            'student'
        );

        $this->dropTable('student');
    }
}
