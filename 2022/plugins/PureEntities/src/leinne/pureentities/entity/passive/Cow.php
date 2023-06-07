<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\passive;

use leinne\pureentities\entity\Animal;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\item\Bucket;
use pocketmine\world\sound\BucketFillWaterSound;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class Cow extends Animal{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::COW;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.3, 0.9);
    }

    public function getDefaultMaxHealth() : int{
        return 10;
    }

    public function getName() : string{
        return 'Cow';
    }
	
	public function interact(Player $player, Item $item) : bool{
        $hand = $player->getInventory()->getItemInHand();
		if($hand instanceof Bucket and $hand->getMeta() === 0) { // check for empty bucket
			$item = ItemFactory::getInstance()->get(ItemIds::BUCKET, 1);
			if($player->isSurvival()){
				if($hand->getCount() > 1){
					$count = $hand->getCount();
			        $player->getInventory()->setItemInHand($hand->setCount($count - 1));
					$player->getInventory()->addItem($item);
				}else{
					$player->getInventory()->setItemInHand($item);
				}
			}else{
				$player->getInventory()->addItem($item);
			}
			$this->getWorld()->addSound($this->location, new BucketFillWaterSound());
		}
        return false;
    }

    public function canInteractWithTarget(Entity $target, float $distanceSquare) : bool{
        return false; //TODO: 아이템 유인 구현
    }

    public function interactTarget() : bool{
        if(!parent::interactTarget()){
            return false;
        }

        // TODO: 동물 AI 기능
        return false;
    }

    public function getDrops() : array{
        return [
            VanillaItems::LEATHER()->setCount(mt_rand(0, 2)),
            ItemFactory::getInstance()->get($this->isOnFire() ? ItemIds::STEAK : ItemIds::RAW_BEEF, 0, mt_rand(1, 3)),
        ];
    }

    public function getXpDropAmount() : int{
        return mt_rand(1, 3);
    }

}