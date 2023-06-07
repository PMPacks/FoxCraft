<?php

declare(strict_types = 1);

namespace hachkingtohach1\CraftingTable\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use hachkingtohach1\CraftingTable\CraftingTable;

class UpdateGUI extends Task {
	
	public function __construct(CraftingTable $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(isset($this->plugin->usingCraftingTable[$player->getName()])){
				$menu = $this->plugin->usingCraftingTable[$player->getName()];
				foreach($this->plugin->recipes as $recipe){				
					$hasResult = 0;
					$slots = [10 => 1, 11 => 2, 12 => 3, 19 => 4, 20 => 5, 21 => 6, 28 => 7, 29 => 8, 30 => 9];
					foreach($slots as $slot => $result){
				    	if($menu->getItem($slot)->getId() == (int) explode(",", $recipe[$result])[0]
					    	and $menu->getItem($slot)->getMeta() == (int) explode(",", $recipe[$result])[1]
					    	and $menu->getItem($slot)->getCount() >= (int) explode(",", $recipe[$result])[2]
						){
							$hasName = true;
					    	$hasFlag = true;
							if((string) explode(",", $recipe[$result])[3] != "false"){
								if($menu->getItem($slot)->getCustomName() != (string) explode(",", $recipe[$result])[3]){
									$hasName = false;
								}								
							}							
							if((string) explode(",", $recipe[$result])[4] != "false"){
								$tag = (string) explode(":", explode(",", $recipe[$result])[4])[1];
								$type = (string) explode(":", explode(",", $recipe[$result])[4])[0];
								if($type == "int"){
									if($menu->getItem($slot)->getNamedTag()->getTag($tag, IntTag::class) == null){
								   		$hasFlag = false;
									}
								}
								if($type == "string"){
									if($menu->getItem($slot)->getNamedTag()->getTag($tag, StringTag::class) == null){
								    	$hasFlag = false;
									}
								}
							}
							if($hasFlag == true and $hasName == true){
								$hasResult++;
							}
						}
					}
                	if($hasResult >= 9){
				    	$menu->setItem(23, $recipe["result"]);
					}              				
				}
			}
		}
	}
}