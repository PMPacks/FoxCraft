<?php

declare(strict_types = 1);

namespace hachkingtohach1\NPC\task;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use hachkingtohach1\NPC\Main;
use hachkingtohach1\NPC\entity\NPCEntity;

class CreateEntity extends Task {
	
	private $time = false;
	
	public function __construct(Main $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{		
        if($this->time == false){
			$this->time = microtime(true);
		}		
        $timeDiff = microtime(true) - $this->time;		
		if($timeDiff < 25){			
			if(count($this->plugin->npc) > 0){
		    	foreach($this->plugin->npc as $case => $npc){
			    	if(!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($npc["WORLD"])) {
                    	$this->plugin->getServer()->getWorldManager()->loadWorld($npc["WORLD"]);
					}
			    	$vector = new Vector3($npc["X"], $npc["Y"], $npc["Z"]);
			    	$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($npc["WORLD"]);
		        	$world->loadChunk($vector->x >> 1000, $vector->z >> 1000);
				}
			}
			$this->plugin->registerEntities();		
		}else{
			$this->plugin->registerId = [];
			foreach($this->plugin->getServer()->getWorldManager()->getWorlds() as $world){
				foreach($world->getEntities() as $entity){
					if($entity instanceof NPCEntity){
						$entity->close();
					}
				}
			}
			$this->time = microtime(true);
		}
	}
}