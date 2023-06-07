<?php

namespace GN\xyz;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;


class xyz extends PluginBase {

	public function onEnable(){
	$this->getLogger()->info("§a❖§cX§eY§dZ§a❖ XYZ Đã Bật");
		return true;
	}
	

	public function onLoad(){
		$this->getLogger()->info("§a❖§cX§eY§dZ§a❖ XYZ Đang Load");
	}
	

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "xyz":
				if($sender instanceof Player){
					$playerX = $sender->getX();
                	$playerY = $sender->getY();
                	$playerZ = $sender->getZ();

                	$outX=round($playerX,1);
                	$outY=round($playerY,1);
                	$outZ=round($playerZ,1);

                	$playerLevel = $sender->getLevel()->getName();

                	$sender->sendMessage("§a❖§cX§eY§dZ§a❖ §cX:§b" . $outX . " §f| §eY:§b" . $outY . " §f| §dZ:§b" . $outZ . " §f| §aWorld:§b " . $playerLevel);
					return true;
				}

				else{
					$sender->sendMessage("§a❖§cX§eY§dZ§a❖§b Vui Lòng Sử Dụng Lệnh Trong Game");
            }
		}
	}    

	
    public function onDisable(){
        $this->getLogger()->info("§a❖§cX§eY§dZ§a❖ XYZ Đã Tắt");
        return true;
	}
}