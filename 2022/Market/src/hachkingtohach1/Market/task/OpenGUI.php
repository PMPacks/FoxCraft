<?php

declare(strict_types = 1);

namespace hachkingtohach1\Market\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\Market\Market;

class OpenGUI extends Task {
	
	private $plugin;
	private $player;
	
	public function __construct(Market $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
	}	
	
	public function onRun() :void{
		$page = $this->plugin->dataUser[$player->getXuid][0];
		$category = $this->plugin->dataUser[$player->getXuid][1];
        $this->plugin->market($this->player, $page, $category);		
	}
}	
