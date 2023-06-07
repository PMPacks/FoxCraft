<?php

declare(strict_types=1);

namespace hachkingtohach1\NPC;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\{EntityFactory, EntityDataHelper};
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use hachkingtohach1\NPC\task\CreateEntity;
use hachkingtohach1\NPC\entity\NPCEntity;
use hachkingtohach1\PlayerStats\PlayerStats;

class Main extends PluginBase implements Listener {
	
	public $npc = [
	    1 => [
		    "X" => 324.5,
		    "Y" => 67,
		    "Z" => 360.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Cooker",
			"SCALE" => 1,		
            "NAME" => "§f§lĐầu bếp\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz cooker"],
			"TAG_DISABLE" => false
		],
		2 => [
		    "X" => 353.5,
		    "Y" => 67,
		    "Z" => 281.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Angler",
			"SCALE" => 1,		
            "NAME" => "§f§lNgười câu cá\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz angler"],
			"TAG_DISABLE" => false
		],
		3 => [
		    "X" => 313.5,
		    "Y" => 67,
		    "Z" => 352.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Forger",
			"SCALE" => 1,		
            "NAME" => "§f§lThợ rèn\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["eshop"],
			"TAG_DISABLE" => false
		],
		4 => [
		    "X" => 314.5,
		    "Y" => 67,
		    "Z" => 355.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "WeaponSmith",
			"SCALE" => 1,		
            "NAME" => "§f§lThợ rèn vũ khí\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz weaponsmith"],
			"TAG_DISABLE" => false
		],
		5 => [
		    "X" => 317.5,
		    "Y" => 67,
		    "Z" => 350.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "ArmorSmith",
			"SCALE" => 1,		
            "NAME" => "§f§lThợ rèn áo giáp\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz armorsmith"],
			"TAG_DISABLE" => false
		],
		6 => [
		    "X" => 318.5,
		    "Y" => 68,
		    "Z" => 333.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Miner",
			"SCALE" => 1,		
            "NAME" => "§f§lThợ mỏ\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz miner"],
			"TAG_DISABLE" => false
		],
        7 => [
		    "X" => 324.5,
		    "Y" => 69,
		    "Z" => 318.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Farmer",
			"SCALE" => 1,		
            "NAME" => "§f§lNông dân\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz farmer"],
			"TAG_DISABLE" => false
		],
        8 => [
		    "X" => 330.5,
		    "Y" => 66,
		    "Z" => 278.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "AuctionAgent",
			"SCALE" => 1,		
            "NAME" => "§6§lĐấu thầu\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["ah"],
			"TAG_DISABLE" => false
		],
		9 => [
		    "X" => 325.5,
		    "Y" => 66,
		    "Z" => 284.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Info",
			"SCALE" => 1,		
            "NAME" => "§l§aInfo\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["info"],
			"TAG_DISABLE" => false
		],
		10 => [
		    "X" => 317.5,
		    "Y" => 69,
		    "Z" => 394.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Dungeon",
			"SCALE" => 1,		
            "NAME" => "§l§cDungeon\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["dungeon"],
			"TAG_DISABLE" => false
		],
		11 => [
		    "X" => 362.5,
		    "Y" => 67,
		    "Z" => 373.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Builder",
			"SCALE" => 1,		
            "NAME" => "§l§5Thợ xây dựng\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz builder"],
			"TAG_DISABLE" => false
		],
		12 => [
		    "X" => 431.5,
		    "Y" => 69,
		    "Z" => 395.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Redstoner",
			"SCALE" => 1,		
            "NAME" => "§l§cThợ điện\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz redstone"],
			"TAG_DISABLE" => false
		],
		13 => [
		    "X" => 386.5,
		    "Y" => 68,
		    "Z" => 363.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dThợ cắt kính\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz glass"],
			"TAG_DISABLE" => false
		],
		14 => [
		    "X" => 337.5,
		    "Y" => 64,
		    "Z" => 283.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 0.1,		
            "NAME" => "§l§7-------------------------\n§l§6THAM GIA SKYBLOCK\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n§l§7-------------------------\n",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz glass"],
			"TAG_DISABLE" => false
		],
		15 => [
		    "X" => 330.5,
		    "Y" => 65,
		    "Z" => 304.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		16 => [
		    "X" => 329.5,
		    "Y" => 54,
		    "Z" => 144.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Coal",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		17 => [
		    "X" => 252.5,
		    "Y" => 35,
		    "Z" => 242.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Gold",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		18 => [
		    "X" => 214.5,
		    "Y" => 13,
		    "Z" => 172.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Iron",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		19 => [
		    "X" => 241.5,
		    "Y" => 23,
		    "Z" => 264.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Diamond",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		20 => [
		    "X" => 220.5,
		    "Y" => 38,
		    "Z" => 208.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Obisidian",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		21 => [
		    "X" => 240.5,
		    "Y" => 27,
		    "Z" => 259.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Redstone",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		22 => [
		    "X" => 210.5,
		    "Y" => 23,
		    "Z" => 230.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Glass",
			"SCALE" => 1,		
            "NAME" => "§l§dWarp\n§l§eCHẠM VÀO",			
		    "WORLD" => "Lapis",
			"COMMANDS" => ["warp"],
			"TAG_DISABLE" => false
		],
		23 => [
		    "X" => 344.5,
		    "Y" => 66,
		    "Z" => 357.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Lumber",
			"SCALE" => 1,		
            "NAME" => "§l§eTiều phu\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => ["shopguiz lumber"],
			"TAG_DISABLE" => false
		],
		24 => [
		    "X" => 312.5,
		    "Y" => 69,
		    "Z" => 334.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "Miner",
			"SCALE" => 1,		
            "NAME" => "§aChủ Mỏ Vàng\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => [],
			"TAG_DISABLE" => false
		],
		25 => [
		    "X" => 331.5,
		    "Y" => 64,
		    "Z" => 287.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "HuongDan",
			"SCALE" => 1,		
            "NAME" => "§aJason\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => [],
			"TAG_DISABLE" => false
		],
		26 => [
		    "X" => 333.5,
		    "Y" => 69,
		    "Z" => 322.5,
			"YAW" => 0,
			"PITCH" => 0,
		    "SKIN" => "HuongDan",
			"SCALE" => 1,		
            "NAME" => "§aHưỡng Dẫn Viên\n§l§eCHẠM VÀO",			
		    "WORLD" => "chéile éadrom",
			"COMMANDS" => [],
			"TAG_DISABLE" => false
		],
	];
	public $registerId = [];
	private static $instance;

	public function onLoad() :void{
        self::$instance = $this;
	}
	
    public static function getInstance(): Main{
        return self::$instance;
    }

	public function onEnable() :void{
		$entityfactory = EntityFactory::getInstance();
		$entityfactory->register(NPCEntity::class, function(World $world, CompoundTag $nbt) : NPCEntity{
			return new NPCEntity(EntityDataHelper::parseLocation($nbt, $world), $this->getSkin("Steve"), $nbt);
		}, ['NPCEntity']);
		$this->saveDefaultConfig();          
        $this->getScheduler()->scheduleRepeatingTask(new CreateEntity($this), 20);		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}	
	
	public function getSkin(string $fileName) :?Skin{
        $path = $this->getDataFolder()."skins". DIRECTORY_SEPARATOR .$fileName.".png";
        if(!is_file($path)){
            return null;
        }
        $img = @imagecreatefrompng($path);
        $bytes = '';
        $l = (int) @getimagesize($path)[1];
        for ($y = 0; $y < $l; $y++){
            for ($x = 0; $x < 64; $x++){
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r).chr($g).chr($b).chr($a);
            }
        }
        @imagedestroy($img);
        return new Skin("Standard_CustomSlim", $bytes);
    }
	
	public function create(int $id, Vector3 $vector, Skin $skin, World $world, Location $location, bool $tagDisable, string $name, float $scale){
        $world->loadChunk($vector->x >> 4 ,$vector->z >> 4);
		$entity = new NPCEntity($location, $skin);
		$entity->setScale($scale);
		$entity->setSkin($skin);		
		$entity->setNametag($name);
        if($tagDisable == true){
			$entity->setNameTagVisible(false);
            $entity->setNameTagAlwaysVisible(false);
		}
		if($tagDisable == false){
			$entity->setNameTagVisible(true);
            $entity->setNameTagAlwaysVisible(true);
		}        
        $entity->spawnToAll();	
        $this->registerId[$entity->getId()] = [$id, $entity, $name];		
	}
	
	public function registerEntities(){
		foreach($this->npc as $case => $npc){			
			$id = $case;
			$vector = new Vector3($npc["X"], $npc["Y"], $npc["Z"]);
			$skin = $this->getSkin($npc["SKIN"]);
			$world = $this->getServer()->getWorldManager()->getWorldByName($npc["WORLD"]);
			$location = new Location((float) $npc["X"], (float) $npc["Y"], (float) $npc["Z"], $world, $npc["YAW"], $npc["PITCH"]);
		    $tagDisable = $npc["TAG_DISABLE"];
			$name = $npc["NAME"];
			$scale = $npc["SCALE"];	
			$check = [];
            foreach($this->registerId as $idEntity => [$ide, $entity, $tag]){
				$check[$ide] = $entity;
			}			
			if(!isset($check[$id])){
			    $this->create($id, $vector, $skin, $world, $location, $tagDisable, $name, $scale);
			}
		}
	}
	
	public function getDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof NPCEntity){
			$event->cancel();
		}
		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if($damager instanceof Player and $entity instanceof NPCEntity){
				if(isset($this->registerId[$entity->getId()])){
					$id = $this->registerId[$entity->getId()][0];
					$commands = $this->npc[$id]["COMMANDS"];
					foreach($commands as $command){
				        Server::getInstance()->dispatchCommand($damager, str_replace("%player", $damager->getName(), $command));
					}					
				}
			}
		}
	}
}