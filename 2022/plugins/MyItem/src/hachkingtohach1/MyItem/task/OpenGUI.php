<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\MyItem\MyItem;

class OpenGUI extends Task {
	
	private $plugin;
	private $player;
	
	public function __construct(MyItem $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
	}	
	
	public function onRun() :void{
        $this->plugin->openMenuItems($this->player);		
	}
}	
