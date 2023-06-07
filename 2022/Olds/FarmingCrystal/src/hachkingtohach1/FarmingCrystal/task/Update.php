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

class Update extends Task {
	
	public $createEntity = true;
	
	public function __construct(FarmingCrystal $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun(int $currentTick){
		if($this->createEntity == true){
			foreach($this->plugin->getServer()->getLevels() as $level){
                foreach($level->getEntities() as $entity){
				    if($entity instanceof Crystal){
						$entity->kill();
					}
				}
			}	
			foreach($this->plugin->getConfig()->getAll() as $case => $data){
			    $level = $this->plugin->getServer()->getLevelByName($data["WORLD"]);
			    if(!$this->plugin->getServer()->isLevelLoaded($data["WORLD"])) {
                    $this->plugin->getServer()->loadLevel($data["WORLD"]);
				}
			    $level->setBlockIdAt($data["X"], $data["Y"], $data["Z"], $data["ID"]);
                $level->setBlockDataAt($data["X"], $data["Y"], $data["Z"], $data["META"]);
			    $this->plugin->getConfig()->remove($case);
				$this->plugin->getConfig()->save();
			}           		
			foreach($this->plugin->crystal as $case => $data){
				$level = $this->plugin->getServer()->getLevelByName($data["LEVEL"]);
				if(!$this->plugin->getServer()->isLevelGenerated($data["LEVEL"])) {
                    return;
				}
		        if(!$this->plugin->getServer()->isLevelLoaded($data["LEVEL"])) {
                    $this->plugin->getServer()->loadLevel($data["LEVEL"]);
			        return;
				}
			    $skin = $this->plugin->getSkin($data["SKIN"]);
		        $nbt = new CompoundTag("", [
                    new ListTag("Pos", [
                        new DoubleTag("", (int)$data["X"]),
                        new DoubleTag("", (int)($data["Y"])),
                        new DoubleTag("", (int)$data["Z"])
                    ]),
                    new ListTag("Motion", [
                        new DoubleTag("", 0),
                        new DoubleTag("", 0),
                        new DoubleTag("", 0)
                    ]),
                    new ListTag("Rotation", [
                        new FloatTag("", 90),
                        new FloatTag("", 90)
                    ]),
                    new CompoundTag("Skin", [
                        new StringTag("Data", $skin->getSkinData()),
                        new StringTag("Name", "Crystal"),
                    ]),
			    ]);
                $entity = new Crystal($level, $nbt);
			    $entity->setScale(1);
			    $entity->spawnToAll();
				$this->plugin->register[$entity->getId()] = [$data["Y"], $data["DISTANCE"]];
			}
			$this->createEntity = false;
		}
		foreach($this->plugin->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
				if($entity instanceof Crystal){					
					if(!isset($this->plugin->postions[$entity->getId()])){
						$this->plugin->postions[$entity->getId()] = [
						    "ENTITY" => $entity,
						    "POSTION" => [],
						];
						return;
					}
					if(!empty($this->plugin->postions[$entity->getId()]["POSTION"])){
						$rand = array_rand($this->plugin->postions[$entity->getId()]["POSTION"], 1);
						$data = $this->plugin->postions[$entity->getId()]["POSTION"][$rand];
						if($entity->getLevel()->getName() == $data[5]){
							$a = new Vector3($entity->x, $entity->y + 3, $entity->z);
							$b = new Vector3($data[0], $data[1], $data[2]);						    
							$distance = $this->plugin->findDist($a, $b);
							$vector = $b->subtract($a)->normalize()->multiply(0.2);
							for($i = 0; $i <= $distance; $i += 0.2){
								$a = $a->add($vector);	
                                $particle = new DustParticle($a, 255, 255, 255);								
			                    $entity->getLevel()->addParticle($particle);
							}
							$entity->getLevel()->setBlockIdAt($data[0], $data[1], $data[2], $data[3]);
                            $entity->getLevel()->setBlockDataAt($data[0], $data[1], $data[2], $data[4]);
							$this->plugin->getConfig()->remove($entity->getId().$data[0].$data[1].$data[2].$data[4]);
						    $this->plugin->getConfig()->save();	
						    unset($this->plugin->postions[$entity->getId()]["POSTION"][$rand]);						    
						}
					}					
				}
			}
		}
	}
}