<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\items\weapons;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use hachkingtohach1\MyItem\MyItem;

class Weapons implements Listener {		
	
    public $plugin;
	
    public function __construct(MyItem $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }	
}