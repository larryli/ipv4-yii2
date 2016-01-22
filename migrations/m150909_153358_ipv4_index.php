<?php

use yii\BaseYii as Yii;
use yii\db\Migration;

/**
 * Class m150909_153358_ipv4_index
 */
class m150909_153358_ipv4_index extends Migration
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        foreach ($ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $table = $this->tableName($name);
                $this->createTable($table, [
                    'id' => $this->bigPrimaryKey(),
                    'division_id' => $this->integer(),
                ], $tableOptions);
            }
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function down()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        foreach ($ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $this->dropTable($this->tableName($name));
            }
        }
    }

    /**
     * @param $name
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function tableName($name)
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        return $ipv4->prefix . $name;
    }
}
