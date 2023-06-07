<?php

declare(strict_types = 1);

namespace hachkingtohach1\CraftingTable\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\CraftingTable\CraftingTable;

class OpenRecipe extends Task {
	
	private $plugin;
	private $player;
	private $name;
	
	public function __construct(CraftingTable $plugin, Player $player, string $name){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->name = $name;
	}	
	
	public function onRun() :void{
        $this->plugin->seeRecipe($this->player , $this->name);			
	}
}	
