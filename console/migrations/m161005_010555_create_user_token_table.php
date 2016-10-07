<?php

use yii\db\Migration;

/**
 * Handles the creation for table `user_token`.
 */
class m161005_010555_create_user_token_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('user_token', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(),
            'title' => $this->string(),
            'ip_address' => $this->string(),
            'expire_date' => $this->timestamp()->notNull(),
            'created_date' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_date' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'action' => $this->smallInteger()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-user_token-user_id',
            'user_token',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->batchInsert('user_token',
            ['user_id', 'token', 'title', 'ip_address', 'expire_date', 'action'],
        [
            [1, 'uEjx4gdvgBZmJbxEZfqG8E6Qs1H6c6nu', 'ACTION_ACCESS', '127.0.0.1', '2016-08-24 07:03:08', 1],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey(
            'fk-user_token-user_id',
            'user_token'
        );
        $this->dropTable('user_token');
    }
}
