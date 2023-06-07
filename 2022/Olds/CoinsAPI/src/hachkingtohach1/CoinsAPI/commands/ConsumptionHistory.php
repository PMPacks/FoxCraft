<?php

namespace hachkingtohach1\CoinsAPI\commands;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use hachkingtohach1\CoinsAPI\CoinsAPI;

class ConsumptionHistory extends Command implements PluginIdentifiableCommand{

    public function __construct(){
        parent::__construct("consumptionHistory", "/consumptionHistory <player>", ("/consumptionHistory <player>"), ["coh"]);
    }
	
    public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player) return;
		if(!empty($args[0])){
			foreach(CoinsAPI::getInstance()->getServer()->getOnlinePlayers() as $player){
			    if(strtolower($player->getName()) == strtolower($args[0])){ 
					$sender->sendMessage(TextFormat::BOLD."Lịch sử Coins bạn đã tiêu xài: ".CoinsAPI::getInstance()->consumptionHistory($player));
				}
			}
		}
    }

    public function getPlugin(): Plugin{
        return CoinsAPI::getInstance();
    }
}