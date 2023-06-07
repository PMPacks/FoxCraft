<?php

namespace hachkingtohach1\CraftingTable;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use skymin\InventoryLib\{InvLibManager, LibInvType, InvLibAction, LibInventory};
use hachkingtohach1\CraftingTable\task\{OpenGUI, UpdateGUI, OpenRecipe, OpenRecipes};
use hachkingtohach1\MyItem\MyItem;

class CraftingTable extends PluginBase implements Listener {
	
	public $usingCraftingTable = [];
	public $recipes = [];
	public $pageDataPlayer = [];
	public $inRecipe = [];
	private $status = true;
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): CraftingTable{
        return self::$instance;
    }

    public function onEnable() :void{
		InvLibManager::register($this);	
        $this->registerRecipes();		
		$this->saveDefaultConfig();
		$this->getScheduler()->scheduleRepeatingTask(new UpdateGUI($this), 1);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "craftingtable"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			$this->craftingTable($sender);
			return true;
        }
		if($command->getName() == "recipe"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			$this->openRecipes($sender);
			return true;
		}
		if($command->getName() == "admincraftingtable"){
			if($this->status == true){
				$sender->sendMessage(TextFormat::GREEN."> CraftingTable enable!");
				$this->status = false;
			}else{
				$sender->sendMessage(TextFormat::RED."> CraftingTable disable!");
				$this->statusShop = true;
			}
			return true;
        }
		return false;
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function craftingTable(Player $player){		
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Advanced Crafting');		
		
		$null = $this->getDataItem(26, 0, 1);
		$null->setCustomName("-");    
		
		for($i = 0; $i <= 53; $i++){
			if(!in_array($i, [10, 11, 12, 19, 20, 21, 28, 29, 30, 23, 45])){
				$menu->setItem($i, $null);
			}
		}		

        $nullNew = $this->getDataItem(260, 0, 1);
		$nullNew->setCustomName("-");    
		$menu->setItem(23, $nullNew);
		
		$recipe = $this->getDataItem(340, 0, 1);
		$recipe->setCustomName("Cách chế tạo");    
		$menu->setItem(45, $recipe);
		
        $menu->send($player);		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$itemClicked = $action->getSourceItem();
			$player = $action->getPlayer();              
            foreach($this->recipes as $recipe){
				$result = $recipe["result"];
				if($result->getId() == $itemClicked->getId() and $itemClicked->getId() == $menu->getItem(23)->getId()
					and $result->getMeta() == $itemClicked->getMeta() and $itemClicked->getMeta() == $menu->getItem(23)->getMeta()
					and $result->getCount() >= $itemClicked->getCount() and $itemClicked->getCount() >= $menu->getItem(23)->getCount()
					and $result->getCustomName() == $itemClicked->getCustomName() and $itemClicked->getCustomName() == $menu->getItem(23)->getCustomName()
				){
					$slots = [10 => 1, 11 => 2, 12 => 3, 19 => 4, 20 => 5, 21 => 6, 28 => 7, 29 => 8, 30 => 9];
					foreach($slots as $slot => $resultIndex){
						$item = $menu->getItem($slot);
						$countNow = $menu->getItem($slot)->getCount();
						$countIndex = (int) explode(",", $recipe[$resultIndex])[2];
						$countLast = $countNow - $countIndex;
						$item->setCount($countLast);
						$menu->setItem($slot, $item);
					}									
				}
			}			
			switch($itemClicked->getCustomName()){
				case "-":
				    $action->setCancelled();
				break;
				case "Cách chế tạo":
				    $action->setCancelled();
					$player->removeCurrentWindow();	
					$this->getScheduler()->scheduleDelayedTask(new OpenRecipes($this, $player), 10);
				break;
			}
		});
		$menu->setCloseListener(function(Player $player) use ($menu) : void{
			for($i = 0; $i <= 53; $i++){
			    if(in_array($i, [10, 11, 12, 19, 20, 21, 28, 29, 30])){
			        if($menu->getItem($i)->getId() !== 0){
				        $player->getInventory()->addItem($menu->getItem($i));
					}
				}		
			}
            if(isset($this->usingCraftingTable[$player->getName()])){
				unset($this->usingCraftingTable[$player->getName()]);
			}				
		});
		$this->usingCraftingTable[$player->getName()] = $menu;
	}
	
	public function openRecipes(Player $player){
		if(!isset($this->pageDataPlayer[$player->getName()])){
			$this->pageDataPlayer[$player->getName()] = 1;
		}	
        $menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Cách chế tạo/Lưu ý: Vài item sẽ không có công thức!'); 		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$item = $action->getSourceItem();
			$player = $action->getPlayer();
			$name = [];
			if(!empty(MyItem::getInstance()->getAllItems())){
				foreach(MyItem::getInstance()->getAllItems() as $item){
					foreach(glob(MyItem::getInstance()->getDataFolder()."items".DIRECTORY_SEPARATOR."*.yml") as $file) {
                        $config = new Config($file, Config::YAML);
			            $data = $config->getAll(\false);
						if($item->getCustomName() == $data["NAME"]){
							$name[$item->getCustomName()] = $data["NAME_RECIPE"];
						}
					}
				}
			}
			$item = $action->getSourceItem();
			$player = $action->getPlayer();
			switch($item->getCustomName()){
            	case "-":
					$action->setCancelled();
            	break;	
            	case TextFormat::GREEN."Text Page":
				    $action->setCancelled();
					if($this->pageDataPlayer[$player->getName()] < count(MyItem::getInstance()->dataItems)){
						$this->pageDataPlayer[$player->getName()] += 1;
						$player->removeCurrentWindow();	
					    $this->getScheduler()->scheduleDelayedTask(new OpenRecipes($this, $player), 10);
					}
            	break;
            	case TextFormat::RED."Reverse Page":
				    $action->setCancelled();
					if($this->pageDataPlayer[$player->getName()] > 0){
						$this->pageDataPlayer[$player->getName()] -= 1;						
						$player->removeCurrentWindow();	
					    $this->getScheduler()->scheduleDelayedTask(new OpenRecipes($this, $player), 10);
					}
            	break;
                case "Back":		
				    $action->setCancelled();
                    $player->removeCurrentWindow();	
					$this->getScheduler()->scheduleDelayedTask(new OpenRecipes($this, $player), 10);
                    if(isset($this->inRecipe[$player->getName()])){
				        unset($this->inRecipe[$player->getName()]);
					}					
				break;				
			}
			foreach($name as $check){
				if(!empty($name[$item->getCustomName()])){
					$action->setCancelled();
					$nameI = $name[$item->getCustomName()];		
                    if(empty($this->recipes[$nameI])){
			            return;
					}					
					$null = $this->getDataItem(26, 0, 1);
					$null->setCustomName("-");    
		
					for($i = 0; $i <= 53; $i++){
						if(!in_array($i, [10, 11, 12, 19, 20, 21, 28, 29, 30])){
							$menu->setItem($i, $null);
						}
					}	
		
					$back = $this->getDataItem(355, 14, 1);
					$back->setCustomName("Back");
					$menu->setItem(53, $back);
		
					$a = $this->recipes[$nameI][1];
					$b = $this->recipes[$nameI][2];
					$c = $this->recipes[$nameI][3];
					$d = $this->recipes[$nameI][4];
					$e = $this->recipes[$nameI][5];
					$f = $this->recipes[$nameI][6];
					$g = $this->recipes[$nameI][7];
					$h = $this->recipes[$nameI][8];
					$i = $this->recipes[$nameI][9];
					$result = $this->recipes[$nameI]["result"];
					$slots = [10 => $a, 11  => $b, 12  => $c, 19  => $d, 20  => $e, 21  => $f, 28  => $g, 29  => $h, 30  => $i];
					foreach($slots as $case => $slot){
						$item = $this->getDataItem((int) explode(",", $slot)[0], (int) explode(",", $slot)[1], (int) explode(",", $slot)[2]);
						if(explode(",", $slot)[3] != "false"){
							$item->setCustomName(explode(",", $slot)[3]);				
						}
						$menu->setItem($case, $item);
					}
					$menu->setItem(23, $result);
                    $this->inRecipe[$player->getName()] = $player;					
				}
			}
			if(isset($this->inRecipe[$player->getName()])){
				$action->setCancelled();
			}
		});
		$menu->send($player, function() use ($menu, $player) : void{
		    $nextPage = $this->getDataItem(262, 0, 1);
		    $nextPage->setCustomName(TextFormat::GREEN."Text Page");
		    $itemName = ["%page", "%maxpage"];
		    $array = [$this->pageDataPlayer[$player->getName()], count(MyItem::getInstance()->dataItems)];
		
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
			
			$recipe = [];
            if(!empty(MyItem::getInstance()->dataItems[$this->pageDataPlayer[$player->getName()]])){  
			    foreach(MyItem::getInstance()->dataItems[$this->pageDataPlayer[$player->getName()]] as $item){
			        foreach($this->recipes as $case => $data){
				        $itemCheck = $data["result"];
				        if($itemCheck->getCustomName() == $item->getCustomName()){
				            $recipe[$item->getCustomName()] = true; 	
						}
					}
				}
			}
		    if(!empty(MyItem::getInstance()->dataItems[$this->pageDataPlayer[$player->getName()]])){  
			    $items = MyItem::getInstance()->dataItems[$this->pageDataPlayer[$player->getName()]];
				foreach($items as $item){
					$menu->addItem($item);						
			    }
		    }		
        });			
	}
	
	public function seeRecipe(Player $player, string $name){		
		if(empty($this->recipes[$name])){
			//$player->sendMessage(TextFormat::RED."This recipe does not exist!");
			return;
		}
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Recipe Item');						
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$itemClicked = $action->getSourceItem();
			$player = $action->getPlayer();  
			switch($itemClicked->getCustomName()){
				case "Back":		
				    $action->setCancelled();
                    $player->removeCurrentWindow();	
					$this->getScheduler()->scheduleDelayedTask(new OpenRecipes($this, $player), 10);								
				break;
				default: $action->setCancelled();				
			}
		});
		$menu->send($player, function() use ($menu, $name, $player) : void{
			$null = $this->getDataItem(26, 0, 1);
			$null->setCustomName("-");    
		
			for($i = 0; $i <= 53; $i++){
				if(!in_array($i, [10, 11, 12, 19, 20, 21, 28, 29, 30])){
					$menu->setItem($i, $null);
				}
			}	
		
			$back = $this->getDataItem(355, 14, 1);
			$back->setCustomName("Back");
			$menu->setItem(53, $back);
		
			$a = $this->recipes[$name][1];
			$b = $this->recipes[$name][2];
			$c = $this->recipes[$name][3];
			$d = $this->recipes[$name][4];
			$e = $this->recipes[$name][5];
			$f = $this->recipes[$name][6];
			$g = $this->recipes[$name][7];
			$h = $this->recipes[$name][8];
			$i = $this->recipes[$name][9];
			$result = $this->recipes[$name]["result"];
			$slots = [10 => $a, 11  => $b, 12  => $c, 19  => $d, 20  => $e, 21  => $f, 28  => $g, 29  => $h, 30  => $i];
			foreach($slots as $case => $slot){
				$item = $this->getDataItem((int) explode(",", $slot)[0], (int) explode(",", $slot)[1], (int) explode(",", $slot)[2]);
				if(explode(",", $slot)[3] != "false"){
					$item->setCustomName(explode(",", $slot)[3]);				
				}
				$menu->setItem($case, $item);
			}
			$menu->setItem(23, $result);
		});
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK){
		    if($block->getId() == 247){
			    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 10);
			    $event->cancel();
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();   
		if($block->getId() == 247){
			$event->setDrops([$this->getDataItem(247, 0, 1)]);
		}
	}
	
	public function registerRecipes(){
		$this->recipes = [
	    //Name
		    //slot => id,meta,count,name,type:flag
			    // 1 2 3
			    // 4 5 6
			    // 7 8 9
			"MINER_HELMET" => [ 			    
				1 => "264,0,64,false,false",
				2 => "264,0,64,false,false",
				3 => "264,0,64,false,false",
				4 => "264,0,64,false,false",
				5 => "0,0,0,false,false",
				6 => "264,0,64,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("MINER_HELMET", 1)
			],
	    	"MINER_CHESTPLATE" => [ 			    
				1 => "264,0,64,false,false",
				2 => "0,0,0,false,false",
				3 => "264,0,64,false,false",
				4 => "264,0,64,false,false",
				5 => "264,0,64,false,false",
				6 => "264,0,64,false,false",
				7 => "264,0,64,false,false",
				8 => "264,0,64,false,false",
				9 => "264,0,64,false,false",
				"result" => MyItem::getInstance()->getItem("MINER_CHESTPLATE", 1)
			],		
			"MINER_LEGGINGS" => [ 			    
				1 => "264,0,64,false,false",
				2 => "264,0,64,false,false",
				3 => "264,0,64,false,false",
				4 => "264,0,64,false,false",
				5 => "0,0,0,false,false",
				6 => "264,0,64,false,false",
				7 => "264,0,64,false,false",
				8 => "0,0,0,false,false",
				9 => "264,0,64,false,false",
				"result" => MyItem::getInstance()->getItem("MINER_LEGGINGS", 1)
			],
			"MINER_BOOTS" => [ 			    
				1 => "264,0,64,false,false",
				2 => "0,0,0,false,false",
				3 => "264,0,64,false,false",
				4 => "264,0,64,false,false",
				5 => "0,0,0,false,false",
				6 => "264,0,64,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("MINER_BOOTS", 1)
			],	
            ///
			"GOD_HELMET" => [ 			    
				1 => "266,0,10,§6Gold's God,int:Rarity",
				2 => "266,0,10,§6Gold's God,int:Rarity",
				3 => "266,0,10,§6Gold's God,int:Rarity",
				4 => "266,0,10,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,10,§6Gold's God,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOD_HELMET", 1)
			],
	    	"GOD_CHESTPLATE" => [ 			    
				1 => "266,0,10,§6Gold's God,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "266,0,10,§6Gold's God,int:Rarity",
				4 => "266,0,10,§6Gold's God,int:Rarity",
				5 => "266,0,10,§6Gold's God,int:Rarity",
				6 => "266,0,10,§6Gold's God,int:Rarity",
				7 => "266,0,10,§6Gold's God,int:Rarity",
				8 => "266,0,10,§6Gold's God,int:Rarity",
				9 => "266,0,10,§6Gold's God,int:Rarity",
				"result" => MyItem::getInstance()->getItem("GOD_CHESTPLATE", 1)
			],		
			"GOD_LEGGINGS" => [ 			    
				1 => "266,0,10,§6Gold's God,int:Rarity",
				2 => "266,0,10,§6Gold's God,int:Rarity",
				3 => "266,0,10,§6Gold's God,int:Rarity",
				4 => "266,0,10,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,10,§6Gold's God,int:Rarity",
				7 => "266,0,10,§6Gold's God,int:Rarity",
				8 => "0,0,0,false,false",
				9 => "266,0,10,§6Gold's God,int:Rarity",
				"result" => MyItem::getInstance()->getItem("GOD_LEGGINGS", 1)
			],
			"GOD_BOOTS" => [ 			    
				1 => "266,0,10,§6Gold's God,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "266,0,10,§6Gold's God,int:Rarity",
				4 => "266,0,10,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,10,§6Gold's God,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOD_BOOTS", 1)
			],	
            ///			
			"PROTECTOR_HELMET" => [ 			    
				1 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				2 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				3 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				4 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("PROTECTOR_HELMET", 1)
			],
	    	"PROTECTOR_CHESTPLATE" => [ 			    
				1 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				4 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				5 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				6 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				7 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				8 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				9 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				"result" => MyItem::getInstance()->getItem("PROTECTOR_CHESTPLATE", 1)
			],		
			"PROTECTOR_LEGGINGS" => [ 			    
				1 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				2 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				3 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				4 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				7 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				8 => "0,0,0,false,false",
				9 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				"result" => MyItem::getInstance()->getItem("PROTECTOR_LEGGINGS", 1)
			],
			"PROTECTOR_BOOTS" => [ 			    
				1 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				4 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "265,0,20,§5Special Iron Ingot,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("PROTECTOR_BOOTS", 1)
			],
			///
			"ENDER_SWORD" => [ 			    
				1 => "0,0,0,false,false",
				2 => "381,0,2,§5Special Ender Eye,int:Rarity",
				3 => "0,0,0,false,false",
				4 => "0,0,0,false,false",
				5 => "381,0,2,§5Special Ender Eye,int:Rarity",
				6 => "381,0,2,§5Special Ender Eye,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("ENDER_SWORD", 1)
			],
			///
			"ZEUS_AXE" => [ 			    
				1 => "266,0,5,§6Gold's God,int:Rarity",
				2 => "266,0,5,§6Gold's God,int:Rarity",
				3 => "0,0,0,false,false",
				4 => "266,0,5,§6Gold's God,int:Rarity",
				5 => "280,0,1,§5Zeus's Handle,int:Rarity",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,§5Zeus's Handle,int:Rarity",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("ZEUS_AXE", 1)
			],
			"MURAMASAS_SWORD" => [ 			    
				1 => "0,0,0,false,false",
				2 => "265,0,64,§5Special Iron Ingot,int:Rarity",
				3 => "0,0,0,false,false",
				4 => "265,0,64,§5Special Iron Ingot,int:Rarity",
				5 => "265,0,64,§5Special Iron Ingot,int:Rarity",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("MURAMASAS_SWORD", 1)
			],
			"JACOB_HOE" => [ 			    
				1 => "266,0,2,§6Gold's God,int:Rarity",
				2 => "266,0,2,§6Gold's God,int:Rarity",
				3 => "266,0,2,§6Gold's God,int:Rarity",
				4 => "170,0,64,false,false",
				5 => "280,0,10,false,false",
				6 => "170,0,64,false,false",
				7 => "170,0,64,false,false",
				8 => "280,0,10,false,false",
				9 => "170,0,64,false,false",
				"result" => MyItem::getInstance()->getItem("JACOB_HOE", 1)
			],
			"COOKIE_HOE" => [ 			    
				1 => "357,0,10,false,false",
				2 => "357,0,10,false,false",
				3 => "357,0,10,false,false",
				4 => "0,0,0,false,false",
				5 => "280,0,1,false,false",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("COOKIE_HOE", 1)
			],
			"PRO_PICKAXE" => [ 			    
				1 => "264,0,10,false,false",
				2 => "264,0,10,false,false",
				3 => "264,0,5,false,false",
				4 => "0,0,0,false,false",
				5 => "280,0,1,false,false",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("PRO_PICKAXE", 1)
			],
			"PIGGY_PICKAXE" => [ 			    
				1 => "371,0,64,false,false",
				2 => "371,0,64,false,false",
				3 => "371,0,64,false,false",
				4 => "0,0,0,false,false",
				5 => "280,0,5,false,false",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,5,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("PIGGY_PICKAXE", 1)
			],
			"GOM_PICKAXE" => [ 			    
				1 => "266,0,15,§6Gom Fragment,int:Rarity",
				2 => "266,0,15,§6Gom Fragment,int:Rarity",
				3 => "266,0,15,§6Gom Fragment,int:Rarity",
				4 => "0,0,0,false,false",
				5 => "280,0,1,false,false",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOM_PICKAXE", 1)
			],
			"GOOD_AXE" => [ 			    
				1 => "265,0,10,false,false",
				2 => "265,0,10,false,false",
				3 => "0,0,0,false,false",
				4 => "265,0,10,false,false",
				5 => "280,0,1,false,false",
				6 => "0,0,0,false,false",
				7 => "0,0,0,false,false",
				8 => "280,0,1,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOOD_AXE", 1)
			],
			///
			"ZEUS_HELMET" => [ 			    
				1 => "266,0,8,§6Gold's God,int:Rarity",
				2 => "266,0,8,§6Gold's God,int:Rarity",
				3 => "266,0,8,§6Gold's God,int:Rarity",
				4 => "266,0,8,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,8,§6Gold's God,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("ZEUS_HELMET", 1)
			],
	    	"ZEUS_CHESTPLATE" => [ 			    
				1 => "266,0,8,§6Gold's God,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "266,0,8,§6Gold's God,int:Rarity",
				4 => "266,0,8,§6Gold's God,int:Rarity",
				5 => "266,0,8,§6Gold's God,int:Rarity",
				6 => "266,0,8,§6Gold's God,int:Rarity",
				7 => "266,0,8,§6Gold's God,int:Rarity",
				8 => "266,0,8,§6Gold's God,int:Rarity",
				9 => "266,0,8,§6Gold's God,int:Rarity",
				"result" => MyItem::getInstance()->getItem("ZEUS_CHESTPLATE", 1)
			],		
			"ZEUS_LEGGINGS" => [ 			    
				1 => "266,0,8,§6Gold's God,int:Rarity",
				2 => "266,0,8,§6Gold's God,int:Rarity",
				3 => "266,0,8,§6Gold's God,int:Rarity",
				4 => "266,0,8,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,8,§6Gold's God,int:Rarity",
				7 => "266,0,8,§6Gold's God,int:Rarity",
				8 => "0,0,0,false,false",
				9 => "266,0,8,§6Gold's God,int:Rarity",
				"result" => MyItem::getInstance()->getItem("ZEUS_LEGGINGS", 1)
			],
			"ZEUS_BOOTS" => [ 			    
				1 => "266,0,8,§6Gold's God,int:Rarity",
				2 => "0,0,0,false,false",
				3 => "266,0,8,§6Gold's God,int:Rarity",
				4 => "266,0,8,§6Gold's God,int:Rarity",
				5 => "0,0,0,false,false",
				6 => "266,0,8,§6Gold's God,int:Rarity",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("ZEUS_BOOTS", 1)
			],	
			///
			"GOBLIN_HELMET" => [ 			    
				1 => "334,0,10,false,false",
				2 => "334,0,10,false,false",
				3 => "334,0,10,false,false",
				4 => "334,0,10,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,10,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOBLIN_HELMET", 1)
			],
	    	"GOBLIN_CHESTPLATE" => [ 			    
				1 => "334,0,10,false,false",
				2 => "0,0,0,false,false",
				3 => "334,0,10,false,false",
				4 => "334,0,10,false,false",
				5 => "334,0,10,false,false",
				6 => "334,0,10,false,false",
				7 => "334,0,10,false,false",
				8 => "334,0,10,false,false",
				9 => "334,0,10,false,false",
				"result" => MyItem::getInstance()->getItem("GOBLIN_CHESTPLATE", 1)
			],		
			"GOBLIN_LEGGINGS" => [ 			    
				1 => "334,0,10,false,false",
				2 => "334,0,10,false,false",
				3 => "334,0,10,false,false",
				4 => "334,0,10,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,10,false,false",
				7 => "334,0,10,false,false",
				8 => "0,0,0,false,false",
				9 => "334,0,10,false,false",
				"result" => MyItem::getInstance()->getItem("GOBLIN_LEGGINGS", 1)
			],
			"GOBLIN_BOOTS" => [ 			    
				1 => "334,0,10,false,false",
				2 => "0,0,0,false,false",
				3 => "334,0,10,false,false",
				4 => "334,0,10,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,10,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOBLIN_BOOTS", 1)
			],
            ///
			"GOOD_HELMET" => [ 			    
				1 => "334,0,2,false,false",
				2 => "334,0,2,false,false",
				3 => "334,0,2,false,false",
				4 => "334,0,2,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,2,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOOD_HELMET", 1)
			],
	    	"GOOD_CHESTPLATE" => [ 			    
				1 => "334,0,2,false,false",
				2 => "0,0,0,false,false",
				3 => "334,0,2,false,false",
				4 => "334,0,2,false,false",
				5 => "334,0,2,false,false",
				6 => "334,0,2,false,false",
				7 => "334,0,2,false,false",
				8 => "334,0,2,false,false",
				9 => "334,0,2,false,false",
				"result" => MyItem::getInstance()->getItem("GOOD_CHESTPLATE", 1)
			],		
			"GOOD_LEGGINGS" => [ 			    
				1 => "334,0,2,false,false",
				2 => "334,0,2,false,false",
				3 => "334,0,2,false,false",
				4 => "334,0,2,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,2,false,false",
				7 => "334,0,2,false,false",
				8 => "0,0,0,false,false",
				9 => "334,0,2,false,false",
				"result" => MyItem::getInstance()->getItem("GOOD_LEGGINGS", 1)
			],
			"GOOD_BOOTS" => [ 			    
				1 => "334,0,2,false,false",
				2 => "0,0,0,false,false",
				3 => "334,0,2,false,false",
				4 => "334,0,2,false,false",
				5 => "0,0,0,false,false",
				6 => "334,0,2,false,false",
				7 => "0,0,0,false,false",
				8 => "0,0,0,false,false",
				9 => "0,0,0,false,false",
				"result" => MyItem::getInstance()->getItem("GOOD_BOOTS", 1)
			],				
		];
	}
}