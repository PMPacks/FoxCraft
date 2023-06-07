<?php

declare(strict_types = 1);

namespace hachkingtohach1\Quest\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use hachkingtohach1\Quest\Quest;

class SendMessage extends Task {
	
	public $count = [];
	public $delay = [];
	
	public function __construct(Quest $plugin){
        $this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(isset($this->plugin->sendMessage[$player->getName()])){
				if(count($this->plugin->sendMessage[$player->getName()]) >= 1){
					if(!isset($this->delay[$player->getName()])){
						$this->delay[$player->getName()] = microtime(true);
					}
					if(!isset($this->count[$player->getName()])){
						$this->count[$player->getName()] = 0;
					}
					if(microtime(true) - $this->delay[$player->getName()] >= 2){
					    $player->sendMessage("§l§cNPC§r >§f ".$this->plugin->sendMessage[$player->getName()][$this->count[$player->getName()]]);
						//$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_POP, mt_rand());
						unset($this->delay[$player->getName()]);
					    unset($this->plugin->sendMessage[$player->getName()][$this->count[$player->getName()]]);
					    $this->count[$player->getName()] += 1;
					}
				}else{										
					if(!empty($this->plugin->getQuest($player))){
						if(isset($this->plugin->getConfig()->get("QUESTS")[$this->plugin->getQuest($player)])){
							$quest = $this->plugin->getConfig()->get("QUESTS")[$this->plugin->getQuest($player)];					    
							if($this->plugin->getProcessQuestPlayer($player) >= 100){
						    	$this->plugin->setStatus($player, 0);
				            	$this->plugin->setQuest($player, 1);
			                	$this->plugin->rewardQuest($player, $quest["REWARD"]);
								unset($this->count[$player->getName()]);
							}
						}
					}
					$this->plugin->sendMessage[$player->getName()] = [];
				}
			}
		}
	}
}