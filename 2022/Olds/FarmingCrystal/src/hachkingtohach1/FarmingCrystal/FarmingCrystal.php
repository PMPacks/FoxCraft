<?php

declare(strict_types=1);

namespace hachkingtohach1\FarmingCrystal;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use hachkingtohach1\FarmingCrystal\entity\Crystal;
use hachkingtohach1\FarmingCrystal\task\Update;
use hachkingtohach1\FarmingCrystal\task\Movement;

class FarmingCrystal extends PluginBase implements Listener {
	
	public $crystal = [
		#1 => [
		    #"SKIN" => "natural",
			#"X" => -76,
			#"Y" => 62,
			#"Z" => 33,
			#"LEVEL" => "skyblock",
			#"DISTANCE" => 8
		#]
	];
	public $postions = [];
	public $register = [];
	private static $instance;

	public function onLoad(){
        self::$instance = $this;
	}
	
    public static function getInstance(): FarmingCrystal{
        return self::$instance;
    }

	public function onEnable(){
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder()."skins/");
		Entity::registerEntity(Crystal::class, true);
		$this->saveDefaultConfig();          		
		$this->getScheduler()->scheduleRepeatingTask(new Update($this), 100);
		$this->getScheduler()->scheduleRepeatingTask(new Movement($this), 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}	
	
	public function getSkin(string $fileName) :?Skin{
        $path = $this->getDataFolder()."skins".DIRECTORY_SEPARATOR.$fileName.".png";
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
	
	public function findDist($a, $b){
        return sqrt(pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2) + pow($a->z - $b->z, 2));
	}
	
	public function getDirectionBetweenLocations(Vector3 $a, Vector3 $b){
        return $a->subtract($b->x, $b->y, $b->z);
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$vector = new Vector3($player->getX(), $player->getY(), $player->getZ());        
		$seeds = [37, 38, 39, 40, 59, 127, 141, 142, 86];
		if(in_array($block->getId(), $seeds)){
		    foreach($player->getLevel()->getEntities() as $entity){	
		        if(isset($this->register[$entity->getId()])){
				    if($entity instanceof Crystal and $entity->getPosition()->distance($vector) <= $this->register[$entity->getId()][1]){
				        $this->postions[$entity->getId()]["POSTION"][] = [$block->getX(), $block->getY(), $block->getZ(), $block->getId(), 0, $block->getLevel()->getName()];
				        $data = [
					        "X" => $block->getX(),
						    "Y" => $block->getY(),
						    "Z" => $block->getZ(),
						    "WORLD" => $block->getLevel()->getName(),
						    "ID" => $block->getId(),
						    "META" => $block->getDamage()
					    ];
					    $this->getConfig()->set($entity->getId().$block->getX().$block->getY().$block->getZ().$block->getId().$block->getDamage(), $data);
	                    $this->getConfig()->save();	
					}
				}
			}
		}
	}
}