<?php

use yii\db\Migration;
use common\models\User;
/**
 * Handles the creation for table `user`.
 */
class m161004_065114_create_user_table extends Migration
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

        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'person_id' => $this->string(),
            'face_id' => $this->string(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'device_hash' => $this->string()->unique(),
            'password_hash' => $this->string()->notNull(),
            'email' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(User::STATUS_ACTIVE),
            'role' => $this->smallInteger()->notNull()->defaultValue(User::ROLE_USER),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Insert users for student
        $this->batchInsert('user',
            ['username', 'auth_key', 'password_hash', 'email', 'device_hash', 'role'],
        [
            ['tungpm', 'tungpm', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'tung@mail.com', 'f8:32:e4:5f:77:4f', User::ROLE_USER],
            ['namth', 'namth', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'namthse03439@fpt.edu.vn', '74:51:ba:3f:8b:22', User::ROLE_USER],
            ['canhnht', 'canhnht', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'canh@mail.com', 'f8:32:e4:5f:6f:35', User::ROLE_USER],
            ['charity', 'charity', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'charity@mail.com', 'f8:32:e4:5f:73:f5', User::ROLE_USER],
            ['penghui', 'penghui', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'penghui@mail.com', '38:2c:4a:3d:5f:ae', User::ROLE_USER],
        ]);

        // Insert users for teacher
        $this->batchInsert('user',
            ['username', 'auth_key', 'password_hash', 'email', 'role'],
        [
            ['zhangqinjie', 'zhangqinjie', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'zhangqinjie@mail.com', User::ROLE_USER],
            ['foojong', 'foojong', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'faj2@np.edu.sg', User::ROLE_USER],
            ['kohkheng', 'kohkheng', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'ksk@np.edu.sg', User::ROLE_USER],
            ['kohseng', 'kohseng', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'kks6@np.edu.sg', User::ROLE_USER],
            ['tangseng', 'tangseng', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'tks5@np.edu.sg', User::ROLE_USER],
        ]);

        // Insert other users
        $this->batchInsert('user',
            ['username', 'auth_key', 'password_hash', 'email', 'role'],
        [
            ['2222', '2222', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', '22222222@connect.np.edu.sg', User::ROLE_USER],
            ['3333', '3333', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', '33333333@connect.np.edu.sg', User::ROLE_USER],
            ['4444', '4444', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', '44444444@connect.np.edu.sg', User::ROLE_USER],
            ['npnp', 'npnp', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'npnp@mail.com', User::ROLE_USER],
            ['abab', 'abab', '$2y$13$3p4KSrmepU5A8mduqEtz3eicSvfEskzLnnUsIukJayp3e7jDStnaa', 'abab@mail.com', User::ROLE_USER],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('user');
    }
}
