<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\entity\Attribute;
use hachkingtohach1\MyItem\MyItem;

class RemoveIce extends Task {
	
	private $entity;
	private $pos;
	private $plugin;
	
	public function __construct($entity, string $pos, $level, MyItem $plugin){
		$this->entity = $entity;
		$this->pos = $pos;
		$this->level = $level;
		$this->plugin = $plugin;
	}	
	
	public function onRun() :void{
        $x = (int) explode(",", $this->pos)[0];
	    $y = (int) explode(",", $this->pos)[1];
		$z = (int) explode(",", $this->pos)[2];
		$this->level->setBlockIdAt($x, $y + 2, $z, 0);
        $this->level->setBlockDataAt($x, $y + 2, $z, 0);
		if($this->entity != null){
			$this->entity->setSneaking(false);
			unset($this->plugin->freeze[$this->entity->getId()]);
		}
	}
}	
