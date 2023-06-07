<?php

namespace hachkingtohach1\Market;

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
use pocketmine\world\sound\EndermanTeleportSound;
use skymin\InventoryLib\{InvLibManager, LibInvType, InvLibAction, LibInventory};
use hachkingtohach1\Market\provider\{DataBase, sql\SQL};
use hachkingtohach1\Market\utils\Math;
use hachkingtohach1\Market\task\OpenGui;
use hachkingtohach1\Market\task\SeeItem;

class Market extends PluginBase implements Listener {
	
	public $dataBase;
	public $dataUser = [];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): Market{
        return self::$instance;
    }

    public function onEnable() :void{
		$this->dataBase = new SQL("mysql");    
		InvLibManager::register($this);
		$this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function getDatabase(): Database{
        return $this->dataBase;
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "mk"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			$this->market($sender);
			return true;
        }
		if($command->getName() == "mks"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(TextFormat::RED."/mks <time:seconds>");
				return false;
			}
			//
			$time = time() + (int)$args[0];		
			$rand = rand(1, 1000);
			$generateId = $rand.$sender->getName();
			$item = $sender->getInventory()->getItemInHand();
			if(!$this->getDatabase()->itemExists($generateId)){						
                $this->getDataBase()->addItemsSold($sender, $generateId);
				$this->getDataBase()->createItem($sender, $generateId, json_encode($item->jsonSerialize()), $time);
				$sender->sendMessage(TextFormat::GREEN."Item has created! /mkm to check it.");
			}
			return true;
        }
		if($command->getName() == "mkm"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			$this->managerItems($sender);
			return true;
        }
		return false;
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function market(Player $player, int $index, string $category){       
		$this->dataUser[$player->getXuid] = [];	
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Market');
		$count = 0;
		$page = 1;
		$items = [];
		foreach($this->getDatabase()->getAll() as $id => $data){				
			$itemsSold = explode(",", $this->getDatabase()->getItemsSold($data["xuid"]));
			if(!in_array($id, $itemsSold) and $count <= 28){
                $item = Item::nbtDeserialize($data["item"]);
			    $nbt = $item->getNamedTag();
				if($nbt->getTag("Category", StringTag::class) != null){                    					
					if($nbt->getString("Category") == $category){
						$seller = $data["seller"];
			            $xuid = $data["xuid"];		
			            $price = $data["price"];
                        $time = $data["time"];
						$sold = $data["sold"];
						$nbt->setString("Code", "$id");						
					    $newLore = [];
						foreach($item->getLore() as $lore){
							$newLore[] = $lore;
						}											
						$newLore[] = TextFormat::BOLD.TextFormat::DARK_GRAY."────────────────";
						$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Người bán: $seller";
						$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Giá bán: ".TextFormat::GOLD.$price;
           				$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Tình trạng: ".Math::calculateTime($time);
						$newLore[] = TextFormat::BOLD.TextFormat::RED."";
						$newLore[] = TextFormat::BOLD.TextFormat::YELLOW."Chạm để xem!";
						$item->setLore($newLore);                        
		                $item->setNamedTag($nbt);						
						if($sold == 0 and Math::calculateTime($time) != TextFormat::GREEN."Ended!"){							
						    $items[$page][] = [$item, Item::nbtDeserialize($data["item"]), $seller, $price, $time, $id, $xuid, $data];
						    $count++;
						}			
					}
				}					
			}else{
			    $count = 0;
			    $page++;
			}
		}		
		$total = count($items);
		
		foreach($items[$index] as $case => [$sample, $item, $seller, $price, $time, $id, $xuid, $data]){
			$menu->addItem($sample);
		}		
		
		$weapons = $this->getDataItem(268, 0, 1);
		$weapons->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Vũ khí");  
		$menu->setItem(1, $weapons);
		
		$armors = $this->getDataItem(299, 0, 1);
		$armors->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Giáp");  
		$menu->setItem(2, $armors);
		
		$potions = $this->getDataItem(438, 5, 1);
		$potions->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Thuốc");  
		$menu->setItem(3, $potions);
		
		$enchants = $this->getDataItem(375, 5, 1);
		$enchants->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Bùa phép");  
		$menu->setItem(4, $enchants);
		
		$others = $this->getDataItem(280, 5, 1);
		$others->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Những cái khác");  
		$menu->setItem(5, $others);
		
		$reversePage = $this->getDataItem(262, 0, 1);
		$reversePage->setCustomName(TextFormat::BOLD.TextFormat::RED."Lùi trang".TextFormat::WHITE."($page/$total)");  
		$menu->setItem(51, $nextPage);
		
		$nextPage = $this->getDataItem(262, 0, 1);
		$nextPage->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Sang trang".TextFormat::WHITE."($page/$total)");  
		$menu->setItem(47, $nextPage);	
		
		$null = $this->getDataItem(95, 0, 1);
		$null->setCustomName("-");  
		for($i = 0; $i <= 53; $i++){
			if($menu->getItem($i)->getId() == 0){
			    $menu->setItem($i, $null);
			}
		}
		
		// Page, Category, Items, ItemsNow, Menu
		$this->dataUser[$player->getXuid] = [$index, $category, $items, $items[$index], $menu];
		
        $menu->send($player);		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$itemClicked = $action->getSourceItem();
			$player = $action->getPlayer();
			$page = $this->dataUser[$player->getXuid][0];
			$total = $this->dataUser[$player->getXuid][2];
            switch($itemClicked->getCustomName()){
            	case "-":
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Vũ khí":
				    $data = $this->dataUser[$player->getXuid];
					$this->dataUser[$player->getXuid] = [$data[0], "WEAPONS", $data[2], $data[3], $data[4]];
				    $player->removeCurrentWindow();
				    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Giáp":
					$data = $this->dataUser[$player->getXuid];
					$this->dataUser[$player->getXuid] = [$data[0], "ARMORS", $data[2], $data[3], $data[4]];
				    $player->removeCurrentWindow();
				    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Thuốc":
					$data = $this->dataUser[$player->getXuid];
					$this->dataUser[$player->getXuid] = [$data[0], "POTIONS", $data[2], $data[3], $data[4]];
				    $player->removeCurrentWindow();
				    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Bùa phép":
					$data = $this->dataUser[$player->getXuid];
					$this->dataUser[$player->getXuid] = [$data[0], "ENCHANTMENTS", $data[2], $data[3], $data[4]];
				    $player->removeCurrentWindow();
				    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Những cái khác":
					$data = $this->dataUser[$player->getXuid];
					$this->dataUser[$player->getXuid] = [$data[0], "OTHERS", $data[2], $data[3], $data[4]];
				    $player->removeCurrentWindow();
				    $this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::RED."Lùi trang".TextFormat::WHITE."($page/$total)":
					$data = $this->dataUser[$player->getXuid];
					if($data[0] >= 1){
						$this->dataUser[$player->getXuid] = [($data[0] - 1), $data[1], $data[2], $data[3], $data[4]];
						$player->removeCurrentWindow();
				    	$this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					}else{
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
					}
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Sang trang".TextFormat::WHITE."($page/$total)":
					$data = $this->dataUser[$player->getXuid];
					if(count($data[2]) < $data[0]){
						$this->dataUser[$player->getXuid] = [($data[0] + 1), $data[1], $data[2], $data[3], $data[4]];
						$player->removeCurrentWindow();
				    	$this->getScheduler()->scheduleDelayedTask(new OpenGUI($this, $player), 20);
					}else{
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
					}
					$action->setCancelled();
                break;
			}
			$nbt = $itemClicked->getNamedTag();
            foreach($this->dataUser[$player->getXuid][4] as $case => $data){
				if($nbt->getTag("Code", StringTag::class) != null){
					if($nbt->getString("Code") == $data[5]){
						$this->getScheduler()->scheduleDelayedTask(new SeeItem($this, $player, $data[1], $data[2], $data[6], $data[3], $data[5]), 20);
					}
				}
			}				
		});
		$menu->setCloseListener(function(Player $player) use ($menu) : void{});
	}
	
	public function seeItem(Player $player, Item $item, string $seller, int $xuid, int $price, string $id){
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), $seller."'s item");
		
		$menu->setItem(22, $item);
		
		$accept = $this->getDataItem(35, 5, 1);
		$accept->setCustomName(TextFormat::BOLD.TextFormat::GREEN."Chấp nhận mua");  
		$menu->setItem(40, $accept);
		
		$null = $this->getDataItem(95, 0, 1);
		$null->setCustomName("-");  
		for($i = 0; $i <= 53; $i++){
			if($menu->getItem($i)->getId() == 0){
			    $menu->setItem($i, $null);
			}
		}		
		$menu->send($player);		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$itemClicked = $action->getSourceItem();
			$player = $action->getPlayer();
			switch($item->getCustomName()){
            	case "-":
					$action->setCancelled();
                break;
				case TextFormat::BOLD.TextFormat::GREEN."Chấp nhận mua":
				    $api = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
					$money = $api->myMoney($player->getName());
				    if($money >= $price){
						$this->getDatabase()->addItemsSold($xuid, $id);
						$api->reduceMoney($player, $price);
					}else{
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
					}
					$action->setCancelled();
                break;
			}
		});
		$menu->setCloseListener(function(Player $player) use ($menu) : void{});
	}
	
	public function managerItems(Player $player){
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), "Manager");
		
		$count = 0;
		$page = 1;
		$items = [];
		$itemsId = explode(",", $this->getDataBase()->getItemsPlayer($player));
		foreach($itemsId as $id){
			$data = $this->getDataBase()->getItem($id);
			$item = Item::nbtDeserialize($data["item"]);
			$nbt = $item->getNamedTag();
			if($nbt->getTag("Category", StringTag::class) != null){                    					
				if($nbt->getString("Category") == $category){
					$seller = $data["seller"];
			        $xuid = $data["xuid"];		
			        $price = $data["price"];
                    $time = $data["time"];
					$sold = $data["sold"];
					$nbt->setString("Code", "$id");						
					$newLore = [];
					foreach($item->getLore() as $lore){
						$newLore[] = $lore;
					}											
					$newLore[] = TextFormat::BOLD.TextFormat::DARK_GRAY."────────────────";
					$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Người bán: $seller";
                    $newLore[] = TextFormat::BOLD.TextFormat::GRAY."Giá bán: ".TextFormat::GOLD.$price;
           			$newLore[] = TextFormat::BOLD.TextFormat::GRAY."Tình trạng: ".Math::calculateTime($time);
					$newLore[] = TextFormat::BOLD.TextFormat::RED."";
					$newLore[] = TextFormat::BOLD.TextFormat::YELLOW."Chạm để xem!";
					$item->setLore($newLore);                        
		            $item->setNamedTag($nbt);						
					if($sold == 0 and Math::calculateTime($time) != TextFormat::GREEN."Ended!"){							
						$items[$page][] = [$item, Item::nbtDeserialize($data["item"]), $seller, $price, $time, $id, $xuid, $data];
						$count++;
					}
				}
			}				
		}
		
	}
}