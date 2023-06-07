<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\rune;

use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\level\particle\{
	AngryVillagerParticle, BubbleParticle, CriticalParticle, EnchantParticle, ExplodeParticle, FlameParticle, HeartParticle, EntityFlameParticle
};

class Rune{
	
	public static function getRune(Player $player, $entity){
		$center = new Vector3($entity->getLocation()->x, $entity->getLocation()->y, $entity->getLocation()->z);
	    $item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$particle = null;
		if($nbt->getTag("Rune", IntTag::class) != null){
			if($nbt->getInt("Rune") == 1){
				$particle = new AngryVillagerParticle();
			}
			if($nbt->getInt("Rune") == 2){
				$particle = new BubbleParticle();
			}
			if($nbt->getInt("Rune") == 3){
				$particle = new CriticalParticle();
			}
			if($nbt->getInt("Rune") == 4){
				$particle = new EnchantParticle();
			}
			if($nbt->getInt("Rune") == 5){
				$particle = new ExplodeParticle();
			}
			if($nbt->getInt("Rune") == 6){
				$particle = new FlameParticle();
			}
			if($nbt->getInt("Rune") == 7){
				$particle = new HeartParticle();
			}
			if($nbt->getInt("Rune") == 8){
				$particle = new EntityFlameParticle();
			}
		}
        if($particle != null){		
	        $entity->getWorld()->addParticle($center, $particle, [$entity]);
		}
	}
}