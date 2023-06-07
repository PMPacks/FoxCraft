<?php

namespace hachkingtohach1\MyItem;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Durable;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\{EntityFactory, EntityDataHelper};
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\Level;
use pocketmine\world\World;
use pocketmine\color\Color;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\Skin;
use hachkingtohach1\PlayerStats\PlayerStats;
use hachkingtohach1\MyItem\ability\Manager;
use hachkingtohach1\MyItem\task\Freeze;
use hachkingtohach1\MyItem\task\DarkFire;
use hachkingtohach1\MyItem\task\RemoveLaggy;
use hachkingtohach1\MyItem\task\OpenGUI;
use hachkingtohach1\MyItem\task\UpdateArmor;
use hachkingtohach1\MyItem\entity\Bubble;
use hachkingtohach1\MyItem\entity\Throww;
use hachkingtohach1\MyItem\entity\GiantSword;
use hachkingtohach1\MyItem\entity\Bonemerang;
use hachkingtohach1\MyItem\sounds\Sounds;
use hachkingtohach1\MyItem\rune\Rune;
use hachkingtohach1\MyItem\items\Items;
use hachkingtohach1\MyItem\armor\Bonus;
use skymin\InventoryLib\{InvLibManager, LibInvType, InvLibAction, LibInventory};

class MyItem extends PluginBase implements Listener {

    private const PREFIX = "[MYITEM]";
    private const KEY_MYITEM = "ability|register:item|register";
	public const KEY_BONUS = "bonus|register:item|register";
	private const ITEM_RATING_MODE = false;
	public const WEAPONS = "WEAPONS";
	public const ARMORS = "ARMORS";
	public const SPECIAL = "SPECIAL";
	public const OTHERS = "OTHERS";
	private static $instance = null;
	private array $items = [];
	private array $item = [];	
	public array $freeze = [];
	public array $darkFire = [];
	public array $haveBlock = [];
	public array $inAbility = [];
    public array $throw = [];
	public array $giantSword = [];
	public array $bonemerang = [];
    public array $curse = [];
	public array $deaths = [];
	public array $usedarkfire = [];
	public array $timeCountDown = [];
	public array $dataItems = [];
	public array $pageDataPlayer = [];
	public array $armorUsing = [];
	public $available;		
	public $modeCreateItem;
	public $antiLagg = false;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): MyItem{
        return self::$instance;
    }

    public function onEnable() :void{	
	    InvLibManager::register($this);
		$entityfactory = EntityFactory::getInstance();
		$entityfactory->register(Bubble::class, function(World $world, CompoundTag $nbt) : Bubble{
			return new Bubble(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Bubble']);
		$entityfactory->register(Throww::class, function(World $world, CompoundTag $nbt) : Throww{
			return new Throww(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Throww']);
		$entityfactory->register(GiantSword::class, function(World $world, CompoundTag $nbt) : GiantSword{
			return new GiantSword(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['GiantSword']);
		$entityfactory->register(Bonemerang::class, function(World $world, CompoundTag $nbt) : Bonemerang{
			return new Bonemerang(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Bonemerang']);
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder()."items/");
		$this->loadAllItems();
		$this->saveDefaultConfig();
		$customPath = $this->getDataFolder() . "customskill";
        @mkdir($customPath);
        foreach(scandir($customPath) as $file){
            if(!is_dir("$customPath/$file") && strtolower(explode(".", $file)[1]) === "php"){
                require_once "$customPath/$file";
                $className = explode(".", $file)[0];
                try{
                    $fullCheckName = "hachkingtohach1\\MyItem\\customskill\\$className";
                    $reflectionClass = new \ReflectionClass($fullCheckName);
                    if(!$reflectionClass->isAbstract() && $reflectionClass->isSubclassOf(self::class)){
                        $this->available[] = new $fullCheckName($reflectionClass->getShortName(), null);
                    }
                }catch(\ReflectionException $e){
                    $this->getLogger()->debug($e->getMessage());
                }
            }
        }
		$this->getScheduler()->scheduleRepeatingTask(new Freeze($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new DarkFire($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateArmor($this), 100);
		$this->getScheduler()->scheduleRepeatingTask(new RemoveLaggy($this), 500);
		new Manager($this);
		new Bonus($this);
		new Items($this);
		$count = 0;
		$page = 1;
		$items = [];	    
		if(!empty($this->getAllItems())){
			foreach($this->getAllItems() as $item){
				if($count <= 44){
				    $items[$page][] = $item;
					$count++;
				}
                if($count > 44){				    
					$page++;
					$count = 0;
				}
			}
		}
		$this->dataItems = $items;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		$nbt = CompoundTag::create()
	    ->setTag("Pos", new ListTag([
            new DoubleTag($pos->x),
			new DoubleTag($pos->y),
			new DoubleTag($pos->z)
		]))
		->setTag("Motion", new ListTag([
			new DoubleTag($motion !== null ? $motion->x : 0.0),
			new DoubleTag($motion !== null ? $motion->y : 0.0),
			new DoubleTag($motion !== null ? $motion->z : 0.0)
		]))
		->setTag("Rotation", new ListTag([
			new FloatTag($yaw),
			new FloatTag($pitch)
		]));
		return $nbt;
	}
	
	public function loadAllItems(){
		foreach(glob($this->getDataFolder()."items".DIRECTORY_SEPARATOR."*.yml") as $item) {
            $config = new Config($item, Config::YAML);
            $this->items[basename($item, ".yml")] = $config->getAll(\false);
		}
	}
	
	public function giveItem(Player $player, string $name, int $count){		
		$item = $this->getItem($name, $count);
		if($item != null){
			$player->getInventory()->addItem($item);
		}else{
			$player->sendMessage(TextFormat::RED."This is item don't exist!");
		}
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function getAllItems() :array{
		$items = [];
		$result = [];
		foreach(glob($this->getDataFolder()."items".DIRECTORY_SEPARATOR."*.yml") as $item) {
            $config = new Config($item, Config::YAML);
			$result[] = $config->getAll(\false);
		}
		if(!empty($result)){
			foreach($result as $data){
			    $item = $this->getDataItem($data["ID"], $data["META"], 1);		
	            $item->setCustomName($data["NAME"]);
		        $item->setLore($data["LORE"]);
		        $item->getNamedTag()->setString("Permission", $data["PERMISSION"]);
		        $item->getNamedTag()->setString("Ability", $data["ABILITY"]);
		        $item->getNamedTag()->setInt("Damagemodifier", $data["DAMAGE"]);
		        $item->getNamedTag()->setInt("Strength", $data["STRENGTH"]);
		        $item->getNamedTag()->setInt("Defense", $data["DEFENSE"]);
		        $item->getNamedTag()->setInt("Critchance", $data["CRIT_CHANCE"]);
		        $item->getNamedTag()->setInt("Critdamage", $data["CRIT_DAMAGE"]);
			    $item->getNamedTag()->setInt("Miningspeed", $data["MINING_SPEED"]);
			    $item->getNamedTag()->setInt("Attackspeed", $data["ATTACK_SPEED"]);
			    $item->getNamedTag()->setInt("Farmingfortune", $data["FARMING_FORTUNE"]);
			    $item->getNamedTag()->setInt("Miningfortune", $data["MINING_FORTUNE"]);
			    $item->getNamedTag()->setInt("Foragingfortune", $data["FORAGING_FORTUNE"]);
			    $item->getNamedTag()->setInt("Ferocity", $data["FEROCITY"]);
			    $item->getNamedTag()->setInt("Abilitydamage", $data["ABILITY_DAMAGE"]);
			    $item->getNamedTag()->setInt("Health", $data["HEALTH"]);
			    $item->getNamedTag()->setInt("Speed", $data["SPEED"]);
			    $item->getNamedTag()->setInt("Intelligence", $data["INTELLIGENCE"]);
			    $item->getNamedTag()->setString("Sounds", $data["SOUNDS"]);
			    $item->getNamedTag()->setInt("Rune", $data["RUNE"]);
			    $item->getNamedTag()->setInt("Mana", $data["MANA"]);
			    $item->getNamedTag()->setInt("Counter", $data["COUNTER"]);
                $item->getNamedTag()->setString("Category", $data["CATEGORY"]);
			    if(in_array($item->getId(), [298, 299, 300, 301])){
				    if(!empty($data["COLOR"])){
				        $item->setCustomColor(new Color($data["COLOR"][0], $data["COLOR"][1], $data["COLOR"][2]));
				    }
			    }
				$item->getNamedTag()->setInt("Rarity", $data["RARITY"]);
				if($data["CATEGORY"] == self::ARMORS){
				    $item->getNamedTag()->setString("Bonus", $data["BONUS"]);
				}
				$item->getNamedTag()->setInt("Timecountdown", $data["TIMECOUNTDOWN"]);
				$item->getNamedTag()->setString("Relative", $data["RELATIVE"]);
				if($item instanceof Durable) {
                    $item->setUnbreakable();
				}
                $items[] = $item;				
			}		           
		}
		return $items;	
	}
	
	public function getItem(string $name, int $count){		
		$result = [];
		foreach(glob($this->getDataFolder()."items".DIRECTORY_SEPARATOR."*.yml") as $item) {
            $config = new Config($item, Config::YAML);
            if(basename($item, ".yml") == $name){
			    $result = $config->getAll(\false);
			}
		}
		if(!empty($result)){
			$item = $this->getDataItem($result["ID"], $result["META"], $count);		
	        $item->setCustomName($result["NAME"]);
		    $item->setLore($result["LORE"]);
		    $item->getNamedTag()->setString("Permission", $result["PERMISSION"]);
			$item->getNamedTag()->setString("Ability", $result["ABILITY"]);
		    $item->getNamedTag()->setInt("Damagemodifier", $result["DAMAGE"]);
		    $item->getNamedTag()->setInt("Strength", $result["STRENGTH"]);
		    $item->getNamedTag()->setInt("Defense", $result["DEFENSE"]);
		    $item->getNamedTag()->setInt("Critchance", $result["CRIT_CHANCE"]);
		    $item->getNamedTag()->setInt("Critdamage", $result["CRIT_DAMAGE"]);
			$item->getNamedTag()->setInt("Miningspeed", $result["MINING_SPEED"]);
			$item->getNamedTag()->setInt("Attackspeed", $result["ATTACK_SPEED"]);
			$item->getNamedTag()->setInt("Farmingfortune", $result["FARMING_FORTUNE"]);
			$item->getNamedTag()->setInt("Miningfortune", $result["MINING_FORTUNE"]);
			$item->getNamedTag()->setInt("Foragingfortune", $result["FORAGING_FORTUNE"]);
			$item->getNamedTag()->setInt("Ferocity", $result["FEROCITY"]);
			$item->getNamedTag()->setInt("Abilitydamage", $result["ABILITY_DAMAGE"]);
			$item->getNamedTag()->setInt("Health", $result["HEALTH"]);
			$item->getNamedTag()->setInt("Speed", $result["SPEED"]);
			$item->getNamedTag()->setInt("Intelligence", $result["INTELLIGENCE"]);
			$item->getNamedTag()->setString("Sounds", $result["SOUNDS"]);
			$item->getNamedTag()->setInt("Rune", $result["RUNE"]);
			$item->getNamedTag()->setInt("Mana", $result["MANA"]);
			$item->getNamedTag()->setInt("Counter", $result["COUNTER"]);
            $item->getNamedTag()->setString("Category", $result["CATEGORY"]);
			if(in_array($item->getId(), [298, 299, 300, 301])){
				if(!empty($result["COLOR"])){
				    $item->setCustomColor(new Color($result["COLOR"][0], $result["COLOR"][1], $result["COLOR"][2]));
				}
			}
			$item->getNamedTag()->setInt("Rarity", $result["RARITY"]);
			if($result["CATEGORY"] == self::ARMORS){
				$item->getNamedTag()->setString("Bonus", $result["BONUS"]);
			}
			$item->getNamedTag()->setInt("Timecountdown", $result["TIMECOUNTDOWN"]);
			$item->getNamedTag()->setString("Relative", $result["RELATIVE"]);
			if($item instanceof Durable){
                $item->setUnbreakable();
			}
		    return $item;
		}else{			
			return 0;
		}
	}
	
	public function openMenuItems(Player $player){
		if(!isset($this->pageDataPlayer[$player->getName()])){
			$this->pageDataPlayer[$player->getName()] = 1;
		}	
        $menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Menu'); 		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$item = $action->getSourceItem();
			$player = $action->getPlayer();
			switch($item->getCustomName()){
            	case "-":
					$action->setCancelled();
            	break;	
            	case TextFormat::GREEN."Text Page":		
                    $action->setCancelled();				
					if($this->pageDataPlayer[$player->getName()] < count($this->dataItems)){
						$this->pageDataPlayer[$player->getName()] += 1;
						$player->removeCurrentWindow();	
					    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					}			
            	break;
            	case TextFormat::RED."Reverse Page":
				    $action->setCancelled();
					if($this->pageDataPlayer[$player->getName()] > 0){
						$this->pageDataPlayer[$player->getName()] -= 1;
						$player->removeCurrentWindow();	
					    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					}								
            	break;				
			}
		});		
		$menu->send($player, function() use ($menu, $player) : void{	            	
		    $nextPage = $this->getDataItem(262, 0, 1);
		    $nextPage->setCustomName(TextFormat::GREEN."Text Page");
		    $itemName = ["%page", "%maxpage"];
		    $array = [$this->pageDataPlayer[$player->getName()], count($this->dataItems)];
		
		    $loreItem = [];		
		    foreach([TextFormat::GREEN."%page/".TextFormat::RED."%maxpage", TextFormat::YELLOW."Click to next!"] as $lore){
			    $loreItem[] = str_replace($itemName, $array, $lore);
		    }		
		    $nextPage->setLore($loreItem);
		    $menu->setItem(8, $nextPage);
		
		    $loreItem = [];		
		    $reversePage = $this->getDataItem(262, 0, 1);
		    $reversePage->setCustomName(TextFormat::RED."Reverse Page");	
		    foreach([TextFormat::GREEN."%page/".TextFormat::RED."%maxpage", TextFormat::YELLOW."Click to reverse!"] as $lore){
			    $loreItem[] = str_replace($itemName, $array, $lore);
		    }
		    $reversePage->setLore($loreItem);
		    $menu->setItem(0, $reversePage);				
		
		    $null = $this->getDataItem(27, 0, 1);
		    $null->setCustomName("-");       						
		    for($slot = 1; $slot <= 7; $slot++){
			    $menu->setItem($slot, $null);
		    }
		    if(!empty($this->dataItems[$this->pageDataPlayer[$player->getName()]])){
		        foreach($this->dataItems[$this->pageDataPlayer[$player->getName()]] as $item){
			        $menu->addItem($item);
			    }
		    }		
        });			
	}
	
	public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        if(isset($this->modeCreateItem[$player->getName()])) {
            $key = ".";
			$event->cancel();
            $args = explode(" ", $event->getMessage());
            $item = $this->modeCreateItem[$player->getName()];
            if(!isset($this->item[$item[0]])){
				$this->setBasicData($item[0]);
				$this->item[$item[0]]["ID"] = $player->getInventory()->getItemInHand()->getId();
				$this->item[$item[0]]["META"] = $player->getInventory()->getItemInHand()->getMeta();
			}
			switch($args[0]){
				case $key."help":
				    $player->sendMessage(TextFormat::GOLD."-My Item Set Up Item-");
					//$player->sendMessage(TextFormat::YELLOW.$key."setitem <id> <meta>");
				    $player->sendMessage(TextFormat::YELLOW.$key."setname <name>".TextFormat::RED." (conscription)");
				    $player->sendMessage(TextFormat::YELLOW.$key."setcategory <name>".TextFormat::RED." (conscription)");
					$player->sendMessage(TextFormat::YELLOW.$key."setlore <line> <text>");
				    $player->sendMessage(TextFormat::YELLOW.$key."insertlore <line> <text>");
				    $player->sendMessage(TextFormat::YELLOW.$key."addlore <text>");
				    $player->sendMessage(TextFormat::YELLOW.$key."setdamage <damage>");
					$player->sendMessage(TextFormat::YELLOW.$key."setstrength <strength>");
				    $player->sendMessage(TextFormat::YELLOW.$key."setdefense <defense>");
				    $player->sendMessage(TextFormat::YELLOW.$key."setcritchance <chance>");
				    $player->sendMessage(TextFormat::YELLOW.$key."setcritdamage <damage>");
					$player->sendMessage(TextFormat::YELLOW.$key."setminingspeed <speed>");
					$player->sendMessage(TextFormat::YELLOW.$key."setattackspeed <speed>");
					$player->sendMessage(TextFormat::YELLOW.$key."setminingfortune <fortune>");
					$player->sendMessage(TextFormat::YELLOW.$key."setfarmingfortune <fortune>");
					$player->sendMessage(TextFormat::YELLOW.$key."setforagingfortune <fortune>");
					$player->sendMessage(TextFormat::YELLOW.$key."setferocity <ferocity>");
					$player->sendMessage(TextFormat::YELLOW.$key."setabilitydamage <damage>");
					$player->sendMessage(TextFormat::YELLOW.$key."sethealth <health>");
					$player->sendMessage(TextFormat::YELLOW.$key."setspeed <speed>");
					$player->sendMessage(TextFormat::YELLOW.$key."setintelligence <intelligence>");
					$player->sendMessage(TextFormat::YELLOW.$key."insertability <ability>");
					$player->sendMessage(TextFormat::YELLOW.$key."addability <ability>");
					$player->sendMessage(TextFormat::YELLOW.$key."setpermission <permission>");
				    $player->sendMessage(TextFormat::YELLOW.$key."addsound <sound>");
					$player->sendMessage(TextFormat::YELLOW.$key."insertsound <sound>");
					$player->sendMessage(TextFormat::YELLOW.$key."setcounter <counter>");
					$player->sendMessage(TextFormat::YELLOW.$key."setcolor <a> <b> <c>");	
                    $player->sendMessage(TextFormat::YELLOW.$key."setrarity <string>");	
                    $player->sendMessage(TextFormat::YELLOW.$key."settimecountdown <time>");						
					$player->sendMessage(TextFormat::GOLD."---------------------");
				break;
				case $key."setitem":
				    if(!isset($args[1]) or !isset($args[2])){
					    $player->sendMessage(TextFormat::GREEN.$key."setitem <int> <meta>");
					    break;
					}
				    $this->item[$item[0]]["ID"] = (int) $args[1];
					$this->item[$item[0]]["META"] = (int) $args[2];
					$player->sendMessage(TextFormat::GREEN."Data item was set!");
				break;
				case $key."setname":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setname <name>");
					    break;
					}
					array_shift($args);
				    $this->item[$item[0]]["NAME"] = trim(implode(" ", $args));
					$player->sendMessage(TextFormat::GREEN."Name item was set!");
				break;
				case $key."setcategory":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setcategory <name>");
					    break;
					}
					array_shift($args);
				    $this->item[$item[0]]["NAME"] = trim(implode(" ", $args));
					$player->sendMessage(TextFormat::GREEN."Category item was set!");
				break;
				case $key."setlore":
				    if(empty($args[1]) or empty($args[2])){
					    $player->sendMessage(TextFormat::GREEN.$key."setlore <line> <text>");
					    break;
					}					
				    $newLore = [];
					$data = $args;
					unset($data[0]);
					array_shift($data);
		            foreach($this->item[$item[0]]["LORE"] as $case => $lore){
				        $newLore[$case] = $lore;
					}
		            for($line = 0; $line < (int) $args[1]; $line++){
			            if(empty($newLore[$line])){
				            $newLore[$line] = "";
						}
					}
					$newLore[(int) $args[1]] = trim(implode(" ", $data));
					$this->item[$item[0]]["LORE"] = $newLore;
					$player->sendMessage(TextFormat::GREEN."Lore item was set!");
				break;
				case $key."insertlore":
				    if(empty($args[1]) or empty($args[2])){
					    $player->sendMessage(TextFormat::GREEN.$key."insertlore <line> <text>");
					    break;
					}					
				    $newLore = [];
					$data = $args;
					unset($data[0]);
					array_shift($data);		            
					for($i = 0; $i < (int) $args[1]; $i++){
				        $newLore[$i] = "";
					}
					$newLore[(int) $args[1]] = trim(implode(" ", $data));
					$this->item[$item[0]]["LORE"] = $newLore;
					$player->sendMessage(TextFormat::GREEN."Lore item was set!");
				break;
				case $key."addlore":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."addlore <text>");
					    break;
					}					
				    $newLore = [];
					$data = $args;
					unset($data[0]);
					array_shift($data);
		            foreach($item->getLore() as $case => $lore){
			            $newLore[] = $lore;
					}
		            $newLore[] = trim(implode(" ", $data));
					$this->item[$item[0]]["LORE"] = $newLore;
					$player->sendMessage(TextFormat::GREEN."Lore item was set!");
				break;
				case $key."setdamage":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setdamage <damage>");
					    break;
					}
					$this->item[$item[0]]["DAMAGE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Damage item was set!");
				break;
				case $key."setstrength":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setstrength <strength>");
					    break;
					}
					$this->item[$item[0]]["STRENGTH"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Strength item was set!");
				break;
				case $key."setdefense":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setdefense <defense>");
					    break;
					}
					$this->item[$item[0]]["DEFENSE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Defense item was set!");
				break;
				case $key."setcritchance":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setcritchance <chance>");
					    break;
					}
					$this->item[$item[0]]["CRIT_CHANCE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."CritChance item was set!");
				break;
				case $key."setcritdamage":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setcritdamage <damage>");
					    break;
					}
					$this->item[$item[0]]["CRIT_DAMAGE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."CritDamage item was set!");
				break;
				
				case $key."setattackspeed":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setattackspeed <speed>");
					    break;
					}
					$this->item[$item[0]]["ATTACK_SPEED"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."AttackSpeed item was set!");
				break;
				case $key."setminingspeed":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setminingspeed <speed>");
					    break;
					}
					$this->item[$item[0]]["MINING_SPEED"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."MiningFortune item was set!");
				break;
				case $key."setfarmingfortune":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setfarmingfortune <fortune>");
					    break;
					}
					$this->item[$item[0]]["FARMING_FORTUNE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."FarmingFortune item was set!");
				break;
				case $key."setminingfortune":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setminingfortune <fortune>");
					    break;
					}
					$this->item[$item[0]]["MINING_FORTUNE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."MiningFortune item was set!");
				break;
				case $key."setforagingfortune":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setforagingfortune <fortune>");
					    break;
					}
					$this->item[$item[0]]["FORAGING_FORTUNE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."ForagingFortune item was set!");
				break;
				case $key."setferocity":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setferocity <ferocity>");
					    break;
					}
					$this->item[$item[0]]["FEROCITY"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Ferocity item was set!");
				break;
				case $key."setabilitydamage":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setabilitydamage <damage>");
					    break;
					}
					$this->item[$item[0]]["ABILITY_DAMAGE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."AbilityDamage item was set!");
				break;
				case $key."sethealth":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."sethealth <health>");
					    break;
					}
					$this->item[$item[0]]["HEALTH"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Health item was set!");
				break;
				case $key."setspeed":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setspeed <speed>");
					    break;
					}
					$this->item[$item[0]]["SPEED"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Speed item was set!");
				break;
				case $key."setintelligence":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setintelligence <intelligence>");
					    break;
					}
					$this->item[$item[0]]["INTELLIGENCE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Intelligence item was set!");
				break;
				case $key."setrune":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setrune <runeId>");
					    break;
					}
					$this->item[$item[0]]["RUNE"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."Rune item was set!");
				break;
				case $key."listability":
				    $player->sendMessage(TextFormat::GREEN."-----MyItem-----");
					foreach($this->getManager()->listAbility() as $ability){
						$name = $ability[0];
						$id = $ability[1];
						$dec = $ability[2];
						$player->sendMessage(TextFormat::RED."$id".TextFormat::GRAY." > ".TextFormat::YELLOW.$name.TextFormat::GRAY." - ".TextFormat::DARK_GREEN.$dec);
					}
					$player->sendMessage(TextFormat::GREEN."----------------");
					$player->sendMessage("-----ID-EVENTS-----");
					$player->sendMessage("1 => Click");
					$player->sendMessage("2 => Aim");
					$player->sendMessage("3 => Movement");
					$player->sendMessage("4 => Jump");
					$player->sendMessage("5 => Drop");
					$player->sendMessage("6 => Attack");
					$player->sendMessage("-------------------");
				break;
				case $key."insertability":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."insertability <ability>");
					    break;
					}
					$check = $this->checkCommandAbility(1, $args, $player, true);
					if($check == false){
						return;
					}
					$result = [];
		            $createKey = self::KEY_MYITEM;
		            foreach(explode(":", $createKey) as $case){
			            $result[] = $case;
					}
					$newData = $args;
					unset($newData[0]);
                    $result[] = implode("|", $newData);		
					$this->item[$item[0]]["ABILITY"] = implode(":", $result);
					$player->sendMessage(TextFormat::GREEN."Ability item was set!");
				break;
				case $key."addability":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."addability <ability>");
					    break;
					}
					$check = $this->checkCommandAbility(2, $args, $player, true);
					if($check == false){
						return;
					}
					$result = [];
			        foreach(explode(":", $this->item[$item[0]]["ABILITY"]) as $case){
				        $result[] = $case;
					}
					$newData = $args;
					unset($newData[0]);
			        $result[] = implode("|", $newData);			        
					$this->item[$item[0]]["ABILITY"] = implode(":", $result);
					$player->sendMessage(TextFormat::GREEN."Ability item was set!");
				break;
				case $key."setpermission":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setpermission <permission>");
					    break;
					}
					$this->item[$item[0]]["PERMISSION"] = $args[1];
					$player->sendMessage(TextFormat::GREEN."Permission item was set!");
				break;
				case $key."addsound":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."addsound <sound>");
					    break;
					}
					$sound = new Sounds();
					$result = $sound->addSound($args[1]);
					$this->item[$item[0]]["SOUNDS"] = $result;
					$player->sendMessage(TextFormat::GREEN."Sound item was set!");
				break;
				case $key."insertsound":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."insertsound <sound>");
					    break;
					}
					$sound = new Sounds();
					$result = $sound->insertSound($args[1]);
					$this->item[$item[0]]["SOUNDS"] = $result;
					$player->sendMessage(TextFormat::GREEN."Sound item was set!");
				break;
				case $key."setcounter":
				    if(empty($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setcounter <counter>");
					    break;
					}
					$this->item[$item[0]]["COUNTER"] = $args[1];
					$player->sendMessage(TextFormat::GREEN."Counter item was set!");
				break;
				case $key."setcolor":
				    if(!isset($args[1]) or !isset($args[2]) or !isset($args[3])){
					    $player->sendMessage(TextFormat::GREEN.$key."setcolor <a> <b> <c>");
					    break;
					}		
                    if(!is_numeric($args[1]) or !is_numeric($args[2]) or !is_numeric($args[3])){
					    $player->sendMessage(TextFormat::RED."Color must is number!");
					    break;
					}					
					$this->item[$item[0]]["COLOR"] = [$args[1], $args[2], $args[3]];
					$player->sendMessage(TextFormat::GREEN."Color item was set!");
				break;
				case $key."setrarity":
				    if(!isset($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."setrarity <string>");
					    break;
					}						
					$this->item[$item[0]]["RARITY"] = $args[1];
					$player->sendMessage(TextFormat::GREEN."Rarity item was set!");
				break;
				case $key."settimecountdown":
				    if(!isset($args[1])){
					    $player->sendMessage(TextFormat::GREEN.$key."settimecountdown <time>");
					    break;
					}						
					$this->item[$item[0]]["TIMECOUNTDOWN"] = (int) $args[1];
					$player->sendMessage(TextFormat::GREEN."TimeCountDown item was set!");
				break;
				case $key."leave":				    
					if($this->item[$item[0]]["NAME"] == null){
						$player->sendMessage(TextFormat::RED."You are missing some important data");
						break;
					}
					$new = new Config($this->getDataFolder()."items".DIRECTORY_SEPARATOR.$item[0].".yml", Config::YAML);
					$new->setAll($this->item[$item[0]]);
					$new->save();
                    unset($this->item[$item[0]]);					
					unset($this->modeCreateItem[$player->getName()]);
					$player->sendMessage(TextFormat::GOLD."Data is saving... wait 5s to load data!");
					$player->sendMessage(TextFormat::RED."You have left the mode setup!");
				break;
			}
        }
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($command->getName() == "myitem"){
			if($sender instanceof Player){
				if(!$sender->hasPermission("myitem.command")){
					$sender->sendMessage(TextFormat::RED."You don't have permission!");
					return false;
				}				
			}
			if(empty($args[0])){
				$space = TextFormat::WHITE." - ";
				$sender->sendMessage(TextFormat::DARK_GREEN."-----MyItem-----".TextFormat::AQUA." NEW VERSION");
				$sender->sendMessage(TextFormat::GOLD."/myitem about".$space.TextFormat::GRAY."Show information the plugin.");
				$sender->sendMessage(TextFormat::GOLD."/myitem give <player> <name>".$space.TextFormat::GRAY."Give item for player.");
				$sender->sendMessage(TextFormat::GOLD."/myitem create <name>".$space.TextFormat::GRAY."Create one new item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem remove <name>".$space.TextFormat::GRAY."Remove file item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setname <name>".$space.TextFormat::GRAY."Set display name of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setlore <line> <text>".$space.TextFormat::GRAY."Set lore at specific line.");
				$sender->sendMessage(TextFormat::GOLD."/myitem insertlore <line> <text>".$space.TextFormat::GRAY."Insert lore without remove the previous lore.");
				$sender->sendMessage(TextFormat::GOLD."/myitem addlore <text>".$space.TextFormat::GRAY."Add new lore on held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem addflag <flag> <data> <string/int>".$space.TextFormat::GRAY."Add flag to held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem removeflag <flag>".$space.TextFormat::GRAY."Remove flag from held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setdamage <damage>".$space.TextFormat::GRAY."Set Damage state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setstrength <strength>".$space.TextFormat::GRAY."Set Strength state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setdefense <defense>".$space.TextFormat::GRAY."Set Defense state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setcritchance <chance>".$space.TextFormat::GRAY."Set CritDamage state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setcritdamage <damage>".$space.TextFormat::GRAY."Set CritChance state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setminingspeed <speed>".$space.TextFormat::GRAY."Set MiningSpeed state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setattackspeed <speed>".$space.TextFormat::GRAY."Set AttackSpeed state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setminingfortune <fortune>".$space.TextFormat::GRAY."Set MiningFortune state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setfarmingfortune <fortune>".$space.TextFormat::GRAY."Set FarmingFortune state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setforagingfortune <fortune>".$space.TextFormat::GRAY."Set ForagingFortune state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setferocity <ferocity>".$space.TextFormat::GRAY."Set Ferocity state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setabilitydamage <damage>".$space.TextFormat::GRAY."Set AbilityDamage state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem sethealth <health>".$space.TextFormat::GRAY."Set Health state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setspeed <speed>".$space.TextFormat::GRAY."Set Speed state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setintelligence <intelligence>".$space.TextFormat::GRAY."Set Intelligence state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem listability".$space.TextFormat::GRAY."Show list for abilities.");
				$sender->sendMessage(TextFormat::GOLD."/myitem insertability <ability>".$space.TextFormat::GRAY."Insert ability without remove the previous ability.");
				$sender->sendMessage(TextFormat::GOLD."/myitem addability <ability>".$space.TextFormat::GRAY."Add new ability on held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setpermission <permission>".$space.TextFormat::GRAY."Set new permission on held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setmana <mana>".$space.TextFormat::GRAY."set mana to use item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem addsound <sound>".$space.TextFormat::GRAY."Add sound for item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem insertsound <sound>".$space.TextFormat::GRAY."Insert sound for item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setcounter <counter>".$space.TextFormat::GRAY."Counter setted for item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setcolor <a> <b> <c>".$space.TextFormat::GRAY."Set color for armor, just leather armor.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setrarity <string>".$space.TextFormat::GRAY."Set Rarity state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem settimecountdown <time>".$space.TextFormat::GRAY."Set Timecountdown state of held item.");
				$sender->sendMessage(TextFormat::GOLD."/myitem setunbreakable".$space.TextFormat::GRAY."Set unbreakable state of held item.");
				$sender->sendMessage(TextFormat::DARK_GREEN."-----------------");
				return true;
			}
			switch($args[0]){
				case "about":
				    $sender->sendMessage(TextFormat::DARK_GREEN."-----------------");
					$sender->sendMessage(TextFormat::GREEN."Plugin Name: MyItem");
					$sender->sendMessage(TextFormat::GREEN."Author: DragoVN(hachkingtohach1)");
					$sender->sendMessage(TextFormat::GREEN."Email: pnam5005@gmail.com");
					$sender->sendMessage(TextFormat::DARK_GREEN."-----------------");
				break;
				case "menu":
				    $this->openMenuItems($sender);
				break;
				case "give":
				    if(!isset($args[1]) or !isset($args[2]) or !isset($args[3])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem give <player> <item> <count>");
					    break;
					}
				    foreach($this->getServer()->getOnlinePlayers() as $player){
						if(strtolower($player->getName()) == strtolower($args[1])){
							$this->giveItem($player, $args[2], $args[3]);
							$player->sendMessage(self::PREFIX." You had given a new item!");
						}
					}
			    break;
				case "create":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem create <name>");
					    break;
					}
				    if(!isset($this->modeCreateItem[$sender->getName()])){
						$this->modeCreateItem[$sender->getName()] = [$args[1], $sender];
						$sender->sendMessage(TextFormat::DARK_GREEN."-MyItemSetUpItem-");
				        $sender->sendMessage(TextFormat::GRAY.".help - to show all commands");
				        $sender->sendMessage(TextFormat::GRAY.".leave - to leave setup mode and done item");
				        $sender->sendMessage(TextFormat::DARK_GREEN."-----------------");
					}else{
						$sender->sendMessage(TextFormat::RED."You are in this mode!");
					}
			    break;
				case "remove":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem remove <name>");
					    break;
					}
					if(is_file($file = $this->getDataFolder()."items".DIRECTORY_SEPARATOR.$args[1].".yml")){
						unlink($file);
						$sender->sendMessage(TextFormat::GREEN."File was removed!");
					}else{
						$sender->sendMessage(TextFormat::RED."File don't exist!");
					}
				break;
				case "setname":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setname <name>");
					    break;
					}
				    $this->setName($sender, $args);
					$sender->sendMessage(TextFormat::GREEN."Name item was set!");
			    break;
				case "setlore":
				    if(!isset($args[1]) or empty($args[2]) or !is_numeric($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setlore <line> <text>");
					    break;
					}
				    $this->setLore($sender, $args[1], $args);
					$sender->sendMessage(TextFormat::GREEN."Lore item was set!");
			    break;
				case "insertlore":
				    if(!isset($args[1]) or empty($args[2]) or !is_numeric($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem insertlore <line> <text>");
					    break;
					}
				    $this->InsertLore($sender, $args[1], $args);
					$sender->sendMessage(TextFormat::GREEN."Lore item was set!");
			    break;
				case "addlore":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem addlore <text>");
					    break;
					}
				    $this->addLore($sender, $args);
					$sender->sendMessage(TextFormat::GREEN."Lore item was set!");
			    break;
				case "removelore":
				    $this->removeLore($sender);
					$sender->sendMessage(TextFormat::GREEN."Lore item was set!");
			    break;
				case "addflag":
				    if(empty($args[1]) or empty($args[2]) or empty($args[3])){					
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem addflag <flag> <data> <string/int>");
					    break;
					}
					if(!in_array($args[3], ["string", "int"])){
						break;
					}
				    $this->addFlag($sender, $args[1], $args[2], $args[3]);
					$sender->sendMessage(TextFormat::GREEN."Flag item was set!");
			    break;
				case "removeflag":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem removeflag <flag>");
					    break;
					}
				    $this->removeFlag($sender, $args[1]);
					$sender->sendMessage(TextFormat::GREEN."Flag item was set!");
			    break;
				case "setdamage":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setdamage <damage:int>");
					    break;
					}
				    $this->setDamage($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Damage item was set!");
			    break;
				case "setstrength":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setstrength <setstrength:int>");
					    break;
					}
				    $this->setStrength($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Strength item was set!");
			    break;
				case "setdefense":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setdamage <damage:int>");
					    break;
					}
				    $this->setDefense($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Defense item was set!");
			    break;
				case "setcritchance":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setcritchance <chance:int>");
					    break;
					}
					if((int)$args[1] > 100){
						$sender->sendMessage(TextFormat::RED."Max CritChance is 100!");
					    break;
					}
				    $this->setCritChance($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."CritChance item was set!");
			    break;
				case "setcritdamage":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setcritdamage <damage:int>");
					    break;
					}
				    $this->setCritDamage($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."CritDamage item was set!");
				case "setminingspeed":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setminingspeed <speed:int>");
					    break;
					}
				    $this->setMiningSpeed($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."MiningSpeed item was set!");
				break;
				case "setattackspeed":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setattackspeed <speed:int>");
					    break;
					}
				    $this->setAttackSpeed($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."AttackSpeed item was set!");
				break;
				case "setfarmingfortune":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setfarmingfortune <fortune:int>");
					    break;
					}
				    $this->setFarmingFortune($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."FarmingFortune item was set!");
				break;
				case "setminingfortune":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setminingfortune <fortune:int>");
					    break;
					}
				    $this->setMiningFortune($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."MiningFortune item was set!");
				break;
				case "setforagingfortune":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setforagingfortune <fortune:int>");
					    break;
					}
				    $this->setForagingFortune($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."ForagingFortune item was set!");
			    break;
				case "setferocity":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setferocity <ferocity:int>");
					    break;
					}
				    $this->setFerocity($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Ferocity item was set!");
			    break;
				case "setabilitydamage":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setabilitydamage <damage:int>");
					    break;
					}
				    $this->setAbilityDamage($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."AbilityDamage item was set!");
			    break;
				case "sethealth":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem sethealth <health:int>");
					    break;
					}
				    $this->setHealth($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Health item was set!");
			    break;
				case "setspeed":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setspeed <speed:int>");
					    break;
					}
				    $this->setSpeed($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Speed item was set!");
			    break;
				case "setintelligence":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setintelligence <intelligence:int>");
					    break;
					}
				    $this->setIntelligence($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Intelligence item was set!");
			    break;
				case "setrune":
				    if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setrune <rune:int>");
					    break;
					}
				    $this->setRune($sender, (int)$args[1]);
					$sender->sendMessage(TextFormat::GREEN."Rune item was set!");
			    break;
				case "listability":
					$sender->sendMessage(TextFormat::GREEN."-----MyItem-----");
					foreach($this->getManager()->listAbility() as $ability){
						$name = $ability[0];
						$id = $ability[1];
						$dec = $ability[2];
						$sender->sendMessage(TextFormat::RED."$id".TextFormat::GRAY." > ".TextFormat::YELLOW.$name.TextFormat::GRAY." - ".TextFormat::DARK_GREEN.$dec);
					}
					$sender->sendMessage(TextFormat::GREEN."----------------");
					$sender->sendMessage("-----ID-EVENTS-----");
					$sender->sendMessage("1 => Click");
					$sender->sendMessage("2 => Aim");
					$sender->sendMessage("3 => Movement");
					$sender->sendMessage("4 => Jump");
					$sender->sendMessage("5 => Drop");
					$sender->sendMessage("6 => Attack");
					$sender->sendMessage("-------------------");
				break;
				case "insertability":
				    if($this->checkCommandAbility(1, $args, $sender, false) == true){
					    $sender->sendMessage(TextFormat::GREEN."Ability item was set!");
					}
				break;
				case "addability":
				    if($this->checkCommandAbility(2, $args, $sender, false) == true){
					    $sender->sendMessage(TextFormat::GREEN."Ability item was set!");
					}
				break;
				case "setpermission":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setpermission <permission>");
					    break;
					}
				    $this->setPermission($sender, $args[1]);
					$sender->sendMessage(TextFormat::GREEN."Permission item was set!");
				break;
				case "setmana":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setmana <mana>");
					    break;
					}
					$this->setMana($sender, (int) $args[1]);
					$sender->sendMessage(TextFormat::GREEN."Mana item was set!");
				break;
				case "addsound":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem addsound <sound>");
					    break;
					}
					$sounds = "";
					$sound = new Sounds();
					$item = $sender->getInventory()->getItemInHand();
					$nbt = $item->getNamedTag();
					if($nbt->getTag("Sounds", StringTag::class) != null){
					    $sounds = $sender->getInventory()->getItemInHand()->getNamedTag()->getString("Sounds");
					}
					$result = $sound->addSound($sounds, $args[1]);
					$nbt->setString("Sounds", $result);
				    $sender->getInventory()->setItemInHand($item);
					$sender->sendMessage(TextFormat::GREEN."Item had added sound!");
				break;
				case "insertsound":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem insertsound <sound>");
					    break;
					}
					$sound = new Sounds();
					$result = $sound->insertSound($args[1]); 
					$item = $sender->getInventory()->getItemInHand();
					$nbt = $item->getNamedTag();
					$nbt->setString("Sounds", $result);
				    $sender->getInventory()->setItemInHand($item);
					$sender->sendMessage(TextFormat::GREEN."Item had added sound!");
				break;
				case "setcounter":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setcounter <counter>");
					    break;
					}
					$this->setCounter($sender, (int) $args[1]);
					$sender->sendMessage(TextFormat::GREEN."Counter item was set!");
				break;
				case "setcolor":
					if(!isset($args[1]) or !isset($args[2]) or !isset($args[3])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setcolor <a> <b> <c>");
					    break;
					}
					$item = $sender->getInventory()->getItemInHand();
					if(in_array($item->getId(), [298, 299, 300, 301])){
						$item->setCustomColor(new Color($args[1], $args[2], $args[3]));
						$sender->getInventory()->setItemInHand($item);
					}else{
						$sender->sendMessage(TextFormat::GREEN."It must be leather material!");
					}
				break;
				case "setrarity":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem setrarity <int>");
					    break;
					}
					$this->setRarity($sender, (int) $args[1]);
					$sender->sendMessage(TextFormat::GREEN."Rarity item was set!");
				break;
				case "settimecountdown":
					if(empty($args[1])){
					    $sender->sendMessage(TextFormat::GREEN."Usage: /myitem settimecountdown <time>");
					    break;
					}
					$this->setTimeCountDown($sender, (int) $args[1]);
					$sender->sendMessage(TextFormat::GREEN."TimeCountDown item was set!");
				break;
				case "setkillscounter":
					$this->setKillsCounter($sender);
					$sender->sendMessage(TextFormat::GREEN."Kills counter item was set!");
				break;
				case "setunbreakable":
				    $this->setUnbreakable($sender);
					$sender->sendMessage(TextFormat::GREEN."Unbreakable for item was set!");
				break;
			}				
			return true;
        }
		return false;
	}
	
	public function setName(Player $player, array $args){
		array_shift($args);
		$item = $player->getInventory()->getItemInHand();
		$item->setCustomName(trim(implode(" ", $args)));
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setLore(Player $player, int $line, array $args){
		unset($args[1]);
		array_shift($args);		
		$item = $player->getInventory()->getItemInHand();
		$newLore = [];
		foreach($item->getLore() as $case => $lore){
			$newLore[$case] = $lore;				
		}		
		for($lineX = 0; $lineX < $line; $lineX++){
			if(empty($newLore[$lineX])){
				$newLore[$lineX] = "";
			}
		}
		$newLore[$line] = trim(implode(" ", $args));		
		$item->setLore($newLore);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function InsertLore(Player $player, int $line, array $args){
		unset($args[1]);
		array_shift($args);
		$item = $player->getInventory()->getItemInHand();
		$newLore = [];
		for($i = 0; $i < $line; $i++){
			$newLore[$i] = "";
		}
		$newLore[$line] = trim(implode(" ", $args));
		$item->setLore($newLore);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function addLore(Player $player, array $args){
		unset($args[1]);
		$array = array_shift($args);
		$item = $player->getInventory()->getItemInHand();
		$newLore = [];
		foreach($item->getLore() as $case => $lore){
			$newLore[] = $lore;
		}
		$newLore[] = trim(implode(" ", $args));
		$item->setLore($newLore);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function removeLore(Player $player){
		$item = $player->getInventory()->getItemInHand();
		$item->setLore([]);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function addFlag(Player $player, string $flag, string $type, $data){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		if($type == "string"){
			$nbt->setString($flag, $data);
		}
		if($type == "int"){
			$nbt->setInt($flag, $data);
		}
		$nbt->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function removeFlag(Player $player, string $flag){
		$item = $player->getInventory()->getItemInHand();
		$item->getNamedTag()->removeTag($flag);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setDamage(Player $player, int $damage){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Damagemodifier", $damage);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setStrength(Player $player, int $damage){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Strength", $damage);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setDefense(Player $player, int $damage){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Defense", $damage);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setCritChance(Player $player, int $damage){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Critchance", $damage);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setCritDamage(Player $player, int $damage){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Critdamage", $damage);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setMiningSpeed(Player $player, int $speed){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Miningspeed", $speed);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setAttackSpeed(Player $player, int $speed){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Attackspeed", $speed);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setMiningFortune(Player $player, int $fortune){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Miningfortune", $fortune);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setFarmingFortune(Player $player, int $fortune){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Farmingfortune", $fortune);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setForagingFortune(Player $player, int $fortune){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Foragingfortune", $fortune);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setFerocity(Player $player, int $ferocity){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Ferocity", $ferocity);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setAbilityDamage(Player $player, int $ferocity){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Abilitydamage", $ferocity);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setHealth(Player $player, int $health){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Health", $health);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setSpeed(Player $player, int $speed){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Speed", $speed);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setIntelligence(Player $player, int $intelligence){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Intelligence", $intelligence);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setRune(Player $player, int $rune){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Rune", $rune);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setCounter(Player $player, int $counter){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Counter", $counter);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setRarity(Player $player, int $rarity){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Rarity", $rarity);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setKillsCounter(Player $player){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Kills", 0);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setTimeCountDown(Player $player, int $time){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Timecountdown", $time);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function setUnbreakable(Player $player){
		$item = $player->getInventory()->getItemInHand();
		if($item instanceof Durable){
            $item->setUnbreakable();
		}
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function insertAbility(Player $player, string $ability, $setupmode = false){
		$result = [];
		$createKey = self::KEY_MYITEM;
		$item = $player->getInventory()->getItemInHand();
        $nbt = $item->getNamedTag();
		foreach(explode(":", $createKey) as $case){
			$result[] = $case;
		}
        $result[] = $ability;		
        $nbt->setString("Ability", implode(":", $result));
		$item->setNamedTag($nbt);
		if($setupmode != true){
		    return $player->getInventory()->setItemInHand($item);	
		}else{
			return;
		}			
	}
	
	public function addAbility(Player $player, string $ability, $setupmode = false){
		$result = [];
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		if($nbt->getTag("Ability", StringTag::class) != null){
			foreach(explode(":", $nbt->getString("Ability")) as $case){
				$result[] = $case;
			}
			$result[] = $ability;
			$nbt->setString("Ability", implode(":", $result));
		    $item->setNamedTag($nbt);
		    if($setupmode != true){
			    return $player->getInventory()->setItemInHand($item);
			}else{
				return;
			}
		}else{
			$this->insertAbility($player, $ability);
		}
	}
	
	public function setPermission(Player $player, string $permission){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setString("Permission", $permission);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function checkPermission(Player $player) :bool{
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		if($nbt->getTag("Permission", StringTag::class) != null){
			if($nbt->getString("Permission") != "NOPE"){
				if($player->hasPermission($nbt->getString("Permission"))){
					return true;
				}else{
					return false;
				}
			}else{
				return true;
			}
		}
		return true;
	}
	
	public function checkArmorPermission(Player $player) :bool{
		foreach($player->getArmorInventory()->getContents() as $index => $item){
		    $nbt = $item->getNamedTag();
		    if($nbt->getTag("Permission", StringTag::class) != null){
			    if($nbt->getString("Permission") != "NOPE"){
				    if($player->hasPermission($nbt->getString("Permission"))){
					    return true;
				    }else{
					    return false;
				    }
			    }else{
				    return true;
			    }
		    }
		}
		return false;
	}
	
	public function setMana(Player $player, int $mana){
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		$nbt->setInt("Mana", $mana);
		$item->setNamedTag($nbt);
		return $player->getInventory()->setItemInHand($item);
	}
	
	public function checkMana(Player $player, int $mana) :bool{
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();
		if($nbt->getTag("Mana", IntTag::class) != null){
			if($nbt->getInt("Mana") != 0){
		        if(PlayerStats::getInstance()->checkMana($mana, $player)){
					return true;
				}else{
					return false;
				}
			}			
		}
		return true;
	}
	
	public function checkCommandAbility(int $mode, array $args, Player $player, $setupmode = false) :bool{
		if(empty($args[1])) return false;
		$key = "|";
		$id = $args[1];	
        if($setupmode != false){
			$cmd = "";
		}else{
			$cmd = "/myitem ";
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["POISON"][1],
		    $this->getManager()->listAbility()["WITHER"][1],
			$this->getManager()->listAbility()["SLOW"][1],
			$this->getManager()->listAbility()["CONFUSE"][1],
			$this->getManager()->listAbility()["WEAK"][1],
			$this->getManager()->listAbility()["BLIND"][1],
			$this->getManager()->listAbility()["HUNGRY"][1],
			$this->getManager()->listAbility()["HARM"][1],	
			$this->getManager()->listAbility()["SHIELD"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4]) or empty($args[5])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4]) or !is_numeric($args[5])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <duration> <level> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[5], $player)){
		        if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["FREEZE"][1],
			$this->getManager()->listAbility()["DARK_FLAME"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <ticks> <timecountdown> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[4], $player)){
			    if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["FLAME_WHEEL"][1],
			$this->getManager()->listAbility()["CURSE"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <time> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[4], $player)){
                if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["LIGHTING"][1],
			$this->getManager()->listAbility()["GIANT_SWORD"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style(Throw can't distance)> <damage> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[4], $player)){
			    if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
			}
			return true;
		}
		if(in_array($id, [
		    $this->getManager()->listAbility()["THROW"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4]) or empty($args[5]) or empty($args[6])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4]) or !is_numeric($args[5]) or !is_numeric($args[6])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style(Throw can't distance)> <damage> <id> <meta> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[6], $player)){
			    if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5].$key.$args[6]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5].$key.$args[6]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["TELEPORT"][1],
			$this->getManager()->listAbility()["IMPLOSION"][1],
			$this->getManager()->listAbility()["GRAVITY_STORM"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <distance> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[4], $player)){
				if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["VAMPIRISM"][1],
		    $this->getManager()->listAbility()["CANNIBALISM"][1],
			$this->getManager()->listAbility()["ROOTS"][1],
			$this->getManager()->listAbility()["AIR_SHOCK"][1],
		    $this->getManager()->listAbility()["DARK_IMPACT"][1],
			$this->getManager()->listAbility()["BUBBLE_DEFLECTOR"][1],
			$this->getManager()->listAbility()["SWING"][1]
		])){
			if(empty($args[2]) or empty($args[3])
			    or !is_string($args[2]) or !is_numeric($args[3])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[3], $player)){
				if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3]);
				}
			}
			return true;
		}
		if(in_array($id, [
			$this->getManager()->listAbility()["RAPID_ARROW"][1]
		])){
			if(empty($args[2]) or empty($args[3]) or empty($args[4]) or empty($args[5])
			    or !is_string($args[2]) or !is_numeric($args[3]) or !is_numeric($args[4]) or !is_numeric($args[5])
			){
			    $player->sendMessage(TextFormat::GREEN.$cmd.$args[0]." ".$args[1]." <style> <damage> <yaw> <event>");
				return false;
			}
			if($this->getManager()->checkEvent($args[5], $player)){
				if($mode == 1){
				    $this->insertAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5]);
				}
				if($mode == 2){
				    $this->addAbility($player, $args[1].$key.$args[2].$key.$args[3].$key.$args[4].$key.$args[5]);
				}
			}
			return true;
		}
		return false;
	}
	
	public function setBasicData(string $name) :array{
		$sounds = new Sounds();
		return $this->item[$name] = [
		    "NAME_RECIPE" => $name,		    
			"NAME" => $name,
			"ID" => 0,
			"META" => 0,
			"DAMAGE" => 0,
			"STRENGTH" => 0,
			"CRIT_CHANCE" => 0,
			"CRIT_DAMAGE" => 0,
			"INTELLIGENCE" => 0,
			"DEFENSE" => 0,
			"MINING_SPEED" => 0,
			"ATTACK_SPEED" => 0,
			"FARMING_FORTUNE" => 0,
			"MINING_FORTUNE" => 0,
			"FORAGING_FORTUNE" => 0,
			"FEROCITY" => 0,
			"ABILITY_DAMAGE" => 0,
			"HEALTH" => 0,
			"SPEED" => 0,
			"INTELLIGENCE" => 0,
			"LORE" => [],
			"ABILITY" => self::KEY_MYITEM,
			"PERMISSION" => "NOPE",
			"MANA" => 0,
			"EVENT" => 0,
			"COUNTER" => 0,
			"SOUNDS" => sounds::KEY_SOUND,
			"RUNE" => 0,
			"COLOR" => [],
			"CATEGORY" => "",
			"RARITY" => 1,
			"BONUS" => self::KEY_BONUS,
			"RELATIVE" => "",
			"TIMECOUNTDOWN" => 0
		];
	}
	
	public function getManager(){
		$class = new Manager($this);
		return $class;
	}
	
	public function getBonus(){
		$class = new Bonus($this);
		return $class;
	}
	
	public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
    }
	
	public function onDamage(EntityDamageEvent $event) :void{
        $entity = $event->getEntity();
        if($event instanceof EntityDamageByEntityEvent){          
			$damager = $event->getDamager();
			if($damager instanceof Player){
				$this->deaths[$entity->getId()] = [$damager, microtime(true)];
			}
		}
	}
	
	public function onDeath(EntityDeathEvent $event){
		$entity = $event->getEntity();
		if(!empty($this->deaths[$entity->getId()])){
			$player = $this->deaths[$entity->getId()][0];
			Rune::getRune($player, $entity);
			unset($this->deaths[$entity->getId()]);
		}			
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		if(isset($this->bonemerang[$player->getName()])){
		    foreach($this->bonemerang[$player->getName()] as $time => $data){
				$player->getInventory()->addItem($data[0]);
				unset($this->bonemerang[$player->getName()][$time]);
			}
		}
	}
}