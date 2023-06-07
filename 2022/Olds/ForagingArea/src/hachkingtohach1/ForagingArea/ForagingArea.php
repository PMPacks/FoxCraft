<?php

declare(strict_types=1);

namespace hachkingtohach1\ForagingArea;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\block\BlockBreakEvent;
use hachkingtohach1\ForagingArea\task\Update;

class ForagingArea extends PluginBase implements Listener {
	
	public $register = [
		#1 => [
		    #"POS1" => [-27,61,-8],
			#"POS2" => [-57,82,25],			
			#"WORLD" => "skyblock"
		#]
	];
	private static $instance;

	public function onLoad(){
        self::$instance = $this;
	}
	
    public static function getInstance(): ForagingArea{
        return self::$instance;
    }

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();          		
		$this->getScheduler()->scheduleRepeatingTask(new Update($this), 500);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		foreach($this->register as $case => $data){
			$level = $this->getServer()->getLevelByName($data["WORLD"]);
			if(!$this->getServer()->isLevelLoaded($data["WORLD"])) {
                $this->getServer()->loadLevel($data["WORLD"]);
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();    
		$lumbers = [17, 162];
		foreach($this->register as $data){
			$pos1 = $data["POS1"];
			$pos2 = $data["POS2"];
		    if(min($pos1[0], $pos2[0]) <= $block->getX() 
		        && max($pos1[0], $pos2[0]) >= $block->getX() 
			    && min($pos1[1], $pos2[1]) <= $block->getY() 
			    && max($pos1[1], $pos2[1]) >= $block->getY() 
			    && min($pos1[2], $pos2[2]) <= $block->getZ() 
			    && max($pos1[2], $pos2[2]) >= $block->getZ() 
			){
				if($block->getLevel()->getName() == $data["WORLD"]){
					if(in_array($block->getId(), $lumbers)){
						$event->setCancelled(false);
						$data = [
						    "X" => $block->getX(), 
							"Y" => $block->getY(), 
							"Z" => $block->getZ(), 
							"WORLD" => $block->getLevel()->getName(),
							"ID" => $block->getId(),
							"META" => $block->getDamage()
						];
						$this->getConfig()->set($block->getId().$block->getX().$block->getY().$block->getZ(), $data);
						$this->getConfig()->save();
					}
				}
			}
		}
	}
}