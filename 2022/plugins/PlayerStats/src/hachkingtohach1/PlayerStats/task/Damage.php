<?php

declare(strict_types = 1);

namespace hachkingtohach1\PlayerStats\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\PlayerStats\PlayerStats;
use hachkingtohach1\PlayerStats\entity\DamageIndicator;

class Damage extends Task{
	
	private $plugin;
	private $player;	
	private $entity;	
	
	public function __construct(PlayerStats $plugin, Player $player, DamageIndicator $entity){
        $this->plugin = $plugin;
		$this->entity = $entity;
		$this->player = $player;
	}	
	
	public function onRun() :void{
        if(isset($this->plugin->damageability[$this->player->getXuid()])){
			unset($this->plugin->damageability[$this->player->getXuid()]);
		}			
		$this->entity->close();
	}
}	
