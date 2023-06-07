<?php
/**
 * Copyright 2020-2021 DragoVN
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types = 1);

namespace hachkingtohach1\MirrorNc\task;

use pocketmine\utils\TextFormat;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Human;
use pocketmine\entity\object\ItemEntity;
use slapper\entities\SlapperEntity;
use hachkingtohach1\MirrorNc\MirrorNc;

class ProcessSystem extends Task {
	
	/** @var int $timeRestart*/
	public $timeRestart = 180;
	
	/** @var int $timesClear*/
	public $timesClear = 50;
	
	/** @var bool $restart*/
	private $restart = false;
	
	public function __construct(){}
	
	public function onRun(int $currentTick){
		$prefix = MirrorNc::getInstance()->prefix;
		$loadServer = MirrorNc::getInstance()->getLoadServer();
		$maxMemory = MirrorNc::getInstance()->maxMemoryToRestart();
		$memoryUsed = MirrorNc::getInstance()->getMemoryUsedServer();
		$maxEntitiesItem = MirrorNc::getInstance()->maxEntitiesItem;
		foreach(MirrorNc::getInstance()->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
			    $maxDistance = MirrorNc::MAX_DISTANCE;			
			    foreach($entity->getLevel()->getNearbyEntities($entity->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $entity) as $entities){
				    if($entities instanceof ItemEntity and $entity instanceof ItemEntity){
						$entities->teleport(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()));
					}
				}   
			}
		}			
		if($memoryUsed >= $maxMemory){
			$this->restart = true;
		}
        if($this->restart){
			foreach(MirrorNc::getInstance()->getServer()->getOnlinePlayers() as $player){
			    $player->sendTip($prefix.TextFormat::YELLOW."Restarting in ".TextFormat::RED.$this->timeRestart);
			}
			if($this->timeRestart === 0){
				MirrorNc::getInstance()->getServer()->shutdown();
			}
			$this->timeRestart--;
		}			
	}
}
