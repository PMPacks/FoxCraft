<?php

declare(strict_types = 1);

namespace hachkingtohach1\Alerts\task;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\Alerts\Alerts;

class sendPopup extends Task {
	
	private $timeCountDown = 20;
	
	public function onRun(int $currentTick){				
		if(Alerts::getInstance()->getConfig()->get("sendPopup")["enable"] === false) return;
		if($this->timeCountDown === 0){
		    foreach(Alerts::getInstance()->getServer()->getOnlinePlayers() as $player){
			    $getMessage = Alerts::getInstance()->getConfig()->get("sendPopup")["Messages"];
			    $randomMessage = $getMessage[array_rand($getMessage, 1)];
			    $player->sendPopup(Alerts::getInstance()->replaceFormat($randomMessage));
			}
			$this->timeCountDown = Alerts::getInstance()->getConfig()->get("sendPopup")["timeCountDown"];
		}
		$this->timeCountDown--;
	}
}	