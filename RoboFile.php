<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function phpunit()
    {
        $this->stopOnFail();
        $this->taskPhpUnit()->run();
    }

    public function server()
    {
        $this->taskExec('bin/console server:run')->run();
    }
}