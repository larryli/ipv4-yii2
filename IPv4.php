<?php

/**
 * IPv4.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2;

use Yii;
use yii\base\Component;

/**
 * Class IPv4
 * @package larryli\ipv4\yii2
 */
class IPv4 extends Component
{
    /**
     * object names
     *
     * @var string[]
     */
    static public $classNames = [
        'monipdb' => 'MonIPDBQuery',
        'qqwry' => 'QQWryQuery',
        'full' => 'FullQuery',
        'mini' => 'MiniQuery',
        'china' => 'ChinaQuery',
        'world' => 'WorldQuery',
        'freeipip' => 'FreeIPIPQuery',
        'taobao' => 'TaobaoQuery',
        'sina' => 'SinaQuery',
        'baidumap' => 'BaiduMapQuery',
    ];
    /**
     * @var string table prefix
     */
    public $prefix = 'ipv4_';
    /**
     * @var string runtime path
     */
    public $runtime = '';
    /**
     * @var string larryli\ipv4\query\Database class
     */
    public $database = '';
    /**
     * @var array
     */
    public $providers = [
        'monipdb',
        'qqwry',
        'full' => ['monipdb', 'qqwry'],
        'mini' => 'full',
        'china' => 'full',
        'world' => 'full',
    ];
    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $result = [];
        foreach ($this->providers as $name => $provider) {
            if (is_integer($name)) {
                $name = $provider;
                $provider = '';
            }
            $result[$name] = $provider;
        }
        $this->providers = $result;
        foreach ($this->providers as $name => $provider) {
            $options = null;
            switch ($name) {
                case 'monipdb':
                case 'qqwry':
                    $options = $this->getFileOptions($name);
                    break;
                case 'full':
                case 'mini':
                case 'china':
                case 'world':
                    $options = $this->getDatabaseOptions();
                    break;
            }
            $this->createQuery($name, $options);
        }
    }

    /**
     * @param string $name
     * @param mixed $options
     * @return mixed
     * @throws \Exception
     */
    public function createQuery($name, $options = null)
    {
        if (!isset($this->objects[$name])) {
            if (isset(self::$classNames[$name])) {
                $class = "\\larryli\\ipv4\\" . self::$classNames[$name];
            } else if (is_array($options) && isset($options['class'])) {
                $class = $options['class'];
                $options = $options['options'];
            } else {
                throw new \Exception("Unknown Query name \"{$name}\"");
            }
            $this->objects[$name] = new $class($options);
        }
        return $this->objects[$name];
    }

    /**
     * @param $name
     * @return bool|null|string
     */
    private function getFileOptions($name)
    {
        $options = null;
        if (!empty($this->runtime)) {
            $options = Yii::getAlias($this->runtime);
            switch ($name) {
                case 'monipdb':
                    $options .= '/17monipdb.dat';
                    break;
                case 'qqwry':
                    $options .= '/qqwry.dat';
                    break;
            }
        }
        return $options;
    }

    /**
     * @return array|null
     */
    private function getDatabaseOptions()
    {
        $options = null;
        if (!empty($this->database)) {
            $options = new $this->database(['prefix' => $this->prefix]);
        } else if (!empty($this->runtime)) {
            $options = [
                'database_type' => 'sqlite',
                'database_file' => Yii::getAlias($this->runtime) . '/ipv4.sqlite',
            ];
        }
        return $options;
    }

    /**
     * @return \larryli\ipv4\Query[]
     */
    public function getQueries()
    {
        return $this->objects;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->objects)) {
            return $this->objects[$name];
        }
        return parent::__get($name);
    }

    /**
     * @param string $name
     * @return bool|mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->objects)) {
            return true;
        }
        return parent::__get($name);
    }

}