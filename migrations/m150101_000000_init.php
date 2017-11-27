<?php

use yii\db\Schema;

class m150101_000000_init extends \yii\db\Migration
{
    public function up()
    {
        $this->execute('ALTER SCHEMA DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci');
        $this->execute('ALTER SCHEMA CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'is_email_verified' => $this->boolean()->notNull()->defaultValue(0),
            'auth_key' => $this->char(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string(),
            'email_confirmation_token' => $this->string(),
            'email' => $this->string()->notNull(),
            'role' => $this->smallInteger()->notNull()->defaultValue(10),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => Schema::TYPE_DATETIME . ' NOT NULL',
            'updated_at' => Schema::TYPE_DATETIME . ' NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');
    }

    public function down()
    {
        $this->dropTable('user');
    }
}
