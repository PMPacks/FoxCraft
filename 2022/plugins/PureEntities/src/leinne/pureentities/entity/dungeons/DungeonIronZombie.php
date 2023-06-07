<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\dungeons;

use leinne\pureentities\entity\Monster;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\color\Color;
use pocketmine\player\Player;
use pocketmine\entity\Ageable;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use hachkingtohach1\MyItem\MyItem;

class DungeonIronZombie extends Monster implements Ageable{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::ZOMBIE;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.9, 0.6);
    }

    protected function initEntity(CompoundTag $nbt) : void{	
		$this->setAddHealth(7000);	
        parent::initEntity($nbt);
		
		$this->baby = $nbt->getByte("IsBaby", 0) !== 0;
        $this->breakDoor = $nbt->getByte("CanBreakDoors", 1) !== 0;    
        if($this->isBaby()){
            $this->setScale(0.5);
        }
		$helmet = $this->getDataItem(298, 0, 1)->setCustomColor(new Color(127, 127, 127));
		$chestplate = $this->getDataItem(299, 0, 1)->setCustomColor(new Color(127, 127, 127));
		$leggings = $this->getDataItem(300, 0, 1)->setCustomColor(new Color(127, 127, 127));
		$boots = $this->getDataItem(301, 0, 1)->setCustomColor(new Color(127, 127, 127));
		
		$helmet->setUnbreakable();
		$chestplate->setUnbreakable();
		$leggings->setUnbreakable();
		$boots->setUnbreakable();
		
		$this->getArmorInventory()->setHelmet($helmet);
		$this->getArmorInventory()->setChestplate($chestplate);
		$this->getArmorInventory()->setLeggings($leggings);
		$this->getArmorInventory()->setBoots($boots);
		
        $this->setSpeed(0.9);
        $this->setDamages([5, 7, 9, 13]);
    }
	
	public function isBaby(): bool{
        return $this->baby;
    }

    public function getName() : string{
        return TextFormat::GRAY."Iron Zombie";
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
	
	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
        parent::syncNetworkData($properties);

        $properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT();
		$nbt->setByte("IsBaby", $this->isBaby() ? 1 : 0);
        $nbt->setByte("CanBreakDoors" , $this->breakDoor ? 1 : 0);
        return $nbt;
    }

    public function getDrops() : array{
        $drops = [
            VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
        ];

        if(rand(1, 1000) <= 10){ //0.01%		    
            $drops[] = MyItem::getInstance()->getItem("PROTECTOR_CHESTPLATE", 1);
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
		if($this->isBaby()){
            return 12;
        }
        return 5;
    }
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
}