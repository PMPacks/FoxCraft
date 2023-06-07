<?php

namespace hachkingtohach1\AreaMobs\task;

use pocketmine\scheduler\Task;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;
use hachkingtohach1\AreaMobs\AreaMobs;

class AutoSpawnMobs extends Task {
	
	private $plugin;
	
    public function __construct(AreaMobs $plugin){
		$this->plugin = $plugin;
    }
	
    public function onRun() :void{
        foreach($this->plugin->getDataAreas() as $id => $data){
			if(!isset($this->plugin->register[$id])){
				$this->plugin->register[$id] = [];
			}
			if(!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($data["WORLD"])) {
                $this->plugin->getServer()->getWorldManager()->loadWorld($data["WORLD"]);
			}
			if(count($this->plugin->register[$id]) < $data["AMOUNT"]){
				$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($data["WORLD"]);
				$spawn = explode(",", $data["SPAWN"]);
				$vector3 = new Vector3($spawn[0], $spawn[1], $spawn[2]);
				$world->loadChunk($vector3->x >> 100, $vector3->z >> 100);
				$entityClass = $data["ENTITY"]; 
				$entity = new $entityClass(Location::fromObject($vector3, $world));
            	$entity->spawnToAll();
				$this->plugin->register[$id][$entity->getId()] = $entity;
			}
		}			
    }
}