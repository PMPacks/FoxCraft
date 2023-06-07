<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\dungeons;

use leinne\pureentities\entity\Monster;
use leinne\pureentities\entity\ai\walk\WalkEntityTrait;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\entity\Ageable;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\effect\{Effect, EffectInstance, StringToEffectParser};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Fire;
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\color\Color;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use hachkingtohach1\MyItem\MyItem;

class BossFloorTwo extends Monster{
    use WalkEntityTrait;

    public static function getNetworkTypeId() : string{
        return EntityIds::SPIDER;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.9, 0.6);
    }

    protected function initEntity(CompoundTag $nbt) : void{		
	    $this->setScale(1.5);		
		$this->setAddHealth(500000);	
        parent::initEntity($nbt);

        $this->breakDoor = $nbt->getByte("CanBreakDoors", 1) !== 0;     

        //$helmet = $this->getDataItem(298, 0, 1)->setCustomColor(new Color(255, 242, 0));
		//$chestplate = $this->getDataItem(299, 0, 1)->setCustomColor(new Color(255, 242, 0));
		//$leggings = $this->getDataItem(300, 0, 1)->setCustomColor(new Color(255, 242, 0));
		//$boots = $this->getDataItem(301, 0, 1)->setCustomColor(new Color(255, 242, 0));
		
		//$helmet->setUnbreakable();
		//$chestplate->setUnbreakable();
		//$leggings->setUnbreakable();
		//$boots->setUnbreakable();
		
		//$this->getArmorInventory()->setHelmet($helmet);
		//$this->getArmorInventory()->setChestplate($chestplate);
		//$this->getArmorInventory()->setLeggings($leggings);
		//$this->getArmorInventory()->setBoots($boots);
		
        $this->setSpeed(3);
        $this->setDamages([1, 2, 3, 4, 5]);
    }

    public function getName() : string{
        return TextFormat::BOLD.TextFormat::GOLD."Ancient Spider";
    }

    public function interactTarget() : bool{
        if(!parent::interactTarget()){
            return false;
        }
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
			if($player->isOnline()){
			    $this->teleport(new Vector3($player->getLocation()->x, $player->getLocation()->y, $player->getLocation()->z));
			}
		}
        if($this->interactDelay >= 10){			
            $this->broadcastAnimation(new ArmSwingAnimation($this));
            $target = $this->getTargetEntity();
            $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getResultDamage());
            $target->attack($ev);
            if(rand(1, 20) <= 15){   
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
				    if($player->isOnline()){
					    $x = (int)$player->getLocation()->x;
					    $y = (int)$player->getLocation()->y;
					    $z = (int)$player->getLocation()->z;
				        //$this->teleport(new Vector3($x, $y, $z));
					    $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getResultDamage() * 15);
                        $player->attack($ev);
					}
					if($player->getWorld()->getFolderName() == $this->getWorld()->getFolderName()){
						$from = new Vector3($this->getLocation()->x, $this->getLocation()->y + $this->getEyeHeight() + 3, $this->getLocation()->z);
						$to = new Vector3($player->getLocation()->x, $player->getLocation()->y + $player->getEyeHeight(), $player->getLocation()->z);					    
						$distance = sqrt(pow($from->x - $to->x, 2) + pow($from->y - $to->y, 2) + pow($from->z - $to->z, 2));
						$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(0.2);
						for($i = 0; $i <= $distance; $i += 0.2){
							$from = $from->add($vector->x, $vector->y, $vector->z);								
			                $this->getWorld()->addParticle($from, new DustParticle(new Color(237, 28, 36)), $this->getWorld()->getPlayers());
					    }
					}
					$instance = new EffectInstance(StringToEffectParser::getInstance()->parse("blindness"), 100, 1);
		            $player->getEffects()->add($instance);
					$player->sendMessage(TextFormat::BOLD.TextFormat::RED."BOSS > ".$player->getName()." XEM CHIÊU VẾT CẮN CHẾT NGƯỜi CỦA TA ĐÂY!");
				    $pos = $this->getPosition();
		            $up = $pos->getWorld()->getBlock($pos->getSide(Facing::UP));
		            if($up->getId() === BlockLegacyIds::AIR or $up instanceof Liquid or $up instanceof Fire){
			            $pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
			            $fall = new FallingBlock(Location::fromObject($pos->add(0.5, 2, 0.5), $pos->getWorld()), VanillaBlocks::COBWEB());
			            $fall->spawnToAll();
						$pos->getWorld()->setBlock($fall->getPosition(), VanillaBlocks::AIR());
					}
				}				
			}else{
				foreach($this->getWorld()->getEntities() as $entity){	
                    $vector = new Vector3($this->getLocation()->x, $this->getLocation()->y, $this->getLocation()->z);
                    if($entity->getPosition()->distance($vector) <= 7){
					    if($entity instanceof Player){
							$from = new Vector3($this->getLocation()->x, $this->getLocation()->y + $this->getEyeHeight(), $this->getLocation()->z);
							$to = new Vector3($entity->getLocation()->x, $entity->getLocation()->y + $this->getEyeHeight(), $entity->getLocation()->z);					    
							$distance = sqrt(pow($from->x - $to->x, 2) + pow($from->y - $to->y, 2) + pow($from->z - $to->z, 2));
							$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(0.2);
							for($i = 0; $i <= $distance; $i += 0.2){
								$from = $from->add($vector->x, $vector->y, $vector->z);								
			                	$this->getWorld()->addParticle($from, new DustParticle(new Color(255, 127, 39)), $this->getWorld()->getPlayers());
					    	}
							$instance = new EffectInstance(StringToEffectParser::getInstance()->parse("blindness"), 100, 1);
		                    $entity->getEffects()->add($instance);
					        $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getResultDamage() * 25);
                            $entity->attack($ev);							
							$entity->sendMessage(TextFormat::BOLD.TextFormat::RED."BOSS > TIỆT CHIÊU NỌC ĐỘC ĐEN!");
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
            VanillaItems::STRING()->setCount(mt_rand(0, 100))
        ];

        if(rand(1, 100) <= 10){ //10%		    
            $drops[] = MyItem::getInstance()->getItem("GOLD'S_GOD", rand(1, 10));
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
        return 500;
    }
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
}