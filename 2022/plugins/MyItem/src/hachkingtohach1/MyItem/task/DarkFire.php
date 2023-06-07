<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use hachkingtohach1\MyItem\MyItem;

class DarkFire extends Task {
	
	public function __construct(MyItem $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(isset($this->plugin->darkFire[$player->getName()])){
				foreach($this->plugin->darkFire[$player->getName()]["ENTITIES"] as $entity){
			        if($entity->isAlive()){
			            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_MAGIC, 10);																			
			            $entity->attack($event);
					}
				}
			}
		}
	}
}