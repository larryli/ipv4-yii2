<?php
/**
 * BenchmarkAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use larryli\ipv4\DatabaseQuery;
use larryli\ipv4\FileQuery;
use larryli\ipv4\Query;
use Yii;
use yii\helpers\Console;

/**
 * Class BenchmarkAction
 * @package larryli\ipv4\yii2\actions
 */
class BenchmarkAction extends Action
{
    /**
     * @var \larryli\ipv4\yii2\commands\Ipv4Controller
     */
    public $controller;

    /**
     * benchmark
     *
     * @param string $type file or database
     *
     * @throws \Exception
     */
    public function run($type = 'all')
    {
        $times = $this->controller->times;
        if ($times < 1) {
            $this->stderr("benchmark {$times} is too small\n", Console::FG_GREY, Console::BG_RED);
            return;
        }
        $this->stdout("benchmark {$type}:", Console::FG_GREEN);
        $this->stdout("\t{$times} times\n", Console::FG_YELLOW);
        switch ($type) {
            case 'all':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    $this->benchmark($query, $name, $times);
                }
                break;
            case 'file':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    if (FileQuery::is_a($query)) {
                        $this->benchmark($query, $name, $times);
                    }
                }
                break;
            case 'database':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    if (DatabaseQuery::is_a($query)) {
                        $this->benchmark($query, $name, $times);
                    }
                }
                break;
            default:
                $this->stderr("Unknown type \"{$type}\".\n", Console::FG_GREY, Console::BG_RED);
                break;
        }
    }

    /**
     * @param Query $query
     * @param string $name
     * @param integer $times
     * @throws \Exception
     */
    private function benchmark(Query $query, $name, $times)
    {
        $step = intval(4000000000 / $times);
        if ($step < 1) {
            $step = 1;
        }
        if (count($query) > 0) {
            $this->stdout("\t" . "benchmark {$name}: \t", Console::FG_GREEN);
            $start = microtime(true);
            for ($ip = 0, $i = 0; $i < $times; $ip += $step, $i++) {
                $query->find($ip);
            }
            $time = microtime(true) - $start;
            $this->stdout("{$time} secs\n", Console::FG_YELLOW);
        }
    }
}
