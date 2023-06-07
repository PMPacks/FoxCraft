<?php

declare(strict_types=1);

namespace hachkingtohach1\CoinsAPI;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use hachkingtohach1\CoinsAPI\commands\GetCoins;
use hachkingtohach1\CoinsAPI\commands\GiveCoins;
use hachkingtohach1\CoinsAPI\commands\ReduceCoins;
use hachkingtohach1\CoinsAPI\commands\SetCoins;
use hachkingtohach1\CoinsAPI\commands\ConsumptionHistory;
use hachkingtohach1\CoinsAPI\provider\{DataBase, sql\SQL};

class CoinsAPI extends PluginBase implements Listener {
	
	public $dataBase;	
	private static $instance;

	public function onLoad(){
        self::$instance = $this;
	}
	
    public static function getInstance(): CoinsAPI{
        return self::$instance;
    }

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->dataBase = new SQL("mysql");
        $this->getServer()->getCommandMap()->register("gco", new GetCoins());
		$this->getServer()->getCommandMap()->register("gico", new GiveCoins());
		$this->getServer()->getCommandMap()->register("reco", new ReduceCoins());
	    $this->getServer()->getCommandMap()->register("seco", new SetCoins());
		$this->getServer()->getCommandMap()->register("conh", new ConsumptionHistory());		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}	
	
	public function getDatabase(): Database{
        return $this->dataBase;
	}
	
	public function getCoins(Player $player){
		return $this->getDatabase()->getCoins($player);
	}
	
	public function giveCoins(Player $player, $amount){
		return $this->getDatabase()->giveCoins($player, $amount);
	}
	
	public function reduceCoins(Player $player, $amount){
		$this->addConsumptionHistory($player, $amount);
		return $this->getDatabase()->reduceCoins($player, $amount);	
	}
	
	public function setCoins(Player $player, $amount){
		return $this->getDatabase()->setCoins($player, $amount);	
	}

    public function consumptionHistory(Player $player){
		return $this->getDatabase()->consumptionHistory($player);	
	}

    public function addConsumptionHistory(Player $player, $amount){
		return $this->getDatabase()->addConsumptionHistory($player, $amount);	
	}		
	
	public function onPlayerJoinEvent(PlayerJoinEvent $event) :void{
		$player = $event->getPlayer();
		$this->getDataBase()->createProfile($player);
	}
}