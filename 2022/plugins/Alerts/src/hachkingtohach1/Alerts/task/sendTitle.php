<?php

declare(strict_types = 1);

namespace hachkingtohach1\Alerts\task;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\Alerts\Alerts;

class sendTitle extends Task {
	
	private $timeCountDown = 20;
	
	public function onRun() :void{				
		if(Alerts::getInstance()->getConfig()->get("sendTitle")["enable"] === false) return;
		if($this->timeCountDown === 0){
		    foreach(Alerts::getInstance()->getServer()->getOnlinePlayers() as $player){
			    $getMessage = Alerts::getInstance()->getConfig()->get("sendTitle")["Messages"];
			    $randomMessage = $getMessage[array_rand($getMessage, 1)];
			    $player->sendTitle(Alerts::getInstance()->replaceFormat($randomMessage[0]), Alerts::getInstance()->replaceFormat($randomMessage[1]));
			}
			$this->timeCountDown = Alerts::getInstance()->getConfig()->get("sendTitle")["timeCountDown"];
		}
		$this->timeCountDown--;
	}
}	