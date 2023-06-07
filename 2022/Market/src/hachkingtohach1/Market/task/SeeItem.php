<?php

declare(strict_types = 1);

namespace hachkingtohach1\Market\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use hachkingtohach1\Market\Market;

class SeeItem extends Task {
	
	private $plugin;
	private $player;
	private $item;
	private $seller;
	private $xuid;
	private $price;
	private $id;
	
	public function __construct(Market $plugin, Player $player, Item $item, string $seller, int $xuid, int $price, string $id){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->item = $item;
		$this->seller = $seller;
		$this->xuid = $xuid;
		$this->price = $price;
		$this->id = $id;
	}	
	
	public function onRun() :void{		
        $this->plugin->seeItem($this->player, $this->item, $this->seller, $this->xuid, $this->price, $this->id);		
	}
}	
