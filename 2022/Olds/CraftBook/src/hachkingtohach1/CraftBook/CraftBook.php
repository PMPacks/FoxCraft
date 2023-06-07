<?php

namespace hachkingtohach1\CraftBook;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\InvMenu;
use hachkingtohach1\EnderSword\EnderSword;

class CraftBook extends PluginBase implements Listener {
	
	public CONST BLOCKS = 0;
	public CONST ARMORS = 1;
	public CONST WEAPONS = 2;
	public CONST POTIONS = 3;
	public CONST OTHERS = 4;
	
	public $items = [];
	public $dataItems = [];
	public $itemSeeing = [];
	public $pageDataPlayer = [];	
	
	private static $instance = null;
	
	public function onLoad(){
        self::$instance = $this;
	}
	
	public static function getInstance(): CraftBook{
        return self::$instance;
    }

    public function onEnable(){
		if(!InvMenuHandler::isRegistered()){
	        InvMenuHandler::register($this);
		} 
		$this->saveDefaultConfig();
		$this->items = [
	        "§l§l§r".TextFormat::DARK_PURPLE."Ender Sword".TextFormat::RED."§l§l§l§1" => [
		        "ITEM" => EnderSword::getInstance()->getEnderSword(),
			    "MIXED_NEEDLE" => [
			        "Eye of Ender" => "381,0,164",
				    "Stick" => "280,0,2"
			    ],
			    "CATEGORY" => self::WEAPONS
		    ]
	    ];
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "craftbook"){
			$this->craftBook($sender);
			return true;
        }
		if($command->getName() == "cbc"){
			if(!empty($args[0])){
				if(!in_array($args[0], ["blocks", "armors", "weapons", "potions", "others"])){
					$sender->sendMessage(TextFormat::GREEN."/cbc <category:blocks,armors,weapons,potions,others>");
					return false;
				}
				switch($args[0]){
					case "blocks":
					    $category = self::BLOCKS;
					break;
					case "armors":
					    $category = self::ARMORS;
					break;
					case "weapons":
					    $category = self::WEAPONS;
					break;
					case "potions":
					    $category = self::POTIONS;
					break;
					case "others":
					    $category = self::OTHERS;
					break;
				}
				$this->craftListBook($sender, $category);
			}else{
				$sender->sendMessage(TextFormat::GREEN."/cbc <category:blocks,armors,weapons,potions,others>");
			}			
			return true;
        }
		return false;
	}
	
	public function craft(Player $player, Item $item){
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);		
		$menu->setName($item->getCustomName());
		
		$this->itemSeeing[$player->getName()] = $item;
		
		$menu->getInventory()->setItem(22, $item);
		
		$craftItemConfig = $this->getConfig()->get("CRAFT_ITEM");
		$craftItem = Item::get($craftItemConfig["ID"], $craftItemConfig["META"], $craftItemConfig["COUNT"]);
		$craftItem->setCustomName($craftItemConfig["NAME"]);
		
		$loreItem = [];		
		foreach($this->items[$item->getCustomName()]["MIXED_NEEDLE"] as $name => $itemNeed){
			if(!empty(explode(",", $itemNeed)[3])){
			    $loreItem[] = TextFormat::GREEN.explode(",", $itemNeed)[3].TextFormat::GRAY." x".explode(",", $itemNeed)[2];
			}else{
				$loreItem[] = TextFormat::GREEN.$name.TextFormat::GRAY." x".explode(",", $itemNeed)[2];
			}
		}			
		$craftItem->setLore($loreItem);	     			
		$menu->getInventory()->setItem(40, $craftItem);
		
		$null = Item::get(27, 0, 1);
		$null->setCustomName("-");       						
		for($slot = 0; $slot <= 53; $slot++){
			if($menu->getInventory()->getItem($slot)->getId() === 0){
			    $menu->getInventory()->setItem($slot, $null);
			}
		}
		$menu->send($player);				
		$menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult{
	        $player = $transaction->getPlayer();
	        $itemClicked = $transaction->getItemClicked();
			$inventory = $transaction->getAction()->getInventory();
			$nameItem = $itemClicked->getCustomName();
			switch($nameItem){
                case "-":
					return $transaction->discard();
                break;
                case $this->getConfig()->get("CRAFT_ITEM")["NAME"]:
					$count = 0;
					$itemNeedRemove = [];
					foreach($player->getInventory()->getContents() as $case => $checkInventory){
						foreach($this->items[$this->itemSeeing[$player->getName()]->getCustomName()]["MIXED_NEEDLE"] as $name => $itemNeed){						   
							if($checkInventory->getId() == explode(",", $itemNeed)[0] 
							    and $checkInventory->getDamage() == explode(",", $itemNeed)[1]
								and $checkInventory->getCount() >= explode(",", $itemNeed)[2]
							){
							    if(!empty(explode(",", $itemNeed)[3])){
								    if(explode(",", $itemNeed)[3] == $checkInventory->getCustomName()){
									    $count++;
									    $itemNeedRemove[] = [
								            "ID" => explode(",", $itemNeed)[0], 
									        "META" => explode(",", $itemNeed)[1],
									        "COUNT" => explode(",", $itemNeed)[2],
									        "NAME" => explode(",", $itemNeed)[3]
								        ];	
									}										
								}else{
									$count++;
									$itemNeedRemove[] = [
								       "ID" => explode(",", $itemNeed)[0], 
									   "META" => explode(",", $itemNeed)[1],
									   "COUNT" => explode(",", $itemNeed)[2],
									   "NAME" => false 
								    ];
								}								
							}
						} 						
				    }
					if($count >= count($this->items[$this->itemSeeing[$player->getName()]->getCustomName()]["MIXED_NEEDLE"])){
						foreach($itemNeedRemove as $item){
							foreach($player->getInventory()->getContents() as $case => $checkInventory){
							    if($item["NAME"] != false and $item["NAME"] == $checkInventory->getCustomName()){
								    $checkInventory->setCount($checkInventory->getCount() - $item["COUNT"]);
                                    $player->getInventory()->setItem($case, $checkInventory);
							    }else{
								    $player->getInventory()->removeItem(Item::get($item["ID"], $item["META"], $item["COUNT"])); 
								}
							}
						}
						$player->sendMessage($this->getConfig()->get("MESSAGE_YOU_ENOGHT"));
						$player->getInventory()->addItem($this->itemSeeing[$player->getName()]);
						$player->removeWindow($inventory);
						$player->getLevel()->broadcastLevelSoundEvent($player->asVector3(), LevelSoundEventPacket::SOUND_RANDOM_ANVIL_USE);
					}else{
						$player->sendMessage($this->getConfig()->get("MESSAGE_YOU_NOT_ENOGHT"));
						$player->removeWindow($inventory);
					}
					return $transaction->discard();
                break;				
			} 		
			return $transaction->continue();				    
		});
	}
	
	public function craftListBook(Player $player, int $category){
		if(empty($this->pageDataPlayer[$player->getName()])){
			$this->pageDataPlayer[$player->getName()] = 1;
		}
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName($this->getConfig()->get("TITLE_MENU"));
		
		$nextPageConfig = $this->getConfig()->get("NEXT_PAGE_ITEM");
		$nextPage = Item::get($nextPageConfig["ID"], $nextPageConfig["META"], $nextPageConfig["COUNT"]);
		$nextPage->setCustomName($nextPageConfig["NAME"]);
		$itemName = ["%page", "%maxpage"];
		$array = [$this->pageDataPlayer[$player->getName()], count($this->items)];
		$loreItem = [];
		
		foreach($nextPageConfig["LORE"] as $lore){
			$loreItem[] = str_replace($itemName, $array, $lore);
		}
		
		$nextPage->setLore($loreItem);
		$menu->getInventory()->setItem(8, $nextPage);
		
		$reversePageConfig = $this->getConfig()->get("NEXT_REVERSE_ITEM");
		$reversePage = Item::get($reversePageConfig["ID"], $reversePageConfig["META"], $reversePageConfig["COUNT"]);
		$reversePage->setCustomName($reversePageConfig["NAME"]);
		$reversePage->setLore($loreItem);
		$menu->getInventory()->setItem(0, $reversePage);				
		
		$null = Item::get(27, 0, 1);
		$null->setCustomName("-");       						
		for($slot = 1; $slot <= 7; $slot++){
			$menu->getInventory()->setItem($slot, $null);
		}
		
		$count = 0;
		$page = 1;
		$items = [];
		if(!empty($this->items)){
			foreach($this->items as $item){
				if($item["CATEGORY"] == $category){
				    if($count <= 44){
				        $items[$page][] = $item;
					    $count++;
					}else{
					    $count = 0;
					    $page++;
					}
				}
			}
		}
		$this->dataItems[$player->getName()] = $items;
		if(!empty($items[$this->pageDataPlayer[$player->getName()]])){
		    foreach($items[$this->pageDataPlayer[$player->getName()]] as $dataItems){
			    $menu->getInventory()->addItem($dataItems["ITEM"]);
			}
		}
		
        $menu->send($player);		
		$menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult{
	        $player = $transaction->getPlayer();
	        $itemClicked = $transaction->getItemClicked();
			$inventory = $transaction->getAction()->getInventory();
			$nameItem = $itemClicked->getCustomName();
			switch($nameItem){
                case "-":
					return $transaction->discard();
                break;	
                case $this->getConfig()->get("NEXT_PAGE_ITEM")["NAME"]:
				    if($this->pageDataPlayer[$player->getName()] < count($this->items)){
						$this->pageDataPlayer[$player->getName()] += 1;
					}else{
						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, mt_rand());
					}
					return $transaction->discard();
                break;
                case $this->getConfig()->get("NEXT_REVERSE_ITEM")["NAME"]:
				    if($this->pageDataPlayer[$player->getName()] > 0){
						$this->pageDataPlayer[$player->getName()] -= 1;
					}else{
						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, mt_rand());
					}
					return $transaction->discard();
                break;				
			} 		
			foreach($this->dataItems[$player->getName()][$this->pageDataPlayer[$player->getName()]] as $dataItems){
			    if($nameItem == $dataItems["ITEM"]->getCustomName()){
					$this->craft($player, $dataItems["ITEM"]);
					return $transaction->discard();
				}					
			}
			return $transaction->continue();				    
		});
	}
	
	public function craftBook(Player $player){
		if(empty($this->pageDataPlayer[$player->getName()])){
			$this->pageDataPlayer[$player->getName()] = 1;
		}
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName($this->getConfig()->get("TITLE_MENU"));
		
		$nextPageConfig = $this->getConfig()->get("NEXT_PAGE_ITEM");
		$nextPage = Item::get($nextPageConfig["ID"], $nextPageConfig["META"], $nextPageConfig["COUNT"]);
		$nextPage->setCustomName($nextPageConfig["NAME"]);
		$itemName = ["%page", "%maxpage"];
		$array = [$this->pageDataPlayer[$player->getName()], count($this->items)];
		$loreItem = [];
		
		foreach($nextPageConfig["LORE"] as $lore){
			$loreItem[] = str_replace($itemName, $array, $lore);
		}
		
		$nextPage->setLore($loreItem);
		$menu->getInventory()->setItem(8, $nextPage);
		
		$reversePageConfig = $this->getConfig()->get("NEXT_REVERSE_ITEM");
		$reversePage = Item::get($reversePageConfig["ID"], $reversePageConfig["META"], $reversePageConfig["COUNT"]);
		$reversePage->setCustomName($reversePageConfig["NAME"]);
		$reversePage->setLore($loreItem);
		$menu->getInventory()->setItem(0, $reversePage);				
		
		$null = Item::get(27, 0, 1);
		$null->setCustomName("-");       						
		for($slot = 1; $slot <= 7; $slot++){
			$menu->getInventory()->setItem($slot, $null);
		}
		
		$count = 0;
		$page = 1;
		$items = [];
		if(!empty($this->items)){
			foreach($this->items as $item){
				if($count <= 44){
				    $items[$page][] = $item;
					$count++;
				}else{
					$count = 0;
					$page++;
				}
			}
		}
		$this->dataItems[$player->getName()] = $items;
		if(!empty($items[$this->pageDataPlayer[$player->getName()]])){
		    foreach($items[$this->pageDataPlayer[$player->getName()]] as $dataItems){
			    $menu->getInventory()->addItem($dataItems["ITEM"]);
			}
		}
		
        $menu->send($player);		
		$menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult{
	        $player = $transaction->getPlayer();
	        $itemClicked = $transaction->getItemClicked();
			$inventory = $transaction->getAction()->getInventory();
			$nameItem = $itemClicked->getCustomName();
			switch($nameItem){
                case "-":
					return $transaction->discard();
                break;	
                case $this->getConfig()->get("NEXT_PAGE_ITEM")["NAME"]:
				    if($this->pageDataPlayer[$player->getName()] < count($this->items)){
						$this->pageDataPlayer[$player->getName()] += 1;
					}else{
						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, mt_rand());
					}
					return $transaction->discard();
                break;
                case $this->getConfig()->get("NEXT_REVERSE_ITEM")["NAME"]:
				    if($this->pageDataPlayer[$player->getName()] > 0){
						$this->pageDataPlayer[$player->getName()] -= 1;
					}else{
						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, mt_rand());
					}
					return $transaction->discard();
                break;				
			} 		
			foreach($this->dataItems[$player->getName()][$this->pageDataPlayer[$player->getName()]] as $dataItems){
			    if($nameItem == $dataItems["ITEM"]->getCustomName()){
					$this->craft($player, $dataItems["ITEM"]);
					return $transaction->discard();
				}					
			}
			return $transaction->continue();				    
		});
	}
}