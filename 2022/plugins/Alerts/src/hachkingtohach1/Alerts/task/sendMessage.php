<?php

declare(strict_types = 1);

namespace hachkingtohach1\Alerts\task;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\Alerts\Alerts;

class sendMessage extends Task {
	
	private $timeCountDown = 20;
	
	public function onRun() :void{				
		if(Alerts::getInstance()->getConfig()->get("sendAlerts")["enable"] === false) return;
		if($this->timeCountDown === 0){
		    foreach(Alerts::getInstance()->getServer()->getOnlinePlayers() as $player){
			    $getMessage = Alerts::getInstance()->getConfig()->get("sendAlerts")["Messages"];
			    $randomMessage = $getMessage[array_rand($getMessage, 1)];
			    $player->sendMessage(Alerts::getInstance()->replaceFormat($randomMessage));
			}
			$this->timeCountDown = Alerts::getInstance()->getConfig()->get("sendAlerts")["timeCountDown"];
		}
		$this->timeCountDown--;
	}
}	