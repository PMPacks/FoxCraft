<?php

declare(strict_types = 1);

namespace hachkingtohach1\CraftingTable\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\CraftingTable\CraftingTable;

class OpenGUI extends Task {
	
	private $plugin;
	private $player;
	
	public function __construct(CraftingTable $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
	}	
	
	public function onRun() :void{
        $this->plugin->craftingTable($this->player);		
	}
}	
