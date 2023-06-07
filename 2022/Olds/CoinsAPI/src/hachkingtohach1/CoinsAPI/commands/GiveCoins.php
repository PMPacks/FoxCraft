<?php

namespace hachkingtohach1\CoinsAPI\commands;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use hachkingtohach1\CoinsAPI\CoinsAPI;

class GiveCoins extends Command implements PluginIdentifiableCommand{

    public function __construct(){
        parent::__construct("giveCoins", "/giveCoins <player> <amount>", ("/giveCoins <player>"), ["gico"]);
    }
	
    public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player) return;
		if(!empty($args[0]) and !empty($args[1]) and is_numeric($args[1])){
			foreach(CoinsAPI::getInstance()->getServer()->getOnlinePlayers() as $player){
			    if(strtolower($player->getName()) == strtolower($args[0])){
                    CoinsAPI::getInstance()->giveCoins($player, $args[1]);					
					$sender->sendMessage(TextFormat::BOLD."Coins cho người chơi đó là: ".$args[1]);			
				}
			}
		}
    }

    public function getPlugin(): Plugin{
        return CoinsAPI::getInstance();
    }
}