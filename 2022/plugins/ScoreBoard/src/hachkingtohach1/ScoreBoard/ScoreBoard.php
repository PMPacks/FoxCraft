<?php

declare(strict_types=1);

namespace hachkingtohach1\ScoreBoard;

use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use hachkingtohach1\ScoreBoard\task\SendScore;
use onebone\economyapi\EconomyAPI;
use hachkingtohach1\PlayerStats\PlayerStats;
use hachkingtohach1\Quest\Quest;
use hachkingtohach1\FoxEvent\FoxEvent;

class ScoreBoard extends PluginBase implements Listener {

	private static $instance;

	public function onLoad() :void{
        self::$instance = $this;
	}
	
    public static function getInstance(): ScoreBoard{
        return self::$instance;
    }

	public function onEnable() :void{
		$this->saveDefaultConfig();           		
		$this->getScheduler()->scheduleRepeatingTask(new SendScore(), 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function getPluginName(string $name){
		return $this->getServer()->getPluginManager()->getPlugin($name);
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function getFunction(string $message, Player $player): string{
		$location = $player->getLocation();
		$nameFunction = [
	        "%getmoney",
			"%whatday",
			"%time",
			"%mypos",
			"%getgems",
			"%mylevel",
			"%xplevel",
			"%xpneed",
			"%sizeisland",
			"%rank",
			"%quest",
			"%processquest",
			"%event"
	    ];
		$coins = 0;
		if($this->getPluginName("EconomyAPI")->myMoney($player) > 0){
			$coins = number_format($this->getPluginName("EconomyAPI")->myMoney($player));
		}
		$gems = 0;
		if($this->getPluginName("Gems")->myMoney($player) > 0){
			$gems = number_format($this->getPluginName("Gems")->myMoney($player));
		}
		$pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
        $userGroup = $pure->getUserDataMgr()->getGroup($player);    						
		$sizeIsland = 130;
		if($userGroup == "Vip"){
			$sizeIsland = 200;
		}
		if($userGroup == "Vip+"){
			$sizeIsland = 240;
		}
		if($userGroup == "Vip++"){
			$sizeIsland = 240;
		}
		$event = "Chưa có";
		if(FoxEvent::getInstance()->getEvent() != 0){
			switch(FoxEvent::getInstance()->getEvent()){
				case 1:
				    $event = "§aJacob §7> §a Farm ".FoxEvent::getInstance()->event["NAME_BLOCK"];
				break;
			}
		}
        $function = [
		    $coins,
			date("d/m/Y"),
			time(),
			"x:".(int)$location->x." y:".(int)$location->y." z:".(int)$location->z,
			$gems,
			number_format(PlayerStats::getInstance()->getDatabase()->getLevel($player)),
		    number_format(PlayerStats::getInstance()->getDatabase()->getXpLevel($player)),
			number_format((PlayerStats::getInstance()->getDatabase()->getLevel($player) * 5000)),
			$sizeIsland." x ".$sizeIsland,
			$userGroup,
			Quest::getInstance()->getQuestPlayer($player),
			Quest::getInstance()->getProcessQuestPlayer($player),
			$event
		];
		return str_replace($nameFunction, $function, $message);
	}
}