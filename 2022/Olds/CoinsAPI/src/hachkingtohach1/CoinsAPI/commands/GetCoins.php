<?php

namespace hachkingtohach1\CoinsAPI\commands;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use hachkingtohach1\CoinsAPI\CoinsAPI;

class GetCoins extends Command implements PluginIdentifiableCommand{

    public function __construct(){
        parent::__construct("getCoins", "/getCoins <player>", ("/getCoins <player>"), ["gco"]);
    }
	
    public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player) return;
		if(!empty($args[0])){
			foreach(CoinsAPI::getInstance()->getServer()->getOnlinePlayers() as $player){
			    if(strtolower($player->getName()) == strtolower($args[0])){ 
		            $sender->sendMessage(TextFormat::BOLD."Coins của bạn là: ".CoinsAPI::getInstance()->getCoins($player));					
				}
			}
		}
    }

    public function getPlugin(): Plugin{
        return CoinsAPI::getInstance();
    }
}