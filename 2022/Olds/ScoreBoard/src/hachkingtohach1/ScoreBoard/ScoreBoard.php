<?php

declare(strict_types=1);

namespace hachkingtohach1\ScoreBoard;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use hachkingtohach1\ScoreBoard\task\SendScore;

class ScoreBoard extends PluginBase implements Listener {

	private static $instance;

	public function onLoad(){
        self::$instance = $this;
	}
	
    public static function getInstance(): ScoreBoard{
        return self::$instance;
    }

	public function onEnable(){
		$this->saveDefaultConfig();           		
		$this->getScheduler()->scheduleRepeatingTask(new SendScore(), 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function getPluginName(string $name){
		return $this->getServer()->getPluginManager()->getPlugin($name);
	}
	
	public function getFunction(string $message, Player $player): string{
		$nameFunction = [
	        "%getcoins",
			"%whatday",
			"%time"
	    ];
        $function = [
		    $this->getPluginName("CoinsAPI")->getCoins($player),
			date("d/m/Y"),
			time()
		];
		return str_replace($nameFunction, $function, $message);
	}
}