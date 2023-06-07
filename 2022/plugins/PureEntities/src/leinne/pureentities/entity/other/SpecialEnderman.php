<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\other;

use leinne\pureentities\entity\Monster;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\entity\Ageable;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use hachkingtohach1\MyItem\MyItem;

class SpecialEnderman extends Monster{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::ENDERMAN;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1, 3);
    }

    protected function initEntity(CompoundTag $nbt) : void{		
		$this->setAddHealth(1000);	
        parent::initEntity($nbt);

        $this->breakDoor = $nbt->getByte("CanBreakDoors", 1) !== 0;       	
        $this->setSpeed(1.2);
        $this->setDamages([2, 3, 10]);
    }

    public function getName() : string{
        return TextFormat::LIGHT_PURPLE."Special Enderman";
    }

    public function interactTarget() : bool{
        if(!parent::interactTarget()){
            return false;
        }

        if($this->interactDelay >= 20){			
            $this->broadcastAnimation(new ArmSwingAnimation($this));

            $target = $this->getTargetEntity();
            $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getResultDamage());
            $target->attack($ev);
            if(!$ev->isCancelled()){
                $this->interactDelay = 0;
            }
        }
        return true;
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT();
        $nbt->setByte("CanBreakDoors" , $this->breakDoor ? 1 : 0);
        return $nbt;
    }

    public function getDrops() : array{
        $drops = [
            VanillaItems::ENDER_PEARL()->setCount(mt_rand(0, 2))
        ];

        if(rand(1, 100) <= 40){ //40%		    
            $drops[] = MyItem::getInstance()->getItem("SPECIAL_ENDER_EYE", rand(1, 5));
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
        return 100;
    }
}