<?php

declare(strict_types=1);

namespace hachkingtohach1\Quest;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent; 
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\ConsoleCommandSender;
use hachkingtohach1\Quest\provider\{DataBase, sql\SQL};
use hachkingtohach1\Quest\task\SendMessage;
use leinne\pureentities\entity\neutral\IronGolem;
use leinne\pureentities\entity\neutral\ZombifiedPiglin;
use leinne\pureentities\entity\neutral\Spider;
use leinne\pureentities\entity\passive\Chicken;
use leinne\pureentities\entity\passive\Cow;
use leinne\pureentities\entity\passive\Mooshroom;
use leinne\pureentities\entity\passive\Pig;
use leinne\pureentities\entity\passive\Sheep;
use leinne\pureentities\entity\hostile\Creeper;
use leinne\pureentities\entity\hostile\Skeleton;
use leinne\pureentities\entity\hostile\Zombie;
use leinne\pureentities\entity\passive\SnowGolem;
use leinne\pureentities\entity\vehicle\Boat;
use leinne\pureentities\entity\other\God;
use leinne\pureentities\entity\other\SpecialEnderman;
use leinne\pureentities\entity\other\SpecialGolem;
use leinne\pureentities\entity\other\SpecialZombie;
use leinne\pureentities\entity\dungeons\BossFloorOne;
use leinne\pureentities\entity\dungeons\DungeonEnderman;
use leinne\pureentities\entity\dungeons\DungeonSkeleton;
use leinne\pureentities\entity\dungeons\DungeonZombie;
use leinne\pureentities\entity\dungeons\BossFloorTwo;
use leinne\pureentities\entity\dungeons\DungeonPig;
use leinne\pureentities\entity\dungeons\DungeonIronZombie;
use leinne\pureentities\entity\dungeons\DungeonDrowned;

class Quest extends PluginBase implements Listener{
	
	CONST BREAK = 0;
	CONST PLACE = 1;
	CONST FIND_NPC = 2;
	CONST TRADE_ITEM = 3;
	CONST KILL_MOB = 4;
	CONST REACH_LEVEL = 5;
	CONST GO_WORLD = 6;
	
	private array $placeBlock = [];
	private array $checkDeath = [];
	public array $sendMessage = [];
	
	private $dataBase;	
	private static $instance;

	public function onLoad(){
        self::$instance = $this;
	}
	
    public static function getInstance(): Quest{
        return self::$instance;
    }

	public function onEnable() :void{
		$this->saveDefaultConfig();
		$this->dataBase = new SQL("mysql");    
        $this->getScheduler()->scheduleRepeatingTask(new SendMessage($this), 20);		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}	
	
	public function getDatabase(): Database{
        return $this->dataBase;
	}
	
	public function getQuest(Player $player) :int{
		return (int)$this->getDatabase()->getQuest($player);
	}
	
	public function getStatus(Player $player) :int{
		return (int)$this->getDatabase()->getStatus($player);
	}
	
	public function setQuest(Player $player, float $amount){
		return $this->getDatabase()->setQuest($player, $amount);
	}
	
	public function addStatus(Player $player, float $amount){
		return $this->getDatabase()->addStatus($player, $amount);
	}
	
	public function setStatus(Player $player, float $amount){
		return $this->getDatabase()->setStatus($player, $amount);
	}
	
	public function getQuestPlayer(Player $player) :string{
		if(!empty($this->getQuest($player))){
		    if($this->getQuest($player) >= $this->getConfig()->get("MAXQUEST")){
				$quest = $this->getConfig()->get("ALL_QUESTS_DONE");
			}elseif($this->getQuest($player) >= $this->getConfig()->get("MAXQUEST")){
				$process = "";
			}else{
			    $quest = $this->getConfig()->get("QUESTS")[$this->getQuest($player)]["NAME"];
			}
		}else{
			$quest = "NONE";
		}
		return $quest;
	}
	
	public function getProcessQuestPlayer(Player $player) :int{		
		$process = 0;
		if(!empty($this->getQuest($player))){
			$quest = $this->getConfig()->get("QUESTS")[$this->getQuest($player)];
		    $process = 0;
		    if(!empty($quest["COUNT"])){
			    $process = ($this->getStatus($player)/$quest["COUNT"])*100;
			}else{
			    $process = 0;
			}
			if($this->getQuest($player) >= $this->getConfig()->get("MAXQUEST")){
				$process = $this->getConfig()->get("ALL_QUESTS_DONE");
			}
		}
		if($process >= 100){
			$process = 100;
		}
		return (int)$process;
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function rewardQuest(Player $player, array $cmds){
		foreach($cmds as $cmd){
			Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), str_replace("%player", $player, $cmd));
		}
	}
	
	public function checkQuest(Player $player, $event) :bool{
		if($this->getQuest($player) >= $this->getConfig()->get("MAXQUEST")){
			return true;
		}
		$quest = $this->getConfig()->get("QUESTS")[$this->getQuest($player)];
		if($quest["EVENT"] == "break" and $event instanceof BlockBreakEvent){			
			if($event->getBlock()->getId() == $quest["ID"] and $event->getBlock()->getMeta() == $quest["META"]){
			    if($quest["COUNT"] <= $this->getStatus($player)){
				    ///////////////////////////////
				}else{
					$this->addStatus($player, 1);
				}
			}
			return true;
		}
		if($quest["EVENT"] == "place" and $event instanceof BlockPlaceEvent){
			if($event->getBlock()->getId() == $quest["ID"] and $event->getBlock()->getMeta() == $quest["META"]){
			    if($quest["COUNT"] <= $this->getStatus($player)){
				    ///////////////////////////////
				}else{
					$this->addStatus($player, 1);
				}
			}
			return true;
		}
		if($quest["EVENT"] == "findnpc" and $event instanceof EntityDamageEvent and $event instanceof EntityDamageByEntityEvent){
			if($event->getEntity()->getNameTag() == $quest["TAGENTITY"]){							
                if($quest["COUNT"] > $this->getStatus($player)){
				    foreach($quest["SAY"] as $message){
					    $this->sendMessage[$player->getName()][] = $message;
					}				
				}
                $this->addStatus($player, 1);				
			}
			return true;
		}
		if($quest["EVENT"] == "tradeitem" and $event instanceof EntityDamageEvent and $event instanceof EntityDamageByEntityEvent){
			if($event->getEntity()->getNameTag() == $quest["TAGENTITY"]){
				$count = 0;
				foreach($player->getInventory()->getContents() as $case => $checkInventory){
				    foreach($quest["ITEMS"] as $name => $itemNeed){
					    if($checkInventory->getId() == $itemNeed["ID"] 
						    and $checkInventory->getMeta() == $itemNeed["META"] 
							and $checkInventory->getCount() >= $itemNeed["COUNT"]
						){
				            if(!empty($itemNeed["NAME"])){
								if($itemNeed["NAME"] == $checkInventory->getCustomName()){
									$count++;
								}
							}else{
								$count++;
							}
					    }
					}						
				}
                if($count >= count($quest["ITEMS"])){	
                    foreach($quest["ITEMS"] as $name => $itemNeed){
						foreach($player->getInventory()->getContents() as $case => $checkInventory){
							if(!empty($itemNeed["NAME"])){
							    if($itemNeed["NAME"] != false and $itemNeed["NAME"] == $checkInventory->getCustomName()){
								    $checkInventory->setCount($checkInventory->getCount() - $itemNeed["COUNT"]);
                                    $player->getInventory()->setItem($case, $checkInventory);
								}
							}else{
								$player->getInventory()->removeItem($this->getDataItem($itemNeed["ID"], $itemNeed["META"], $itemNeed["COUNT"])); 
							}
						}
					}				
                    if($quest["COUNT"] <= $this->getStatus($player)){
				        foreach($quest["SAY"] as $message){
					        $this->sendMessage[$player->getName()][] = $message;
						}					
					}else{
					    $this->addStatus($player, 1);
					}
				}else{
					$player->sendMessage($quest["FALSESAY"]);
				}					
			}
			return true;
		}	
		if($quest["EVENT"] == "kill"){			
			if($event instanceof EntityDamageEvent){
				$target = null;
				switch($quest["ENTITY"]){
					case "Zombie": $target = Zombie::class; break;
					case "Skeleton": $target = Skeleton::class; break;
					case "Creeper": $target = Creeper::class; break;
					case "SpecialZombie": $target = SpecialZombie::class; break;
					case "SpecialGolem": $target = SpecialGolem::class; break;
				}
			    if($target == null){
					return false;
				}
				if($event->getEntity() instanceof $target){
				    if($quest["COUNT"] <= $this->getStatus($player)){
				        foreach($quest["SAY"] as $message){
					        $this->sendMessage[$player->getName()][] = $message;
						}
					}else{
				        $this->addStatus($player, 1);
					}
				}
			}
			return true;
		}
		return true;
	}
	
	public function onPlayerJoinEvent(PlayerJoinEvent $event) :void{
		$player = $event->getPlayer();
		$this->sendMessage[$player->getName()] = [];
		$this->getDataBase()->createProfile($player);
	}
	
	public function onDamageEntityEvent(EntityDamageEvent $event) :void{		
        $entity = $event->getEntity();		     		       
		if($event->getFinalDamage() >= $entity->getHealth()){
			$this->checkQuest($entity, $event);
		}
	}
	
	public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event) :void{
		$entity = $event->getEntity();
		$damager = $event->getDamager();
		
	}
	
	public function onBlockBreak(BlockBreakEvent $event) :void{	
		$player = $event->getPlayer();
        $x = (int)$block->getPosistion()->x;
        $y = (int)$block->getPosistion()->y;
        $z = (int)$block->getPosistion()->z;		
		if(!$event->isCancelled()){
			if(isset($this->breakBlock[$player->getName()])){				
			    if(!isset($this->breakBlock[$player->getName()][$x.$y.$z])){
					$this->checkQuest($player, $event);
					$this->breakBlock[$player->getName()][$x.$y.$z] = $block;
				}					
			}else{
				$this->breakBlock[$player->getName()][$x.$y.$z] = $block;
				$this->checkQuest($player, $event);
			}
		}		
	}
	
	public function onBlockPlace(BlockPlaceEvent $event) :void{	
	    $block = $event->getBlock();
		$player = $event->getPlayer();
        $x = (int)$block->getPosistion()->x;
        $y = (int)$block->getPosistion()->y;
        $z = (int)$block->getPosistion()->z;		
		if(!$event->isCancelled()){
			if(isset($this->placeBlock[$player->getName()])){				
			    if(!isset($this->placeBlock[$player->getName()][$x.$y.$z])){
					$this->checkQuest($player, $event);
					$this->placeBlock[$player->getName()][$x.$y.$z] = $block;
				}					
			}else{
				$this->placeBlock[$player->getName()][$x.$y.$z] = $block;
				$this->checkQuest($player, $event);
			}
		}
	}
}