<?php

use yii\db\Migration;

/**
 * Class m150909_153352_ipv4_division
 */
class m150909_153352_ipv4_division extends Migration
{
    /**
     *
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $table = $this->tableName();
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'is_city' => $this->boolean(),
            'parent_id' => $this->integer(),
        ], $tableOptions);
        $this->createIndex('is_city', $table, 'is_city');
        $this->createIndex('parent_id', $table, 'parent_id');
    }

    /**
     *
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function tableName()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = \Yii::$app->get('ipv4');
        return $ipv4->prefix . 'divisions';
    }
}
