<?php

declare(strict_types = 1);

namespace hachkingtohach1\FishingRod;


use pocketmine\block\StillWater;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

class FishingHook extends Projectile {

	protected $gravity = 0.0;
	protected $drag = 0.05;
	protected $touchedWater = false;
	
	public static function getNetworkTypeId() : string{ 
	    return EntityIds::FISHING_HOOK; 
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ 
	    return new EntitySizeInfo(0.25, 0.25); 
	}

	public function onUpdate(int $currentTick): bool{
		if($this->getOwningEntity() == null or (!$this->getOwningEntity() instanceof Entity)){
			$this->close();
		}
		if($this->getOwningEntity() instanceof Player){
			if(!$this->getOwningEntity()->isOnline()){
				$this->close();
			}
		}
		if($this->isFlaggedForDespawn() || !$this->isAlive()){
			return false;
		}
		$hasUpdate = parent::onUpdate($currentTick);

		if($this->isCollidedVertically){
			$this->motion->x = 0;
			$this->motion->y += 0.01;
			$this->motion->z = 0;
			$hasUpdate = true;
		}elseif($this->isCollided && $this->keepMovement === true){
			$this->motion->x = 0;
			$this->motion->y = 0;
			$this->motion->z = 0;
			$this->keepMovement = false;
			$hasUpdate = true;
		}
		if($this->isCollided && !$this->touchedWater){
			foreach($this->getBlocksAroundWithEntityInsideActions() as $block){
				if($block instanceof Water || $block instanceof StillWater){
					$this->touchedWater = true;
					
					$pk = new ActorEventPacket();
					$pk->actorRuntimeId = $this->getId();
					$pk->eventId = ActorEvent::FISH_HOOK_POSITION;
					$pk->eventData = ActorEvent::FISH_HOOK_POSITION;
					Server::getInstance()->broadcastPackets($this->getViewers(), [$pk]);
					break;
				}
			}
		}
		return $hasUpdate;
	}

	public function attractFish(){
		$oe = $this->getOwningEntity();
		if($oe instanceof Player){
			$pk = new ActorEventPacket();
			$pk->actorRuntimeId = $this->getId();
			$pk->eventId = ActorEvent::FISH_HOOK_BUBBLE;
			$pk->eventData = ActorEvent::FISH_HOOK_BUBBLE;
			Server::getInstance()->broadcastPackets($this->getViewers(), [$pk]);
		}
	}

	public function fishBites(){
		$oe = $this->getOwningEntity();
		if($oe instanceof Player){
			$pk = new ActorEventPacket();
			$pk->actorRuntimeId = $this->getId();
			$pk->eventId = ActorEvent::FISH_HOOK_HOOK;
			$pk->eventData = ActorEvent::FISH_HOOK_HOOK;
			Server::getInstance()->broadcastPackets($this->getViewers(), [$pk]);
		}
	}

	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void{
		Server::getInstance()->getPluginManager()->callEvent(new ProjectileHitEntityEvent($this, $hitResult, $entityHit));

		$damage = $this->getResultDamage();

		if($this->getOwningEntity() === null){
			$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}else{
			$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}
		
		if(!($entityHit instanceof Player)) return;

		$entityHit->attack($ev);

		$entityHit->setMotion($this->getOwningEntity()->getDirectionVector()->multiply(-0.3)->add(0, 0.3, 0));

		$this->isCollided = true;
		$this->flagForDespawn();
	}

	public function getResultDamage(): int{
		return 1;
	}
}
