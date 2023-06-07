<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use hachkingtohach1\MyItem\armor\Bonus;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\PlayerStats\PlayerStats;

class UpdateArmor extends Task {
	
	public function __construct(MyItem $plugin){
        $this->plugin = $plugin;
	}
	
	public function onRun() :void{
		$class = $this->plugin->getBonus();
		$instance = $this->plugin::getInstance();
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			foreach($player->getArmorInventory()->getContents() as $index => $item){
			    $check = $class->checkFullSet($player);
				if($item->getNamedTag()->getTag("Bonus", StringTag::class) != null){
					if($item->getNamedTag()->getString("Bonus") != $instance::KEY_BONUS){
					    $data = $class->getDataBonus($item->getNamedTag()->getString("Bonus"));
					    foreach($data as $result){
						    if($result["EVENT"] == $class::FULLSET){
						        if(isset($this->plugin->armorUsing[$player->getName()])){
						            if($check["FULLSET"] and $this->plugin->armorUsing[$player->getName()] != $check["RELATIVE"]){
										$this->plugin->armorUsing[$player->getName()] = $item->getNamedTag()->getString("Relative");
										$class->caculateBonus($data, $player);
									}
								} 
							}
						}
					}
				}
				if(isset($this->plugin->armorUsing[$player->getName()])){
					if($this->plugin->armorUsing[$player->getName()] != ""){
						if(!$check["FULLSET"] or $this->plugin->armorUsing[$player->getName()] != $item->getNamedTag()->getString("Relative")){
					        if(isset(PlayerStats::getInstance()->add[$player->getXuid()])){
		                        PlayerStats::getInstance()->add[$player->getXuid()] = 0;
							}
						}
					}
				}
			}
		}
	}	
}