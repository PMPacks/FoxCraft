<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\entity;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\item\Item;
use pocketmine\entity\{Entity, EntitySizeInfo};
use pocketmine\math\Vector3;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use hachkingtohach1\MyItem\utils\Math;

class Bubble extends Entity{
	
	public static function getNetworkTypeId() : string{ return EntityIds::ARMOR_STAND; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }

    public function setPose() : void{
        $this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, 8);
    }
	
	public function onUpdate(int $currentTick):bool{
		$this->setScale(1);			
		$this->setNametagVisible(true);
        $this->setNameTagAlwaysVisible(true);
		$this->setImmobile(true);
		$this->setInvisible(true);
		$this->getInventory()->setItemInHand(Item::get(35, rand(0, 15), 1));
        $this->getInventory()->sendHeldItem($this->getViewers()); 
        $this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 999, 2));	    		
		$this->getLevel()->broadcastLevelSoundEvent($this->asVector3(), LevelSoundEventPacket::SOUND_LAUNCH);					
		$this->y += 0.01;
		$this->setMotion($this->getMotion()->multiply(1.1));
		$this->yaw += 20;
		$this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);
        foreach(Math::getEntitiesRadius(1.5, $this) as $entity){
			if(!($entity instanceof Projectile) 					
				and !($entity instanceof Painting) 
				and !($entity instanceof ExperienceOrb) 
				and !($entity instanceof ItemEntity) 
				and !($entity instanceof FallingBlock) 
				and !($entity instanceof PaintingMotive) 
				and !($entity instanceof PrimedTNT) 					
			    and ($entity->getId() != $this->getOwningEntityId())
				and ($entity != $this)
			){
				$this->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 100, 2));	 
			}
		}
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		$this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);
		$this->updateMovement();
		return parent::onUpdate($currentTick);
	}
	
	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}
}