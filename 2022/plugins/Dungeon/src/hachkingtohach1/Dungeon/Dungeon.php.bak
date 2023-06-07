<?php

namespace hachkingtohach1\Dungeon;

use pocketmine\player\Player;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\DoorCrashSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\Location;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use hachkingtohach1\Dungeon\task\Scheduler;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\PlayerStats\PlayerStats;
use diduhless\parties\session\SessionFactory;
use leinne\pureentities\entity\dungeons\BossFloorOne;
use leinne\pureentities\entity\dungeons\DungeonEnderman;
use leinne\pureentities\entity\dungeons\DungeonSkeleton;
use leinne\pureentities\entity\dungeons\DungeonZombie;
use leinne\pureentities\entity\dungeons\BossFloorTwo;
use leinne\pureentities\entity\dungeons\DungeonPig;
use leinne\pureentities\entity\dungeons\DungeonIronZombie;
use leinne\pureentities\entity\dungeons\DungeonDrowned;

class Dungeon extends PluginBase implements Listener {
	
	public const WAITING = 0;
	public const RUNNING = 1;
	public const RESTARTING = 2;
	
	public const MAX_SLOTS = 5;
	public const MIN_SLOTS = 1;
	
	public const ERROR = TextFormat::BOLD.TextFormat::RED."Một lỗi gì đó đã xảy ra vui lòng báo lại cho Admin";
	public const YOU_ARE_IN_PARTY_OTHER = TextFormat::BOLD.TextFormat::RED."Bạn cần tạo Party với lệnh /party để tham gia dungeon";
	public const YOU_ARE_IN_GAME = TextFormat::BOLD.TextFormat::RED."Bạn đang trong Dungeon rồi!";
	public const YOU_NEED_IN_GAME = TextFormat::BOLD.TextFormat::RED."Bạn cần trong Dungeon để thực hiện hành động này!";
	public const WAITING_STARTING = TextFormat::BOLD.TextFormat::GOLD."Bắt đầu đếm ngược %time giây!";
	public const WAITING_MORE_TIP = TextFormat::BOLD.TextFormat::GOLD."Chờ thêm người chơi ".TextFormat::RED."%count".TextFormat::GREEN."/%max\n\n\n\n";
	public const WAITING_MORE = TextFormat::BOLD.TextFormat::GOLD."Bạn hãy chờ thêm người chơi để vào dungeon! Vui lòng đừng thoát khỏi thế giới này!";
	public const WAITING_MORE_MAP = TextFormat::BOLD.TextFormat::GOLD."Bạn hãy chờ có map trống rồi hãy thực hiện lại hành động này!";
	public const JOINED_DUNGEON = TextFormat::BOLD.TextFormat::GREEN."Bạn đã tham gia Dungeon!";
	public const LEAVE_DUNGEON = TextFormat::BOLD.TextFormat::RED."Bạn đã thoát khỏi Dungeon!";
	public const YOU_ARE_SPECTATOR = TextFormat::BOLD.TextFormat::AQUA."Bạn đang trong chế độ khán giả, hãy chờ 30s để trở về bình thường!";
	public const NOT_AVAILABLE_MAP = TextFormat::BOLD.TextFormat::RED."Hiện tại chưa có bất kỳ map trống, làm ơn chờ!";
	public const PLAYER_OPEN_DOOR = TextFormat::BOLD.TextFormat::AQUA."%player đã mở một cổng!";
	public const PLAYER_OPEN_CHEST = TextFormat::BOLD.TextFormat::AQUA."%player đã mở EnderChest được ".TextFormat::GRAY."x%count %item";
	public const IN_GAME_TIP = TextFormat::BOLD.TextFormat::GREEN."Người chơi: ".TextFormat::AQUA."%player".TextFormat::WHITE."\nThời gian trôi qua: ".TextFormat::GREEN."%time ".TextFormat::GREEN."\n%ip\n\n";
	public const IN_GAME_SPECTATOR_TIP = TextFormat::BOLD.TextFormat::GREEN."Người chơi: ".TextFormat::AQUA."%player".TextFormat::WHITE."\nThời gian trôi qua: ".TextFormat::GREEN."%time ".TextFormat::RED."\nBạn đang trong chế độ khán giả chờ %timespec giây!\n\n";
	public const RESTARTING_TIP = TextFormat::BOLD.TextFormat::RED."Đây là Dungeon sẽ khởi động lại sau %time giây\n\n\n\n";
	public const SERVER_IP = TextFormat::GOLD."foxcraft.zapto.org";
	public const PREFIX = TextFormat::WHITE."[".TextFormat::RED."Dungeon".TextFormat::WHITE."] ";
	
	public array $dungeons = [];	
	public array $interactedChest = [];
	public array $spectators = [];
	public array $restarting = [];
	public array $noCheckTp = [];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): Dungeon{
        return self::$instance;
    }

    public function onEnable() :void{
		$this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new Scheduler($this), 20);
		$this->registerData();
    }
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "dungeon"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."This is command for in-game!");
				return false;
			}
			$this->formDungeon($sender);
			return true;
        }
		if($command->getName() == "dgc"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."This is command for in-game!");
				return false;
			}
			if(isset($args[0])){
				if($this->inGame($sender)){
					$data = $this->getDungeonFromPlayer($sender);
					foreach($data["players"] as $players){
						$players->sendMessage(self::PREFIX.$sender->getName()." > ".implode(" ", $args));
					}
				}else{
					$sender->sendMessage(self::YOU_NEED_IN_GAME);
					return false;
				}
			}
			return true;
        }
		if($command->getName() == "dgl"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."This is command for in-game!");
				return false;
			}
			if($this->inGame($sender)){
				$data = $this->getDungeonFromPlayer($sender);
				$sender->sendMessage("---Danh sách---");
				foreach($data["players"] as $players){
					$sender->sendMessage(TextFormat::AQUA.$players->getName());
				}
				$sender->sendMessage("---------------");
			}else{
				$sender->sendMessage(self::YOU_NEED_IN_GAME);
				return false;
			}
			return true;
        }
		return false;
	}
	
	public function formDungeon(Player $player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
            $levelPlayer = PlayerStats::getInstance()->getLevel($player)[0];		
			switch($result){						
				case "0";	                  
					if((int) $levelPlayer < 5){
						$player->sendMessage("§l§cBạn phải level 5 để tham gia");
						break;
					}
					$this->findMap($player, "floor-1");
				break;
                case "1";	
                    if((int) $levelPlayer < 6){
						$player->sendMessage("§l§cBạn phải level 6 để tham gia");
						break;
					}				
					$this->findMap($player, "floor-2");
				break;			
                default:
                break;					
			}
		});
		$form->setTitle("§l§6Dungeon");
		$txt = 
			"§l§f• §6Chế độ Dungeon, phiên bản:§f 1.0.3\n\n".
			"§l§f• §c/dgc <câu chat> §bđể chat với mọi người trong Map Dungeon của bạn!\n\n".
			"§l§f• §c/dgl §bđể xem danh sách mọi người Map Dungeon của bạn\n\n".
			"§l§f• §aHãy chọn độ khó mà bạn phù hợp nhất\n\n"
		;
		$form->setContent($txt);
		$form->addButton("§l§cFLOOR 1",0,"textures/ui/realms_slot_check");
		$form->addButton("§l§cFLOOR 2",0,"textures/ui/realms_slot_check");
		$form->sendToPlayer($player);
		return $form;
	}
	
	public function loadMap(string $folderName) :?World{
		$DS = DIRECTORY_SEPARATOR;
		$path = $this->getServer()->getDataPath();		
        if(!file_exists($path."worlds".$DS.$folderName)){
			return null;    
		}
		
		$worldManager = $this->getServer()->getWorldManager();
		
        if(!$worldManager->isWorldGenerated($folderName)) return null;

        if($worldManager->isWorldLoaded($folderName)) {
            $worldManager->unloadWorld($worldManager->getWorldByName($folderName));
        }		
        $tarPath = $this->getDataFolder()."saves".$DS.$folderName.".tar.gz";
		$tar = new \PharData($tarPath);
        $tar->extractTo($path."worlds/".$folderName, null, true);
		$worldManager->loadWorld($folderName);
        $worldManager->getWorldByName($folderName)->setAutoSave(false);
		
        return $worldManager->getWorldByName($folderName);
    }
	
	public function findMap(Player $player, string $type) :bool{
		$session = SessionFactory::getSession($player);
        $party = $session->getParty();			
		// Check Session in Dungeon
        if($this->inGame($session->getPlayer())){
			$player->sendMessage(self::YOU_ARE_IN_GAME);
			return false;
		}	
		foreach($this->dungeons as $case => $data){
			if($data["type"] == $type){
			    if($data["status"] == self::WAITING){
				    if(count($data["players"]) < self::MAX_SLOTS){						
					    if(!$session->hasParty() or $session->isPartyLeader()){
						    $this->joinDungeon($player, $case);
							if($session->isPartyLeader()){
					            $i = 1;
					            if($party->isLeaderWorldTeleport()){
                                    foreach($party->getMembers() as $member){
                                        if($i <= 5 - count($data["players"]) and !$member->isPartyLeader()) {
                                            if($member->getPlayer()->isOnline()){
											    $this->joinDungeon($member->getPlayer(), $case);
											}
								            $i++;
										}
									}
								}
							}
						}
				        return true;
					}
					$i = 1;
				    $this->joinDungeon($player, $case);	
                    if($party->isLeaderWorldTeleport()){
                        foreach($party->getMembers() as $member){
                            if($i <= 5 and !$member->isPartyLeader()) {
                                if($member->getPlayer()->isOnline()){
								    $this->joinDungeon($member->getPlayer(), $case);
								}
								$i++;
							}
						}
					}									
					return true;
				}               
			}
		}
		$player->sendMessage(self::NOT_AVAILABLE_MAP);
		return false;
	}

	public function joinDungeon(Player $player, string $id){       
        $this->dungeons[$id]["players"][$player->getName()] = $player;	
        $player->sendMessage(self::WAITING_MORE);		
	    if(count($this->dungeons[$id]["players"]) >= self::MIN_SLOTS){
			foreach($this->dungeons[$id]["players"] as $players){
                if($players->isOnline()){				
                    $this->loadLevel($this->dungeons[$id]["world"]);				
                    $players->sendMessage(TextFormat::AQUA.$player->getName()." đã tham gia Dungeon ".count($this->dungeons[$id]["players"])."/".self::MAX_SLOTS);
				}
			}				
			$this->dungeons[$id]["timeStart"] = microtime(true);			
		}
	}
	
	public function leaveDungeon(Player $player){
		foreach($this->dungeons as $case => $data){
			if(isset($data["players"][$player->getName()])){
				foreach($data["players"] as $players){
					$players->sendMessage(TextFormat::RED.$player->getName()." đã thoát Dungeon");
				}
				unset($this->dungeons[$case]["players"][$player->getName()]);
			}
		}
		if(isset($this->spectators[$player->getName()])){
			unset($this->spectators[$player->getName()]);
		}
		if(isset($this->interactedChest[$player->getName()])){
			unset($this->interactedChest[$player->getName()]);
		}
		$player->sendMessage(self::LEAVE_DUNGEON);
	}
	
	public function resetMapDungeon(string $world){
		$this->loadMap($world);
	}
	
	public function checkEnd(){
		foreach($this->dungeons as $case => $data){
			if($data["status"] == self::RUNNING){
				if(count($this->dungeons[$case]["players"]) <= 0){
					$this->dungeons[$case]["status"] = self::RESTARTING;
				}
			}
			if($data["status"] == self::RESTARTING){
				if(!isset($this->restarting[$case])){
					$this->restarting[$case] = microtime(true);
				}
				$timeDiff = microtime(true) - $this->restarting[$case];
				if($timeDiff >= 60){
			        foreach($data["doors"] as $id => $door){
						$this->dungeons[$case]["doors"][$id]["opened"] = false;
					}
				    $this->dungeons[$case]["status"] = self::WAITING;
					$this->dungeons[$case]["score"] = 300;	
				    $this->resetMapDungeon($this->dungeons[$case]["world"]);
					unset($this->restarting[$case]);
				}
			}
		}
	}
	
	public function inGame(Player $player) :bool{
		if(!empty($this->getDungeonFromPlayer($player))){
			return true;
		}
		return false;
	}
	
	public function isSpectator(Player $player) :bool{
		if(isset($this->spectators[$player->getName()])){
			return true;
		}
		return false;
	}
	
	public function getDungeonFromPlayer(Player $player) :array{
		$result = [];
		foreach($this->dungeons as $case => $data){
			if(isset($data["players"][$player->getName()])){
				$result = $data;
			}
		}
		return $result;
	}
	
	public function spawnMobs(string $id){
		foreach($this->dungeons[$id]["mobs"] as $data){
			$position = explode(",", $data["position"]);
			$entityClasses = $data["mob"];
			$world = $this->getServer()->getWorldManager()->getWorldByName($this->dungeons[$id]["world"]);
			for($i = 1; $i <= $data["amount"]; $i++){
			    $vector3 = new Vector3($position[0] + rand(0, 2), $position[1], $position[2]  + rand(0, 2));
				//$vector3->y = $world->getHighestBlockAt($vector3->x, $vector3->z) - $position[1];
				$entity = new $entityClasses(Location::fromObject($vector3, $world));
                $entity->spawnToAll();
			}
		}
	}
	
	public function spawnBoss(string $id){
		$boss = $this->dungeons[$id]["boss"];
		$position = explode(",", $boss["position"]);
		$entityClasses = $boss["mob"];		
		$world = $this->getServer()->getWorldManager()->getWorldByName($this->dungeons[$id]["world"]);
		for($i = 1; $i <= $boss["amount"]; $i++){
			$vector3 = new Vector3($position[0], $position[1], $position[2]);
			$vector3->y = $world->getHighestBlockAt($vector3->x, $vector3->z) - $position[1] + 5;
			$entity = new $entityClasses(Location::fromObject($vector3, $world));			
            $entity->spawnToAll();
			$this->dungeons[$id]["idBoss"] = $entity->getId();
		}
	}
	
	public function spawnChest(string $id){
		$chest = $this->dungeons[$id]["chest"];
		$position = explode(",", $chest["position"]);
		$world = $this->getServer()->getWorldManager()->getWorldByName($this->dungeons[$id]["world"]);
		$world->setBlockAt($position[0], $position[1], $position[2], VanillaBlocks::ENDER_CHEST());
	}
	
	public function sendMusic(Player $player){
		$pk = new PlaySoundPacket;
        $pk->soundName = "dungeon";
        $pk->x = (float)$player->getLocation()->x;
        $pk->y = (float)$player->getLocation()->y;
        $pk->z = (float)$player->getLocation()->z;
        $pk->volume = 5.0;
        $pk->pitch = 1.0;
        $player->getNetworkSession()->sendDataPacket($pk);
	}
	
	public function onDamageEvent(EntityDamageEvent $event){
		$entity = $event->getEntity();		
		if($event->getFinalDamage() >= $entity->getHealth()){			
            if($entity instanceof Player){
                foreach($this->dungeons as $case => $data){
			        if(isset($data["players"][$entity->getName()]) and $data["status"] == self::RUNNING){
						$event->cancel();		
			    		$entity->sendTitle(TextFormat::RED."Bạn đã chết! :(");
						$entity->setHealth($entity->getMaxHealth());	
						$entity->getWorld()->addSound($entity->getLocation()->asVector3(), new AnvilFallSound(), [$entity]);			    
			    		$this->spectators[$entity->getName()] = [
			        		"player" => $entity,
				    		"time" => microtime(true),
				    		"position" => (int)$entity->getLocation()->x.",".(int)$entity->getLocation()->y.",".(int)$entity->getLocation()->z,
							"world" => $entity->getWorld()
			    		];	
						$entity->setGamemode(GameMode::SPECTATOR());
						$entity->sendMessage(self::YOU_ARE_SPECTATOR);
						$this->dungeons[$case]["score"] -= 2;                        						
					}
				}				
			}
		}			
	}
	
	public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event){    
        $entity = $event->getEntity();    
		$damager = $event->getDamager();
		if($entity instanceof Player and $damager instanceof Player){
			if($this->inGame($entity) or $this->inGame($damager)){
				$event->cancel();
			}
		}
	}
	
	public function onEntityDeathEvent(EntityDeathEvent $event){
	    $entity = $event->getEntity();
		$world = $entity->getWorld();
		foreach($this->dungeons as $case => $data){
			if($data["status"] == self::RUNNING){
				if($world->getFolderName() == $data["world"]){
					if($data["idBoss"] != null){
						if($data["idBoss"] == $entity->getId()){
							foreach($data["players"] as $player){
								if($player->isOnline()){
									$timeDiff = microtime(true) - $data["time"];									
									$score = $data["score"];
                                    if($score >= 300){
										$xp = $data["xp"] + rand(800, 1000);
									}else{
										$xp = $data["xp"];
									}	
                                    $player->sendTitle($this->caculateScore((int)$score), ">>>>>\o/<<<<<");									
									$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬");
									$player->sendMessage(TextFormat::RED."Hầm mộ ".TextFormat::GRAY." - ".TextFormat::YELLOW.$data["type"]);
									$player->sendMessage(TextFormat::WHITE."Điểm số: ".TextFormat::GREEN.$score.TextFormat::GRAY." (".$this->caculateScore((int)$score).TextFormat::GRAY.")");
									$player->sendMessage(TextFormat::RED."Bạn đã dọn sạch Dungeon! Trong thời gian: ".TextFormat::BOLD.TextFormat::AQUA. gmdate("H:i:s", $timeDiff));
									$player->sendMessage("");
									$player->sendMessage(TextFormat::GREEN."Kinh nghiệm: +".TextFormat::BOLD.TextFormat::GOLD.$xp);
									$player->sendMessage(TextFormat::BOLD.TextFormat::AQUA."Chạm vào rương Ender để mở phần thưởng của bạn nào!");
									$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬ ▬");
									PlayerStats::getInstance()->checkLevel($player, $xp);
								}
							}
							$this->spawnChest($case);
							$this->dungeons[$case]["status"] = self::RESTARTING;                            						
						}
					}
				}
			}
		}
	}
	
	public function caculateScore(int $score) :string{
		$result = TextFormat::BOLD.TextFormat::RED."D";
		if($score >= 300){
			$result = TextFormat::BOLD.TextFormat::YELLOW."S+";
		}
		if($score >= 270 and $score <= 299){
			$result = TextFormat::BOLD.TextFormat::YELLOW."S";
		}
		if($score >= 230 and $score <= 269){
			$result = TextFormat::BOLD.TextFormat::LIGHT_PURPLE."A";
		}
		if($score >= 160 and $score <= 229){
			$result = TextFormat::BOLD.TextFormat::GOLD."B";
		}
		if($score >= 100 and $score <= 159){
			$result = TextFormat::BOLD.TextFormat::GOLD."C";
		}
		return $result;
	}
	
	public function onEntityTeleportEvent(EntityTeleportEvent $event){
	    $entity = $event->getEntity();	
		$world = $event->getTo()->getWorld();
        $from = $event->getFrom();		
		if($entity instanceof Player){
			if(isset($this->noCheckTp[$entity->getName()])){
				unset($this->noCheckTp[$entity->getName()]);
				return;
			}
			if($from->getWorld()->getFolderName() != $world->getFolderName()){
			    if($this->inGame($entity)){
				    foreach($this->dungeons as $case => $data){
					    if(isset($data["players"][$entity->getName()])){
				            if($entity->getWorld()->getFolderName() != $data["world"]){
							    $this->leaveDungeon($entity);
							}
						}
					}
				}
			}
		}
	}
	
	public function onPlayerQuitEvent(PlayerQuitEvent $event){
	    $player = $event->getPlayer();
		if($this->inGame($player)){
			foreach($this->dungeons as $case => $data){
				if(isset($data["players"][$player->getName()])){
					$this->leaveDungeon($player);
				}
			}
		}
	}
	
	public function onPlayerInteractEvent(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$locationBlock = $block->getPosition();
		$worldPlayer = $player->getWorld();
		foreach($this->dungeons as $case => $dungeon){
		    if($this->inGame($player) and $worldPlayer->getFolderName() == $dungeon["world"]){
			    foreach($dungeon["doors"] as $id => $door){
					$xd = explode(",", $door["lever"])[0];
					$yd = explode(",", $door["lever"])[1];
					$zd = explode(",", $door["lever"])[2];
				    if((int)$locationBlock->x == $xd and 
					    (int)$locationBlock->y == $yd and
						(int)$locationBlock->z == $zd
					){
						$pos1 = explode(",", $door["position"][1]);
						$pos2 = explode(",", $door["position"][2]);
						$minX = (int)min($pos1[0], $pos2[0]);
						$maxX = (int)max($pos1[0], $pos2[0]);
						$minY = (int)min($pos1[1], $pos2[1]);
						$maxY = (int)max($pos1[1], $pos2[1]);
						$minZ = (int)min($pos1[2], $pos2[2]);
						$maxZ = (int)max($pos1[2], $pos2[2]);

		                //$minY = min(World::Y_MAX - 1, max(World::Y_MIN, $minY));
		                //$maxY = min(World::Y_MAX - 1, max(World::Y_MIN, $maxY));
                        if($door["opened"] == false){
						    for($x = $minX; $x <= $maxX; ++$x){
				                for($z = $minZ; $z <= $maxZ; ++$z){
					                for($y = $minY; $y <= $maxY; ++$y){
						                $worldPlayer->setBlockAt($x, $y, $z, VanillaBlocks::AIR());						
								    }
							    }
						    }
						    $player->getWorld()->addSound($player->getLocation()->asVector3(), new DoorCrashSound(), $player->getWorld()->getPlayers());
					        foreach($dungeon["players"] as $players){
							    $players->sendMessage(str_replace("%player", $player->getName(), self::PLAYER_OPEN_DOOR));
						    }
							$this->dungeons[$case]["doors"][$id]["opened"] = true;
						}
					}
				}
				$chest = $dungeon["chest"];
				$xc = explode(",", $chest["position"])[0];
			    $yc = explode(",", $chest["position"])[1];
			    $zc = explode(",", $chest["position"])[2];
				if($block->getId() == 130){
					$event->cancel();
					if(
					    (int)$locationBlock->x == $xc and
						(int)$locationBlock->y == $yc and
						(int)$locationBlock->z == $zc
					){
						if(!isset($this->interactedChest[$player->getName()])){							
							$items = [];
							foreach($chest["items"] as $case => $data){
								for($i = 1; $i <= $data[0]; $i++){
									$items[] = $data[1];
								}
							}
							$item = $items[array_rand($items, 1)];
							if(!$player->getInventory()->canAddItem($item)){
								$player->sendMessage(TextFormat::RED."Túi đồ của bạn chưa trống!");
								return;
							}
							if(!empty($items)){
							    $player->getInventory()->addItem($item);
							}
							foreach($dungeon["players"] as $players){
								$arrayA = ["%player", "%item", "%count"];
							    if($item->getCustomName() != ""){
									$arrayB = [$player->getName(), $item->getCustomName(), $item->getCount()];
								    $players->sendMessage(str_replace($arrayA, $arrayB, self::PLAYER_OPEN_CHEST));
								}else{
									$arrayB = [$player->getName(), $item->getName(), $item->getCount()];
									$players->sendMessage(str_replace($arrayA, $arrayB, self::PLAYER_OPEN_CHEST));
								}
							}
							$player->getWorld()->addSound($player->getLocation()->asVector3(), new ChestOpenSound(), $player->getWorld()->getPlayers());
							$this->interactedChest[$player->getName()] = $player;
						}
					}					
				}
			}
		}
	}
	
	public function onPlaceBlockEvent(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($this->inGame($player)){
			$event->cancel();
		}
	}
	
	public function onBreakBlockEvent(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($this->inGame($player)){
			$event->cancel();
		}
	}
	
	public function loadLevel(string $world){
		if(!$this->getServer()->getWorldManager()->isWorldLoaded($world)){
            $this->getServer()->getWorldManager()->loadWorld($world);
		}
	}
	
	private function registerData(){
		$this->dungeons = [
		    "mc-091" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "366,13,236",
							2 => "358,5,237"
						],
						"lever" => "369,6,235",
						"opened" => false
					],		
                    2 => [
				    	"position" => [
					    	1 => "350,5,246",
							2 => "349,13,242"
						],
						"lever" => "351,6,249",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "334,5,249",
							2 => "333,13,245"
						],
						"lever" => "335,6,243",
						"opened" => false 
					]					
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "282,2,223",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonEnderman::class,
				    	"position" => "295,2,225",
				    	"amount" => 2
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "282,5,234",
				    	"amount" => 10
					],
					4 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,5,222",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,11,343",
				    	"amount" => 10
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "342,11,244",
				    	"amount" => 10
					],
					7 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "315,15,222",
				    	"amount" => 10
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,15,228",
				    	"amount" => 10
					],
					9 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,15,225",
				    	"amount" => 10
					],
					10 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "353,15,248",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorOne::class,
					"position" => "324,5,247",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)],
						3 => [1, MyItem::getInstance()->getItem("BONEMERANG", 1)],
					],
			    	"position" => "315,6,248"
				],
				"world" => "mc-091",
				"spawn" => "297,5,241",
				"type" => "floor-1",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 1000),
				"score" => 300
			],
            ///
            "mc-092" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "366,13,236",
							2 => "358,5,237"
						],
						"lever" => "369,6,235",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "350,5,246",
							2 => "349,13,242"
						],
						"lever" => "351,6,249",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "334,5,249",
							2 => "333,13,245"
						],
						"lever" => "335,6,243",
						"opened" => false 
					]					
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "282,2,223",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonEnderman::class,
				    	"position" => "295,2,225",
				    	"amount" => 2
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "282,5,234",
				    	"amount" => 10
					],
					4 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,5,222",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,11,343",
				    	"amount" => 10
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "342,11,244",
				    	"amount" => 10
					],
					7 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "315,15,222",
				    	"amount" => 10
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,15,228",
				    	"amount" => 10
					],
					9 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,15,225",
				    	"amount" => 10
					],
					10 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "353,15,248",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorOne::class,
					"position" => "324,5,247",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)],
						3 => [1, MyItem::getInstance()->getItem("BONEMERANG", 1)],
					],
			    	"position" => "315,6,248"
				],
				"world" => "mc-092",
				"spawn" => "297,5,241",
				"type" => "floor-1",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 1000),
				"score" => 300
			],
            ///
            "mc-093" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "366,13,236",
							2 => "358,5,237"
						],
						"lever" => "369,6,235",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "350,5,246",
							2 => "349,13,242"
						],
						"lever" => "351,6,249",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "334,5,249",
							2 => "333,13,245"
						],
						"lever" => "335,6,243",
						"opened" => false 
					]					
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "282,2,223",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonEnderman::class,
				    	"position" => "295,2,225",
				    	"amount" => 2
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "282,5,234",
				    	"amount" => 10
					],
					4 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,5,222",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,11,343",
				    	"amount" => 10
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "342,11,244",
				    	"amount" => 10
					],
					7 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "315,15,222",
				    	"amount" => 10
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,15,228",
				    	"amount" => 10
					],
					9 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,15,225",
				    	"amount" => 10
					],
					10 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "353,15,248",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorOne::class,
					"position" => "324,5,247",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)],
						3 => [1, MyItem::getInstance()->getItem("BONEMERANG", 1)],
					],
			    	"position" => "315,6,248"
				],
				"world" => "mc-093",
				"spawn" => "297,5,241",
				"type" => "floor-1",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 1000),
				"score" => 300
			],
            ///
            "mc-094" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "366,13,236",
							2 => "358,5,237"
						],
						"lever" => "369,6,235",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "350,5,246",
							2 => "349,13,242"
						],
						"lever" => "351,6,249",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "334,5,249",
							2 => "333,13,245"
						],
						"lever" => "335,6,243",
						"opened" => false 
					]					
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "282,2,223",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonEnderman::class,
				    	"position" => "295,2,225",
				    	"amount" => 2
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "282,5,234",
				    	"amount" => 10
					],
					4 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,5,222",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,11,343",
				    	"amount" => 10
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "342,11,244",
				    	"amount" => 10
					],
					7 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "315,15,222",
				    	"amount" => 10
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,15,228",
				    	"amount" => 10
					],
					9 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,15,225",
				    	"amount" => 10
					],
					10 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "353,15,248",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorOne::class,
					"position" => "324,5,247",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)],
						3 => [1, MyItem::getInstance()->getItem("BONEMERANG", 1)],
					],
			    	"position" => "315,6,248"
				],
				"world" => "mc-094",
				"spawn" => "297,5,241",
				"type" => "floor-1",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 1000),
				"score" => 300
			],
            ///
            "mc-095" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "366,13,236",
							2 => "358,5,237"
						],
						"lever" => "369,6,235",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "350,5,246",
							2 => "349,13,242"
						],
						"lever" => "351,6,249",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "334,5,249",
							2 => "333,13,245"
						],
						"lever" => "335,6,243",
						"opened" => false 
					]					
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "282,2,223",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonEnderman::class,
				    	"position" => "295,2,225",
				    	"amount" => 2
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "282,5,234",
				    	"amount" => 10
					],
					4 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,5,222",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,11,343",
				    	"amount" => 10
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "342,11,244",
				    	"amount" => 10
					],
					7 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "315,15,222",
				    	"amount" => 10
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "328,15,228",
				    	"amount" => 10
					],
					9 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "362,15,225",
				    	"amount" => 10
					],
					10 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "353,15,248",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorOne::class,
					"position" => "324,5,247",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)],
						3 => [1, MyItem::getInstance()->getItem("BONEMERANG", 1)],
					],
			    	"position" => "315,6,248"
				],
				"world" => "mc-095",
				"spawn" => "297,5,241",
				"type" => "floor-1",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 1000),
				"score" => 300
			],
            ///
            "br-001" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "170,17,168",
							2 => "171,10,173"
						],
						"lever" => "174,11,191",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "190,12,172",
							2 => "190,9,174"
						],
						"lever" => "191,3,172",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "233,11,171",
							2 => "233,7,174"
						],
						"lever" => "245,16,187",
						"opened" => false 
					],
                    4 => [
				    	"position" => [
					    	1 => "239,15,194",
							2 => "235,10,194"
						],
						"lever" => "242,13,198",
						"opened" => false 
					]							
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "237,30,231",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "240,14,207",
				    	"amount" => 5
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "237,15,198",
				    	"amount" => 5
					],
					4 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "237,10,183",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "236,15,173",
				    	"amount" => 2
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "195,15,173",
				    	"amount" => 5
					],
					7 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "180,17,170",
				    	"amount" => 5
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "183,15,174",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorTwo::class,
					"position" => "151,11,174",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("JOYEUSE_SWORD", 1)],
						3 => [1, MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)],
					],
			    	"position" => "150,12,169"
				],
				"world" => "br-001",
				"spawn" => "233,29,236",
				"type" => "floor-2",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 2000),
				"score" => 300
			],
            ///
            "br-002" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "170,17,168",
							2 => "171,10,173"
						],
						"lever" => "174,11,191",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "190,12,172",
							2 => "190,9,174"
						],
						"lever" => "191,3,172",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "233,11,171",
							2 => "233,7,174"
						],
						"lever" => "245,16,187",
						"opened" => false 
					],
                    4 => [
				    	"position" => [
					    	1 => "239,15,194",
							2 => "235,10,194"
						],
						"lever" => "242,13,198",
						"opened" => false 
					]							
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "237,30,231",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "240,14,207",
				    	"amount" => 5
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "237,15,198",
				    	"amount" => 5
					],
					4 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "237,10,183",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "236,15,173",
				    	"amount" => 2
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "195,15,173",
				    	"amount" => 5
					],
					7 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "180,17,170",
				    	"amount" => 5
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "183,15,174",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorTwo::class,
					"position" => "151,11,174",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("JOYEUSE_SWORD", 1)],
						3 => [1, MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)],
					],
			    	"position" => "150,12,169"
				],
				"world" => "br-002",
				"spawn" => "233,29,236",
				"type" => "floor-2",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 2000),
				"score" => 300
			],
            ///
            "br-003" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "170,17,168",
							2 => "171,10,173"
						],
						"lever" => "174,11,191",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "190,12,172",
							2 => "190,9,174"
						],
						"lever" => "191,3,172",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "233,11,171",
							2 => "233,7,174"
						],
						"lever" => "245,16,187",
						"opened" => false 
					],
                    4 => [
				    	"position" => [
					    	1 => "239,15,194",
							2 => "235,10,194"
						],
						"lever" => "242,13,198",
						"opened" => false 
					]							
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "237,30,231",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "240,14,207",
				    	"amount" => 5
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "237,15,198",
				    	"amount" => 5
					],
					4 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "237,10,183",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "236,15,173",
				    	"amount" => 2
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "195,15,173",
				    	"amount" => 5
					],
					7 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "180,17,170",
				    	"amount" => 5
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "183,15,174",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorTwo::class,
					"position" => "151,11,174",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("JOYEUSE_SWORD", 1)],
						3 => [1, MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)],
					],
			    	"position" => "150,12,169"
				],
				"world" => "br-003",
				"spawn" => "233,29,236",
				"type" => "floor-2",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 2000),
				"score" => 300
			],
            ///
            "br-004" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "170,17,168",
							2 => "171,10,173"
						],
						"lever" => "174,11,191",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "190,12,172",
							2 => "190,9,174"
						],
						"lever" => "191,3,172",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "233,11,171",
							2 => "233,7,174"
						],
						"lever" => "245,16,187",
						"opened" => false 
					],
                    4 => [
				    	"position" => [
					    	1 => "239,15,194",
							2 => "235,10,194"
						],
						"lever" => "242,13,198",
						"opened" => false 
					]							
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "237,30,231",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "240,14,207",
				    	"amount" => 5
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "237,15,198",
				    	"amount" => 5
					],
					4 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "237,10,183",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "236,15,173",
				    	"amount" => 2
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "195,15,173",
				    	"amount" => 5
					],
					7 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "180,17,170",
				    	"amount" => 5
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "183,15,174",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorTwo::class,
					"position" => "151,11,174",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("JOYEUSE_SWORD", 1)],
						3 => [1, MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)],
					],
			    	"position" => "150,12,169"
				],
				"world" => "br-004",
				"spawn" => "233,29,236",
				"type" => "floor-2",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 2000),
				"score" => 300
			],
            ///
            "br-005" => [
		    	"doors" => [
			    	1 => [
				    	"position" => [
					    	1 => "170,17,168",
							2 => "171,10,173"
						],
						"lever" => "174,11,191",
						"opened" => false 
					],		
                    2 => [
				    	"position" => [
					    	1 => "190,12,172",
							2 => "190,9,174"
						],
						"lever" => "191,3,172",
						"opened" => false 
					],		
                    3 => [
				    	"position" => [
					    	1 => "233,11,171",
							2 => "233,7,174"
						],
						"lever" => "245,16,187",
						"opened" => false 
					],
                    4 => [
				    	"position" => [
					    	1 => "239,15,194",
							2 => "235,10,194"
						],
						"lever" => "242,13,198",
						"opened" => false 
					]							
				],	
            	"mobs" => [
			    	1 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "237,30,231",
				    	"amount" => 5
					],
					2 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "240,14,207",
				    	"amount" => 5
					],
					3 => [
				    	"mob" => DungeonSkeleton::class,
				    	"position" => "237,15,198",
				    	"amount" => 5
					],
					4 => [
				    	"mob" => DungeonDrowned::class,
				    	"position" => "237,10,183",
				    	"amount" => 5
					],
					5 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "236,15,173",
				    	"amount" => 2
					],
					6 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "195,15,173",
				    	"amount" => 5
					],
					7 => [
				    	"mob" => DungeonIronZombie::class,
				    	"position" => "180,17,170",
				    	"amount" => 5
					],
					8 => [
				    	"mob" => DungeonZombie::class,
				    	"position" => "183,15,174",
				    	"amount" => 10
					]
				],			
				"boss" => [
			    	"mob" => BossFloorTwo::class,
					"position" => "151,11,174",
					"amount" => 1
				],
				"chest" => [
			    	"items" => [
				        1 => [25, $this->getDataItem(57, 0, 64)],
						2 => [1, MyItem::getInstance()->getItem("JOYEUSE_SWORD", 1)],
						3 => [1, MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)],
					],
			    	"position" => "150,12,169"
				],
				"world" => "br-005",
				"spawn" => "233,29,236",
				"type" => "floor-2",
				"players" => [],
				"status" => self::WAITING,
				"idBoss" => null,
				"time" => microtime(true),
				"timeStart" => microtime(true),
				"xp" => rand(500, 2000),
				"score" => 300
			]				
		];
	}
}