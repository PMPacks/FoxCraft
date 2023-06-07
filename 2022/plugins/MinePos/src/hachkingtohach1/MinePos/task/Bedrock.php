<?php

declare(strict_types = 1);

namespace hachkingtohach1\MinePos\task;

use hachkingtohach1\MinePos\MinePos;
use hachkingtohach1\MinePos\math\Math;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use pocketmine\block\{Block, BlockIds};
use pocketmine\math\Vector3;
use pocketmine\Server;

class Bedrock extends Task {
	
	public function __construct(MinePos $plugin){
        $this->plugin = $plugin;		
	}	
	
	public function onRun() :void{      
		$time = microtime(true);		
		foreach($this->plugin->minepos->getAll() as $i => [$world, $chance1, $chance2, $blocks, $pos1, $pos2]) {			
		    $pos1 = Math::mathStr($pos1);
		    $pos2 = Math::mathStr($pos2);
			foreach($this->plugin->blockneedreplace->getAll() as $rpos => $name) {
                $posr = Math::mathStr($rpos);				
			    if(min($pos1->x, $pos2->x) <= $posr->x 
				    && max($pos1->x, $pos2->x) >= $posr->x 
			        && min($pos1->y, $pos2->y) <= $posr->y
			        && max($pos1->y, $pos2->y) >= $posr->y 
			        && min($pos1->z, $pos2->z) <= $posr->z
			        && max($pos1->z, $pos2->z) >= $posr->z
				){			        
					if($name == $world) {
						$level = $this->plugin->getServer()->getWorldManager()->getWorldByName($world);
						if(!$this->plugin->getServer()->getWorldManager()->isWorldGenerated($world)){
                            return;
						}
					    if(!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($world)){
                            $this->plugin->getServer()->getWorldManager()->loadWorld($world);
						    return;
						}
						$blockz = explode(',', $blocks)[array_rand(explode(',', $blocks), 1)];
					    $block = (int)$blockz;
					    $this->plugin->setBlockNeed($level, $posr->x, $posr->y, $posr->z, $block);
					    $this->plugin->blockneedreplace->remove($rpos);					
					}
				}
			}
		}
		$time = microtime(true) - $time;	
	}				
}