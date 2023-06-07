<?php

declare(strict_types = 1);

namespace hachkingtohach1\FarmingCrystal\task;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\entity\Attribute;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\Skin;
use pocketmine\level\particle\DustParticle;
use hachkingtohach1\FarmingCrystal\FarmingCrystal;
use hachkingtohach1\FarmingCrystal\entity\Crystal;

class Movement extends Task {
	
	public function __construct(FarmingCrystal $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun(int $currentTick){
		foreach($this->plugin->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
				if($entity instanceof Crystal){	
                    if(!isset(FarmingCrystal::getInstance()->register[$entity->getId()])) return;
					if(FarmingCrystal::getInstance()->register[$entity->getId()][0] > $entity->y){
			            $entity->getMotion()->y += 0.08;											
					}
		            if(FarmingCrystal::getInstance()->register[$entity->getId()][0] < $entity->y){
			            $entity->getMotion()->y -= 0.02;												
					}
					$entity->move($entity->getMotion()->x, $entity->getMotion()->y, $entity->getMotion()->z);
		            $entity->updateMovement();
				}
			}
		}		
	}
}