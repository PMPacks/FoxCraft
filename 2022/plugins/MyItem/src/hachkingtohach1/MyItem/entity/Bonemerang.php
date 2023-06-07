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

class Bonemerang extends Entity{
	
	public static function getNetworkTypeId() : string{ return EntityIds::ARMOR_STAND; }

    protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }
	
	protected function sendSpawnPacket(Player $player) : void{
        if($this->getOwningEntity() != null or ($this->getOwningEntity() instanceof Player)){
		    parent::sendSpawnPacket($player);
		   $class = new ItemFactory();
		   $item = $class->get(352, 0, 1, null);		  
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
        $time = MyItem::getInstance()->bonemerang[$this->getId()][1];  		
		if(isset(MyItem::getInstance()->bonemerang[$this->getOwningEntity()->getName()][$time])){
			$bonemerang = MyItem::getInstance()->bonemerang[$this->getOwningEntity()->getName()][$time][1];		
			$item = MyItem::getInstance()->bonemerang[$bonemerang->getOwningEntity()->getName()][$time][0];	$item = MyItem::getInstance()->bonemerang[$bonemerang->getOwningEntity()->getName()][$time][0];	
			$timeDiff = microtime(true) - MyItem::getInstance()->bonemerang[$bonemerang->getId()][2];
			if($bonemerang->getOwningEntity()->getWorld()->getFolderName() != $bonemerang->getWorld()->getFolderName()){	
				if($bonemerang->getOwningEntity()->getInventory()->canAddItem($item)){
					$bonemerang->getOwningEntity()->getInventory()->addItem($item);
				}else{
					$bonemerang->getOwningEntity()->getWorld()->dropItem($bonemerang->getOwningEntity()->getPosition(), $item);
				}
				unset(MyItem::getInstance()->bonemerang[$bonemerang->getOwningEntity()->getName()][$time]);
				unset(MyItem::getInstance()->bonemerang[$bonemerang->getId()]);
		    	$bonemerang->close();
				return false;
			}
			$bonemerang->setNametagVisible(true);
        	$bonemerang->setNameTagAlwaysVisible(true);
			$bonemerang->setInvisible(true);
			$bonemerang->getLocation()->yaw += 20;
			$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);			
			$bonemerang->getNetworkProperties()->setInt(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, rand(6, 8));
			foreach(Math::getEntitiesRadius(2, $bonemerang) as $entity){
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
					and !($entity instanceof GiantSword)
					and !($entity instanceof Bonemerang)
				){
					$event = new EntityDamageByEntityEvent($bonemerang->getOwningEntity(), $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, (int) MyItem::getInstance()->bonemerang[$bonemerang->getId()][0]);																	
					$entity->attack($event);
				}
			}
			$x = $bonemerang->getOwningEntity()->getLocation()->x - $bonemerang->getLocation()->x;
			$y = $bonemerang->getOwningEntity()->getLocation()->y - $bonemerang->getLocation()->y;
	    	$z = $bonemerang->getOwningEntity()->getLocation()->z - $bonemerang->getLocation()->z;
			if($bonemerang->getLocation()->y > $bonemerang->getOwningEntity()->getLocation()->y){
               	$bonemerang->motion->y -= 0.4;
			}
			if($bonemerang->getLocation()->y < $bonemerang->getOwningEntity()->getLocation()->y){
                $bonemerang->motion->y += 0.2;
		    }
			if($timeDiff <= 3){
				$bonemerang->getNetworkProperties()->setInt(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, rand(6, 8));
				$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);
				$bonemerang->getMotion()->multiply(0.5);
				$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);
		    	$bonemerang->move($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);
				$bonemerang->updateMovement();
			}else{			
		    	//$this->motion->y = $this->getOwningEntity()->getLocation()->y;
		    	$bonemerang->getNetworkProperties()->setInt(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, rand(6, 8));
            	$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);		
				$bonemerang->motion->x = 5 * 0.15 * (($x + 0.1) / (abs($x) + abs($z)));
				$bonemerang->motion->z = 5 * 0.15 * (($z + 0.1) / (abs($x) + abs($z)));
				$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);					
				$bonemerang->move($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);
				$bonemerang->updateMovement();
				if($x * $x + $z * $z < 1 + $bonemerang->getScale() or $timeDiff >= 5){
					$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);		
					$bonemerang->motion->x = 0;
			    	$bonemerang->motion->z = 0;
					$bonemerang->boundingBox->offset($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);					
                	$bonemerang->move($bonemerang->motion->x, $bonemerang->motion->y, $bonemerang->motion->z);	
                	$bonemerang->updateMovement();	
                	//$item = MyItem::getInstance()->bonemerang[$bonemerang->getOwningEntity()->getName()][$time][0];				
					if($bonemerang->getOwningEntity()->getInventory()->canAddItem($item)){
				    	$bonemerang->getOwningEntity()->getInventory()->addItem($item);
					}else{
						$bonemerang->getOwningEntity()->getWorld()->dropItem($bonemerang->getOwningEntity()->getPosition(), $item);
					}
					unset(MyItem::getInstance()->bonemerang[$bonemerang->getOwningEntity()->getName()][$time]);
					unset(MyItem::getInstance()->bonemerang[$bonemerang->getId()]);
					$bonemerang->close();
				}
			}
		}
		$this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);	
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);				
        $this->updateMovement();		
		return parent::onUpdate($currentTick);
	}
	
	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}
}