<?php

namespace refaltor\vote;

use pocketmine\plugin\PluginBase;
use refaltor\vote\command\vote;

class Main extends PluginBase
{
    private static $instance;

    public function onEnable() :void
    {
        self::$instance = $this;
        $this->saveResource('config.yml');
        $array = $this->getConfig()->get('command');
        $this->getServer()->getCommandMap()->register('vote', new vote($this));
    }

    public static function getInstance(): self{
        return self::$instance;
    }
}
