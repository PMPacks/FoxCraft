<?php

namespace hachkingtohach1\Dungeons\floor;

use hachkingtohach1\Dungeons\Dungeons;

class Floor implements Listener {	
	
	public const MAX_SLOTS = 5;
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	    $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public function getConfig(){
		return $this->plugin->getConfig();
	}
	
	public function getPlayers(string $name){
		return count($this->plugin->rooms[$name]['players']);
	}
	
	public function randomRoom(int $floor){
		$result = [];
		foreach($this->plugin->rooms as $room){
			if(count($result) <= 7){
			    if($room["floor"] == $floor){
				    $result[] = $room["id"];
				}
			}
		}
		return $result;
	}
	
	public function findArenas(Player $player, int $floor){
		$result = [];
		foreach($this->plugin->arenas as $arena){
			if($arena["floor"] == $floor){
				$result[] = $arena["id"];
			}
		}
		if(empty($result)){
			$player->sendMessage($this->getConfig()->get("MESSAGE_INQUEUE"));
			return;
		}		
		$random = $result[array_rand($result, 1)];
		unset($this->plugin->arenas[$random]);
		$this->plugin->rooms[$random]["MASTER"] = $player;
		$player->sendMessage($this->getConfig()->get("MESSAGE_FOUND_ROOM"));
	}
	
	public function joinRoom(Player $player, string $name){
		if(empty($this->plugin->rooms[$name])) return;
		if(isset($this->plugin->ingame[$player->getName()])){
			$player->sendMessage($this->getConfig()->get("MESSAGE_INGAME"));
			return;
		}
		if(count($this->getPlayers($name)) >= self::MAX_SLOTS){
			$player->sendMessage($this->getConfig()->get("MESSAGE_FULL"));
			return;
		}
		$this->plugin->ingame[$player->getName()] = $player;
        $this->plugin->rooms[$name]["MEMBERS"][$player->getName()] = $player;	
		$player->sendMessage($this->getConfig()->get("MESSAGE_JOIN"));
	}
	
	public function leaveRoom(Player $player){
		if(isset($this->plugin->ingame[$player->getName()])){
			$nameRoom = $this->plugin->ingame[$player->getName()]["ROOM"];
			if(!empty($this->plugin->rooms[$nameRoom]["MASTER"])){
				unset($this->plugin->rooms[$nameRoom]["MASTER"][$player->getName()]);
			}
			if(!empty($this->plugin->rooms[$nameRoom]["MEMBERS"])){
				unset($this->plugin->rooms[$nameRoom]["MEMBERS"][$player->getName()]);
			}
			unset($this->plugin->ingame[$player->getName()]);
		}
	}
	
	public function endGame(string $nameRoom){
		
	}
}
	
	