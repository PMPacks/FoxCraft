<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\dungeons;

use leinne\pureentities\entity\Monster;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\utils\TextFormat;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\sound\LaunchSound;
use pocketmine\nbt\tag\CompoundTag;

class DungeonSkeleton extends Monster{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::SKELETON;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.9, 0.6);
    }

    protected function initEntity(CompoundTag $nbt) : void{
		$this->setAddHealth(1000);
        parent::initEntity($nbt);

        $this->setSpeed(3);
    }

    public function getDefaultHeldItem() : Item{
        return VanillaItems::BOW();
    }

    public function getName() : string{
        return TextFormat::WHITE."Dungeon Skeleton";
    }

    public function interactTarget() : bool{
        if(!parent::interactTarget()){
            return false;
        }

        if($this->interactDelay >= 10){			
            $this->broadcastAnimation(new ArmSwingAnimation($this));

            $target = $this->getTargetEntity();
            $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0);
            $target->attack($ev);           
            if(!$ev->isCancelled()){
                $this->interactDelay = 0;
            }
        }
        return true;
    }

    public function getDrops() : array{
        return [
            VanillaItems::BONE()->setCount(mt_rand(0, 2)),
            VanillaItems::ARROW()->setCount(mt_rand(0, 2)),
        ];
    }

    public function getXpDropAmount() : int{
        return 5;
    }

}