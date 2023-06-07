<?php

declare(strict_types = 1);

namespace hachkingtohach1\ScoreBoard\task;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use hachkingtohach1\ScoreBoard\ScoreBoard;
use hachkingtohach1\ScoreBoard\utils\ScoreBoardAPI;

class SendScore extends Task {
	
	private $line = [];
	
	public function __construct(){}	
	
	public function onRun() :void{	
		if(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardType") === "public"){    
			foreach(ScoreBoard::getInstance()->getServer()->getOnlinePlayers() as $player){				
				if(!isset($this->line[$player->getName()])){
					$this->line[$player->getName()] = 1;
				}
				if($this->line[$player->getName()] < 15){
					ScoreBoardAPI::setScore($player, ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPublic")["broad"]["title"]);
				    foreach(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPublic")["broad"]["score"] as $message){
				        ScoreBoardAPI::setScoreLine($player, $this->line[$player->getName()], ScoreBoard::getInstance()->getFunction($message, $player));
					    $this->line[$player->getName()] += 1;
					}
				}
                unset($this->line[$player->getName()]);				
		    }
		}elseif(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardType") === "private"){
			foreach(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPrivate") as $score){
				foreach(ScoreBoard::getInstance()->getServer()->getWorldByName($score["world"])->getEntities() as $entity){	 
                    if($entity instanceof Player){
						if(!isset($this->line[$entity->getName()])){
					        $this->line[$entity->getName()] = 1;
						}
						if($this->line[$entity->getName()] < 15){
				            ScoreBoardAPI::setScore($entity, $score["title"]);
							foreach($score["score"] as $message){
				                ScoreBoardAPI::setScoreLine($entity, $this->line[$entity->getName()], ScoreBoard::getInstance()->getFunction($message, $entity));
					            $this->line[$entity->getName()] += 1;
							}
						}
						unset($this->line[$entity->getName()]);	
					}
				}				
			}
		}
	}
}