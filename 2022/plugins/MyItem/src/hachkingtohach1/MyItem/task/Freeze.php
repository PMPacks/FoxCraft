<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;
use pocketmine\entity\Attribute;
use hachkingtohach1\MyItem\MyItem;

class Freeze extends Task {
	
	public function __construct(MyItem $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->freeze as $freeze){
			$entity = $freeze[0];
			$pos = $freeze[1];
			$levelName = $freeze[2];
			if(!$this->plugin->getServer()->isLevelGenerated($levelName)) {
                return;
			}
		    if(!$this->plugin->getServer()->isLevelLoaded($levelName)) {
                $this->plugin->getServer()->loadLevel($levelName);
			    return;
			}
			$x = (int) explode(",", $pos)[0];
			$y = (int) explode(",", $pos)[1];
			$z = (int) explode(",", $pos)[2];
			$level = $this->plugin->getServer()->getLevelByName($levelName);
			$vector3 = new Vector3($x, $y, $z);
			if($entity != null){
			    $entity->setSneaking(true);
			}
		    $level->setBlockIdAt($x, $y + 2, $z, 174);
            $level->setBlockDataAt($x, $y + 2, $z, 174);
		}
	}
}