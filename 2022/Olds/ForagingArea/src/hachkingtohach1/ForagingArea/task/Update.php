<?php

declare(strict_types = 1);

namespace hachkingtohach1\ForagingArea\task;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\entity\Attribute;
use hachkingtohach1\ForagingArea\ForagingArea;

class Update extends Task {
	
	public function __construct(ForagingArea $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun(int $currentTick){
		if(!empty($this->plugin->getConfig()->getAll())){
		    $rand = array_rand($this->plugin->getConfig()->getAll(), 1);		
		    $data = $this->plugin->getConfig()->get($rand);
			if(!$this->plugin->getServer()->isLevelGenerated($data["WORLD"])) {
                return;
			}
			if(!$this->plugin->getServer()->isLevelLoaded($data["WORLD"])) {
                $this->plugin->getServer()->loadLevel($data["WORLD"]);
				return;
			}
			$level = $this->plugin->getServer()->getLevelByName($data["WORLD"]);	
			$level->setBlockIdAt($data["X"], $data["Y"], $data["Z"], $data["ID"]);
            $level->setBlockDataAt($data["X"], $data["Y"], $data["Z"], $data["META"]);
			$this->plugin->getConfig()->remove($rand);
			$this->plugin->getConfig()->save();
		}
	}
}