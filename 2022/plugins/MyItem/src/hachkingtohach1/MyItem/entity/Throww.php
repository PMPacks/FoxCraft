<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\entity;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\entity\{Entity, EntitySizeInfo};
use pocketmine\math\Vector3;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\entity\object\PrimedTNT;
use DaPigGuy\PiggyCustomEnchants\entities\{HomingArrow, PigProjectile, PiggyFireball, PiggyLightning, PiggyProjectile, PiggyTNT, PiggyWitherSkull};
use slapper\entities\{SlapperEntity, SlapperHuman};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use hachkingtohach1\MyItem\utils\Math;
use hachkingtohach1\MyItem\MyItem;

class Throww extends Entity{
	
	public static function getNetworkTypeId() : string{ return EntityIds::ARMOR_STAND; }

    protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }
	
	protected function sendSpawnPacket(Player $player) : void{
        if($this->getOwningEntity() != null or ($this->getOwningEntity() instanceof Player)){
		    parent::sendSpawnPacket($player);
		   $class = new ItemFactory();
		   $item = $class->get((int)MyItem::getInstance()->throw[$this->getOwningEntity()->getName()][1], (int)MyItem::getInstance()->throw[$this->getOwningEntity()->getName()][2], 1, null);		  
           $pk = new MobEquipmentPacket();
           $pk->actorRuntimeId = $this->getId();
           $pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($item));
           $pk->inventorySlot = 0;
           $pk->hotbarSlot = 0;
           $player->getNetworkSession()->sendDataPacket($pk);
		}
    }
	
	public function onUpdate(int $currentTick):bool{
		if($this->getOwningEntity() == null or !($this->getOwningEntity() instanceof Player)){
			$this->close();
			return false;	
		}
		if($this->getOwningEntity() instanceof Player){
			if(!$this->getOwningEntity()->isOnline()){
				$this->close();
			    return false;
			}
		}
		$this->setNametagVisible(true);
        $this->setNameTagAlwaysVisible(true);
		$this->setInvisible(true);
		$x = $this->getOwningEntity()->getLocation()->x - $this->getLocation()->x;
		$y = $this->getOwningEntity()->getLocation()->y - $this->getLocation()->y;
		$z = $this->getOwningEntity()->getLocation()->z - $this->getLocation()->z;
		$this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);
		$this->setMotion($this->getMotion()->multiply(0.60));			
        $this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z); // 9 0 13
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, rand(0, 12));
		foreach(Math::getEntitiesRadius(1.5, $this) as $entity){
			if(!($entity instanceof Projectile) 					
				and !($entity instanceof Painting) 
				and !($entity instanceof ExperienceOrb) 
				and !($entity instanceof ItemEntity) 
				and !($entity instanceof FallingBlock) 
				and !($entity instanceof PaintingMotive) 
				and !($entity instanceof PrimedTNT) 
                and !($entity instanceof Player)                      
                and !($entity instanceof HomingArrow) 
				and !($entity instanceof PigProjectile) 
				and !($entity instanceof PiggyFireball) 
				and !($entity instanceof PiggyLightning) 
				and !($entity instanceof PiggyProjectile) 
				and !($entity instanceof PiggyTNT) 
                and !($entity instanceof PiggyWitherSkull)
                and !($entity instanceof SlapperEntity)
				and !($entity instanceof Throww)
			){
				$event = new EntityDamageByEntityEvent($this->getOwningEntity(), $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, (int) MyItem::getInstance()->throw[$this->getOwningEntity()->getName()][0]);																	
				$entity->attack($event);
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