<?php

declare(strict_types=1);

namespace hachkingtohach1\PlayerStats\entity;

use pocketmine\entity\{Entity, EntitySizeInfo};
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;

class DamageIndicator extends Entity{
	
	public static function getNetworkTypeId() : string{ return EntityIds::ARMOR_STAND; }

    protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }
	
	public function onUpdate(int $currentTick):bool{
		$this->setScale(0.01);
		$this->setImmobile(true);
		$this->setInvisible(false);
		$this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);  
		return parent::onUpdate($currentTick);
	}
	
	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}
}