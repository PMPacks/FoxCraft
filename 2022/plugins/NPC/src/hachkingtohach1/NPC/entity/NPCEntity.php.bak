<?php

declare(strict_types=1);

namespace hachkingtohach1\NPC\entity;

use pocketmine\player\Player;
use pocketmine\item\ItemFactory;
use pocketmine\entity\{Human, EntitySizeInfo};
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\math\Vector2;
use pocketmine\event\entity\EntityDamageEvent;
use hachkingtohach1\NPC\Main;

class NPCEntity extends Human{

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);
    }
	
	public function onUpdate(int $currentTick) :bool{
		if(!isset(Main::getInstance()->registerId[$this->getId()])){
			$this->close();
			return false;
		}
		foreach($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(1, 1, 1) , $this) as $player){
            if(!$player instanceof Player){
                return true;
            }
            $xdiff = $player->getLocation()->x - $this->getLocation()->x;
            $zdiff = $player->getLocation()->z - $this->getLocation()->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $player->getLocation()->y - $this->getLocation()->y;
            $v = new Vector2($this->getLocation()->x, $this->getLocation()->z);
            $dist = $v->distance(new Vector2($player->getLocation()->x, $player->getLocation()->z));
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;
            $pk = new MovePlayerPacket();
            $pk->actorRuntimeId = $this->getId();
            $pk->position = $this->getPosition()->add(0, $this->getEyeHeight() , 0);
            $pk->yaw = $yaw;
            $pk->pitch = $pitch;
            $pk->headYaw = $yaw;
            $pk->onGround = $this->onGround;
            $player->getNetworkSession()->sendDataPacket($pk);
        }
		return parent::onUpdate($currentTick);
	}
	
	/*public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}*/
}