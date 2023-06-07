<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\MyItem\entity\Bubble;
use hachkingtohach1\MyItem\entity\Throww;
use hachkingtohach1\PlayerStats\entity\DamageIndicator;

class RemoveLaggy extends Task {
	
	private $plugin;
	
	public function __construct(MyItem $plugin){
		$this->plugin = $plugin;
	}	
	
	public function onRun() :void{
        foreach($this->plugin->deaths as $id => [$player, $time]){
			if((microtime(true) - $time) >= 2){
				unset($this->plugin->deaths[$id]);
			}
		}
		foreach($this->plugin->getServer()->getWorldManager()->getWorlds() as $level){
            foreach($level->getEntities() as $entity){
                if($entity instanceof Bubble or $entity instanceof Throww or $entity instanceof DamageIndicator){
					$entity->close();
				}					
			}
		}
	}
}	
