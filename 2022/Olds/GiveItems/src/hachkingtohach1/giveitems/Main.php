<?php

namespace hachkingtohach1\giveitems;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){}
	
	public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args) :bool{
		switch($cmd->getName()){
			case "giveitem":
			    if(!$sender->isOp()){
					$sender->sendMessage("You are need permission!");
					break;
				}
				if(!isset($args[0]) or !isset($args[1]) or !isset($args[2]) or !isset($args[3])){
					$sender->sendMessage("/giveitem <id> <meta> <count> <player> <name_item>");
					break;
				}
                if(isset($args[4])){
					$array = [];
					for($i = 4; $i <= 100; $i++){
						if(isset($args[$i])){
							$array[] = $args[$i];
						}
					}					
					$this->giveItems($args[0], $args[1], $args[2], $args[3], implode(" ", $array));
				} else {
					$this->giveItems($args[0], $args[1], $args[2], $args[3], false);
				}					
			break;
		}
		return false;
	}
	
	public function giveItems(int $id, int $meta, int $count, string $nameplayer, $nameitem = false){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) == strtolower($nameplayer)){
		        $item = Item::get($id, $meta, $count);
		        if($nameitem !== false){
		            $item->setCustomName($nameitem);
				}	
		        $player->getInventory()->addItem($item);
			}
		}
	}
}