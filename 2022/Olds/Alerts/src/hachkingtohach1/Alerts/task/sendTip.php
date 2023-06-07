<?php

declare(strict_types = 1);

namespace hachkingtohach1\Alerts\task;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\Alerts\Alerts;

class sendTip extends Task {
	
	private $timeCountDown = 20;
	
	public function onRun(int $currentTick){				
		if(Alerts::getInstance()->getConfig()->get("sendTips")["enable"] === false) return;
		if($this->timeCountDown === 0){
		    foreach(Alerts::getInstance()->getServer()->getOnlinePlayers() as $player){
			    $getMessage = Alerts::getInstance()->getConfig()->get("sendTips")["Messages"];
			    $randomMessage = $getMessage[array_rand($getMessage, 1)];
			    $player->sendTip(Alerts::getInstance()->replaceFormat($randomMessage));
			}
			$this->timeCountDown = Alerts::getInstance()->getConfig()->get("sendTips")["timeCountDown"];
		}
		$this->timeCountDown--;
	}
}	