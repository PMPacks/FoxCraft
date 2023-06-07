<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\dungeons;

use leinne\pureentities\entity\Monster;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\color\Color;
use pocketmine\entity\Ageable;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use hachkingtohach1\MyItem\MyItem;

class BossFloorOne extends Monster{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::IRON_GOLEM;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1, 3);
    }

    protected function initEntity(CompoundTag $nbt) : void{
		$this->setScale(3);		
		$this->setAddHealth(250000);	
        parent::initEntity($nbt);

        $this->breakDoor = $nbt->getByte("CanBreakDoors", 1) !== 0;       	
        $this->setSpeed(1.2);
        $this->setDamages([20, 30, 50]);
    }

    public function getName() : string{
        return TextFormat::BOLD.TextFormat::GOLD."Lord Of Golem >";
    }

    public function interactTarget() : bool{
        if(!parent::interactTarget()){
            return false;
        }

        if($this->interactDelay >= 5){			
            $this->broadcastAnimation(new ArmSwingAnimation($this));

            $target = $this->getTargetEntity();
            $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getResultDamage());
            $target->attack($ev);
            if(rand(1, 20) <= 18){   
                $players = [];			
				foreach($this->getWorld()->getEntities() as $entity){	
                    $vector = new Vector3($this->getLocation()->x, $this->getLocation()->y, $this->getLocation()->z);
                    if($entity->getPosition()->distance($vector) <= 5){
					    if($entity instanceof Player){
						    $players[$entity->getName()] = $entity;
						}                       						
				    }
			    }
				if(count($players) >= 1){
				    $player = $players[array_rand($players, 1)];
					if($player->getWorld()->getFolderName() == $this->getWorld()->getFolderName()){
						$from = new Vector3($this->getLocation()->x, $this->getLocation()->y + $this->getEyeHeight() + 3, $this->getLocation()->z);
						$to = new Vector3($player->getLocation()->x, $player->getLocation()->y + $player->getEyeHeight(), $player->getLocation()->z);					    
						$distance = sqrt(pow($from->x - $to->x, 2) + pow($from->y - $to->y, 2) + pow($from->z - $to->z, 2));
						$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(0.2);
						for($i = 0; $i <= $distance; $i += 0.2){
							$from = $from->add($vector->x, $vector->y, $vector->z);								
			                $this->getWorld()->addParticle($from, new DustParticle(new Color(237, 28, 36)), $this->getWorld()->getPlayers());
					    }
						$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, rand(10, 15));
                        $player->attack($ev);
						if(rand(1, 5) == 5){
						    $player->sendMessage(TextFormat::BOLD.TextFormat::RED."BOSS > ĂN TIA LAZE CỦA TA NÀO!");
						}
					}
				}
			}else{
				foreach($this->getWorld()->getEntities() as $entity){	
                    $vector = new Vector3($this->getLocation()->x, $this->getLocation()->y, $this->getLocation()->z);
                    if($entity->getPosition()->distance($vector) <= 7){
					    if($entity instanceof Player){
							$from = new Vector3($this->getLocation()->x, $this->getLocation()->y + $this->getEyeHeight() + 3, $this->getLocation()->z);
							$to = new Vector3($entity->getLocation()->x, $entity->getLocation()->y + $entity->getEyeHeight(), $entity->getLocation()->z);					    
							$distance = sqrt(pow($from->x - $to->x, 2) + pow($from->y - $to->y, 2) + pow($from->z - $to->z, 2));
							$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(0.2);
							for($i = 0; $i <= $distance; $i += 0.2){
								$from = $from->add($vector->x, $vector->y, $vector->z);								
			                	$this->getWorld()->addParticle($from, new DustParticle(new Color(237, 28, 36)), $this->getWorld()->getPlayers());
					    	}
						    $motFlat = $this->getDirectionPlane()->normalize()->multiply(5 * 3.75 / 20);
                            $mot = new Vector3($motFlat->x, -1, $motFlat->y);
                            $entity->setMotion($mot);
                            $mot = new Vector3($motFlat->x, 1, $motFlat->y);
                            $entity->setMotion($mot);
							$vectorNew = new Vector3($this->getLocation()->x, $this->getLocation()->y, $this->getLocation()->z);
		                    $entity->getWorld()->addParticle($vectorNew, new HugeExplodeParticle(), $this->getWorld()->getPlayers());
					        $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 15);
                            $target->attack($ev);
							$target->sendMessage(TextFormat::BOLD.TextFormat::RED."BOSS > TIỆT CHIÊU DƯ CHẤN BÙNG NỔ!");
						}                       						
				    }
			    }
			}
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
            VanillaItems::IRON_INGOT()->setCount(mt_rand(0, 10))
        ];

        if(rand(1, 100) <= 10){ //10%		    
            $drops[] = MyItem::getInstance()->getItem("GOLD'S_GOD", rand(1, 5));
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
        return 500;
    }
}