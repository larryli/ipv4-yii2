<?php

/**
 * QueryController.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\commands;

use larryli\ipv4\yii2\actions\BenchmarkAction;
use larryli\ipv4\yii2\actions\CleanAction;
use larryli\ipv4\yii2\actions\DumpAction;
use larryli\ipv4\yii2\actions\InitAction;
use larryli\ipv4\yii2\actions\QueryAction;
use Yii;
use yii\console\Controller;

/**
 * ipv4 command
 *
 * @package larryli\ipv4\yii2\commands
 */
class Ipv4Controller extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'query';
    /**
     * @var bool Force to initialize(download qqwry.dat & 17monipdb.dat if not exist & generate new database)
     */
    public $force = 0;
    /**
     * @var bool Do not show progress
     */
    public $noProgress = 0;
    /**
     * @var int number of times
     */
    public $times = 100000;

    public function actions()
    {
        return [
            'query' => QueryAction::className(),
            'init' => InitAction::className(),
            'dump' => DumpAction::className(),
            'clean' => CleanAction::className(),
            'benchmark' => BenchmarkAction::className(),
        ];
    }

    public function options($actionID)
    {
        $options = [];
        switch ($actionID) {
            case 'init':
                $options = [
                    'force',
                    'noProgress',
                ];
                break;
            case 'benchmark':
                $options = [
                    'times',
                ];
                break;
        }
        return array_merge($options, parent::options($actionID));
    }
}
