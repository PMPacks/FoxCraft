<?php

declare(strict_types = 1);

namespace hachkingtohach1\PlayerStats\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\entity\{Attribute, AttributeFactory};
use hachkingtohach1\PlayerStats\PlayerStats;
use hachkingtohach1\Dungeon\Dungeon;

class Popup extends Task {
	
	public function __construct(PlayerStats $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
			if(!isset($this->plugin->pets[$player->getXuid()])) return;
			if(isset($this->plugin->intelligence[$player->getXuid()])){
				if($this->plugin->intelligence[$player->getXuid()] > $this->plugin->getIntelligence($player)){
					$reduceMana = $this->plugin->intelligence[$player->getXuid()] - $this->plugin->getIntelligence($player);
					$this->plugin->intelligence[$player->getXuid()] -= $reduceMana;
				}
				if($this->plugin->intelligence[$player->getXuid()] < $this->plugin->getIntelligence($player)){
		            $this->plugin->intelligence[$player->getXuid()] += $this->plugin->addIntelligence[$player->getXuid()];
				}
			}
			if(isset($this->plugin->critChance[$player->getXuid()])){
				if($this->plugin->critChance[$player->getXuid()] <= $this->plugin->getCritChance($player) and $this->plugin->critChance[$player->getXuid()] <= 100){
					$this->plugin->critChance[$player->getXuid()] += rand(10, 15);
				}
			}else{
				return;
			}
			if(!isset($this->plugin->speed[$player->getXuid()])){				
				$value = $this->plugin->getSpeed($player);
				$player->setMovementSpeed((0.05 + 0.1 * ($value/100)));
				$this->plugin->speed[$player->getXuid()] = $value;
			}	
            if($this->plugin->speed[$player->getXuid()] != $this->plugin->getSpeed($player)){	
                $value = $this->plugin->getSpeed($player);
				$player->setMovementSpeed((0.05 + 0.1 * ($value/100)));
			    $this->plugin->speed[$player->getXuid()] = $value;
			} 		
            if(!Dungeon::getInstance()->inGame($player)){
			    if($player->getHealth() <= 5){
				    $maxHealth = $this->plugin->getMaxHealthPlayer($player)/5;
			        $player->setHealth((int)($maxHealth));
				    $player->sendMessage("You death!");				
				    $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
				}
			}				
			if($player->getHealth() == 0) return;
            $maxHealth = $this->plugin->getMaxHealthPlayer($player)/5;			
		    $health = (int)($player->getHealth() * 5);
			$player->setMaxHealth((int)($maxHealth));			
			$tip = "§l§c".$health."/".$this->plugin->getMaxHealthPlayer($player)."❤".
			        " §a".$this->plugin->getDefensePlayer($player)."❈ Defense"."".
					" §b".$this->plugin->intelligence[$player->getXuid()]."/".$this->plugin->getIntelligence($player)."✎ Mana";
			$player->sendTip($tip);
		}
	}
}