<?php

declare(strict_types = 1);

namespace hachkingtohach1\ScoreBoard\task;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use hachkingtohach1\ScoreBoard\ScoreBoard;
use hachkingtohach1\ScoreBoard\utils\ScoreBoardAPI;

class SendScore extends Task {
	
	public function __construct(){}	
	
	public function onRun(int $currentTick){
		$line = 1;
		if(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardType") === "public"){    
			foreach(ScoreBoard::getInstance()->getServer()->getOnlinePlayers() as $player){			
				ScoreBoardAPI::setScore($player, ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPublic")["broad"]["title"]);
				if($line <= 15){
				    foreach(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPublic")["broad"]["score"] as $message){
				        ScoreBoardAPI::setScoreLine($player, $line, ScoreBoard::getInstance()->getFunction($message, $player));
					    $line++;
					}
				}					
		    }
		}elseif(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardType") === "private"){
			foreach(ScoreBoard::getInstance()->getConfig()->get("ScoreBoardPrivate") as $score){
				foreach(ScoreBoard::getInstance()->getServer()->getLevelByName($score["world"])->getEntities() as $entity){	 
                    if($entity instanceof Player){
						if($line <= 15){
				            ScoreBoardAPI::setScore($entity, $score["title"]);
							foreach($score["score"] as $message){
				                ScoreBoardAPI::setScoreLine($entity, $line, ScoreBoard::getInstance()->getFunction($message, $entity));
					            $line++;
							}
						}
					}
				}				
			}
		}
	}
}