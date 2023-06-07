<?php

declare(strict_types = 1);

namespace hachkingtohach1\MyItem\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\PlayerStats\PlayerStats;

class RemoveDarkFire extends Task {
	
	private $nameplayer;
	private $plugin;
	
	public function __construct(string $nameplayer, MyItem $plugin){
		$this->nameplayer = $nameplayer;
		$this->plugin = $plugin;
	}	
	
	public function onRun() :void{
		unset($this->plugin->darkFire[$this->nameplayer]);
		unset(PlayerStats::getInstance()->damageability[$this->nameplayer]);
	}
}	
