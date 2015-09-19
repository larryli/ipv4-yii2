<?php
/**
 * CleanAction.php
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
 * Class CleanAction
 * @package larryli\ipv4\yii2\actions
 */
class CleanAction extends Action
{
    /**
     * benchmark
     *
     * @param string $type file or database
     *
     * @throws \Exception
     */
    public function run($type = 'all')
    {
        $cleanDivision = false;
        $this->stdout("clean {$type}:\n", Console::FG_GREEN);
        switch ($type) {
            case 'all':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    if (DatabaseQuery::is_a($query)) {
                        $cleanDivision = true;
                    }
                    $this->clean($query, $name);
                }
                break;
            case 'file':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    if (FileQuery::is_a($query)) {
                        $this->clean($query, $name);
                    }
                }
                break;
            case 'database':
                foreach ($this->ipv4->getQueries() as $name => $query) {
                    if (DatabaseQuery::is_a($query)) {
                        $cleanDivision = true;
                        $this->clean($query, $name);
                    }
                }
                break;
            default:
                $this->stderr("Unknown type \"{$type}\".", Console::FG_GREY, Console::BG_RED);
                break;
        }
        if ($cleanDivision) {
            $this->cleanDivision();
        }
    }

    /**
     * @param Query $query
     * @param string $name
     * @throws \Exception
     */
    private function clean(Query $query, $name)
    {
        $this->stdout("clean {$name}:", Console::FG_GREEN);
        $query->clean();
        $this->stdout(" completed!\n", Console::FG_GREEN);
    }

    /**
     *
     */
    private function cleanDivision()
    {
        $this->stdout("clean divisions:", Console::FG_GREEN);
        DatabaseQuery::cleanDivision();
        $this->stdout(" completed!\n", Console::FG_GREEN);
    }
}
