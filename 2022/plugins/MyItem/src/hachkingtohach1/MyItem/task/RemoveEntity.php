<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\entity\Entity;

class RemoveEntity extends Task {
	
	private $entity;
	
	public function __construct(Entity $entity){
		$this->entity = $entity;
	}	
	
	public function onRun() :void{
        $this->entity->close();		
	}
}	
