<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\items;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\MyItem\MyItem\armors\Armors;
use hachkingtohach1\MyItem\MyItem\blocks\Blocks;
use hachkingtohach1\MyItem\MyItem\weapons\Weapons;

class Items implements Listener {		
	
    public $plugin;
	
    public function __construct(MyItem $plugin) {
        $this->plugin = $plugin;
		//new Armors($this->plugin);
		//new Blocks($this->plugin);
		//new Weapons($this->plugin);
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }	
}