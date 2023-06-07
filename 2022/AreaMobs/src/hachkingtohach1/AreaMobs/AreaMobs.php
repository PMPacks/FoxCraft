<?php

namespace hachkingtohach1\AreaMobs;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDeathEvent;
use leinne\pureentities\entity\neutral\IronGolem;
use leinne\pureentities\entity\neutral\ZombifiedPiglin;
use leinne\pureentities\entity\neutral\Spider;
use leinne\pureentities\entity\hostile\Creeper;
use leinne\pureentities\entity\hostile\Skeleton;
use leinne\pureentities\entity\hostile\Zombie;
use leinne\pureentities\entity\dungeons\BossFloorOne;
use leinne\pureentities\entity\dungeons\DungeonEnderman;
use leinne\pureentities\entity\dungeons\DungeonSkeleton;
use leinne\pureentities\entity\dungeons\DungeonZombie;
use leinne\pureentities\entity\dungeons\BossFloorTwo;
use leinne\pureentities\entity\dungeons\DungeonPig;
use leinne\pureentities\entity\dungeons\DungeonIronZombie;
use leinne\pureentities\entity\dungeons\DungeonDrowned;
use hachkingtohach1\AreaMobs\task\AutoSpawnMobs;

class AreaMobs extends PluginBase implements Listener {

    private array $areas = [];
	public array $register = [];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): AreaMobs{
        return self::$instance;
    }

    public function onEnable() :void{
		$this->areas = [
	        100 => [
		        "ENTITY" => Zombie::class,
				"AMOUNT" => 5,
				"WORLD" => "Iron",
				"SPAWN" => "243,16,174"
		    ],
			101 => [
		        "ENTITY" => Creeper::class,
				"AMOUNT" => 5,
				"WORLD" => "Gold",
				"SPAWN" => "251,39,224"
		    ],
			102 => [
		        "ENTITY" => ZombifiedPiglin::class,
				"AMOUNT" => 5,
				"WORLD" => "Redstone",
				"SPAWN" => "251,26,260"
		    ],
			103 => [
		        "ENTITY" => Spider::class,
				"AMOUNT" => 5,
				"WORLD" => "Lapis",
				"SPAWN" => "213,26,243"
		    ],
			104 => [
		        "ENTITY" => IronGolem::class,
				"AMOUNT" => 5,
				"WORLD" => "Diamond",
				"SPAWN" => "255,23,265"
		    ],
			105 => [
		        "ENTITY" => DungeonEnderman::class,
				"AMOUNT" => 5,
				"WORLD" => "Obisidian",
				"SPAWN" => "218,34,243"
		    ]
	    ];
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new AutoSpawnMobs($this), 150);
    }
	
	public function getDataAreas() :array{
		return $this->areas;
	}
	
	public function onEntityDeath(EntityDeathEvent $event) :void{
		$entity = $event->getEntity();
		foreach($this->register as $id => $entities){
			foreach($entities as $enti){
			    if($enti->getId() == $entity->getId()){
				    unset($this->register[$id][$enti->getId()]);
				}					
			}
		}
	}
}