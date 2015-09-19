<?php

/**
 * IPv4.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2;

use larryli\ipv4\Query;
use Yii;
use yii\base\Component;
use yii\base\ExitException;
use yii\db\Connection;

/**
 * Class IPv4
 * @package larryli\ipv4\yii2
 */
class IPv4 extends Component
{
    /**
     * @var string table prefix
     */
    public $prefix = 'ipv4_';
    /**
     * @var \yii\db\Connection
     */
    public $db;
    /**
     * @var string larryli\ipv4\query\Database class
     */
    public $database;
    /**
     * @var array
     */
    public $providers = [
        'monipdb' => [
            'filename' => '@runtime/17monipdb.dat',
        ],
        'qqwry' => [
            'filename' => '@runtime/qqwry.dat',
        ],
        'full' => [
            'providers' => ['monipdb', 'qqwry'],
        ],
        'mini' => [
            'providers' => 'full',
        ],
        'china' => [
            'providers' => 'full',
        ],
        'world' => [
            'providers' => 'full',
        ],
    ];
    /**
     * @var \larryli\ipv4\Query[]
     */
    protected $objects = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDatabase();
        $this->initProviders();
    }

    /**
     * @throws ExitException
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDatabase()
    {
        if (empty($this->db)) {
            $this->db = Yii::$app->db;
        } else if (is_string($this->db)) {
            $this->db = Yii::$app->get($this->db);
        }
        if (!is_a($this->db, Connection::className())) {
            throw new ExitException(500, "{$this->db} is not a db connection object", 500);
        }
        if (empty($this->database)) {
            $this->database = Database::className();
        }
        if (is_string($this->database)) {
            $this->database = new $this->database([
                'db' => $this->db,
                'prefix' => $this->prefix,
            ]);
        }
        if (!Database::is_a($this->database)) {
            throw new ExitException(500, "{$this->database} is not a ipv4 database object", 500);
        }
    }

    /**
     *
     */
    protected function initProviders()
    {
        $result = [];
        foreach ($this->providers as $name => $options) {
            if (is_integer($name)) {
                $name = $options;
                $options = [];
            }
            $result[$name] = $options;
        }
        $this->providers = $result;
        foreach ($this->providers as $name => $options) {
            $providers = [];
            if (is_array($options)) {
                $opt = null;
                if (isset($options['providers'])) {
                    if (is_array($options['providers'])) {
                        $providers = $options['providers'];
                    } else {
                        $providers[] = $options['providers'];
                    }
                    unset($options['providers']);
                    $opt = $this->database;
                }
                if (isset($options['filename'])) {
                    $opt = Yii::getAlias($options['filename']);
                    unset($options['filename']);
                }
                if (isset($options['class']) && !empty($opt)) {
                    $options['options'] = $opt;
                } else {
                    $options = $opt;
                }
            }
            $this->createQuery($name, $options, $providers);
        }
    }

    /**
     * @param string $name
     * @param mixed $options
     * @param array $providers
     * @return Query|null
     * @throws \Exception
     */
    public function createQuery($name, $options, array $providers = [])
    {
        $query = $this->getQuery($name);
        if ($query == null) {
            $query = Query::create($name, $options);
            $query->setProviders(array_map(function ($provider) {
                return $this->getQuery($provider);
            }, $providers));
            $this->objects[$name] = $query;
        }
        return $query;
    }

    /**
     * @param $name
     * @return Query|null
     */
    public function getQuery($name)
    {
        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        }
        return null;
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
        if (isset($this->objects[$name])) {
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
        if (isset($this->objects[$name])) {
            return true;
        }
        return parent::__isset($name);
    }
}