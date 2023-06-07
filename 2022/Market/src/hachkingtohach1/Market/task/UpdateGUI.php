<?php

declare(strict_types = 1);

namespace hachkingtohach1\Market\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use hachkingtohach1\Market\Market;
use hachkingtohach1\Market\utils\Math;

class UpdateGUI extends Task {
	
	public function __construct(Market $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(isset($this->plugin->dataUser[$player->getXuid])){
				$index = $this->plugin->dataUser[$player->getName()][3];
				$menu = $this->plugin->dataUser[$player->getName()][4];
				foreach($menu->getContents() as $case => $item){
					$nbt = $item->getNamedTag();
					foreach($index as $data){
						$sample = $data[0];
						$itemNew = $data[1];
						$id = $data[5];
						$base = $data[7];
                        if($nbt->getTag("Code", StringTag::class) != null){	
						    if($id == $nbt->getString("Code")){
								$nbtNew = $itemNew->getNamedTag();
								$nbtNew->setString("Code", "$id");	
								$newLore = [];
							    foreach($itemNew->getLore() as $lore){
									$newLore[] = $lore;
								}
								$seller = $base["seller"];
								$xuid = $base["xuid"];		
								$price = $base["price"];
            					$time = $base["time"];							
								$newLore[] = TextFormat::BOLD.TextFormat::DARK_GRAY."────────────────";
								$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Người bán: $seller";
								$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Giá bán: ".TextFormat::GOLD.$price;
           						$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Tình trạng: ".Math::calculateTime($time);
								$newLore[] = TextFormat::BOLD.TextFormat::RED."";
								$newLore[] = TextFormat::BOLD.TextFormat::YELLOW."Chạm để xem!";
								$itemNew->setLore($newLore);                        
		                		$itemNew->setNamedTag($nbtNew);
                                $menu->setItem($case, $itemNew);							
							}
						}						
					}
				}
			}
		}
	}
}