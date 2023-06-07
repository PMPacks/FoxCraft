<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\armor;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\world\Position;
use pocketmine\entity\Location;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\MyItem\sounds\Sounds;
use hachkingtohach1\PlayerStats\PlayerStats;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use function intdiv;
use function min;

class Bonus implements Listener {		
	
	public const JUMP = 1;
	public const FULLSET = 2;
	
    private $bonus = [
	    "LEGION" => [1, "Increase your all stats with percent."],
		"DOUBLEJUMP" => [2, "Allows you to Double Jump."]
	];	
    public $plugin;
	
    public function __construct(MyItem $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
	
	public function getDataBonus(string $tag) :array{
		$result = []; 
		$data = explode(":", $tag);
		foreach($data as $bonus){
			$id = explode("|", $bonus)[0];
			if(in_array($id, [
			    $this->bonus["LEGION"][0]
			])
			    and !empty(explode("|", $bonus)[0])
				and !empty(explode("|", $bonus)[1])
				and !empty(explode("|", $bonus)[2])
			){
				$result[] = [
				    "TYPE" => explode("|", $bonus)[0],
					"VALUE" => explode("|", $bonus)[1],
					"EVENT" => explode("|", $bonus)[2]
				];
			}
			if(in_array($id, [
			    $this->bonus["DOUBLEJUMP"][0]
			])
			    and !empty(explode("|", $bonus)[0])
				and !empty(explode("|", $bonus)[1])
			){
				$result[] = [
				    "TYPE" => explode("|", $bonus)[0],
					"EVENT" => explode("|", $bonus)[1]
				];
			}
		}
		return $result;
	}
	
	public function checkTimeCountDown(Player $player) :bool{
		foreach($player->getArmorInventory()->getContents() as $index => $item){
			$nbt = $item->getNamedTag();   
        	if(!isset($this->plugin->timeCountDown[$player->getName()])){
				$this->plugin->timeCountDown[$player->getName()] = [];
        	}		
			if(empty($this->plugin->timeCountDown[$player->getName()]["ARMOR"])){
				$this->plugin->timeCountDown[$player->getName()]["ARMOR"] = microtime(true);
        	}
        	if($nbt->getTag("Timecountdown", IntTag::class) == null){
				return true;
			}			
        	if($nbt->getInt("Timecountdown") < 1){       
            	$time = (int)(microtime(true) - $this->plugin->timeCountDown[$player->getName()]["ARMOR"]);
		    	$timeuse = (int) ($nbt->getInt("Timecountdown") - $time);
            	if($time < $nbt->getInt("Timecountdown")){
                	return false;
            	}else{
		        	if($nbt->getTag("Mana", IntTag::class) != null){
                    	if($this->plugin->checkMana($player, $nbt->getInt("Mana")) == false){
                        	return false;
				    	}
			    	}
                	$this->plugin->timeCountDown[$player->getName()]["ARMOR"] = microtime(true);
            	}
            	return true;			
        	}			
		}
		return false;
	}
	
	public function checkFullSet(Player $player) :array{		
		$count = 0;
		$relative = [];
		$bonus = [];
		$result = ["FULLSET" => false, "RELATIVE" => null];
		foreach($player->getArmorInventory()->getContents() as $index => $item){
			if($item->getNamedTag()->getTag("Relative", StringTag::class) != null){	                
				if(isset($relative[$item->getNamedTag()->getString("Relative")])){			
					$count++;
				}else{
					$relative[$item->getNamedTag()->getString("Relative")] = $item->getNamedTag()->getString("Relative");
				    $count++;
				}
				// This is function will not access when $count <= 3
                if($item->getNamedTag()->getTag("Bonus", StringTag::class) != null){
					if(!isset($bonus[$item->getNamedTag()->getString("Bonus")]) and $item->getNamedTag()->getString("Bonus") != $this->plugin::KEY_BONUS){
					    $bonus[$item->getNamedTag()->getString("Bonus")] = $item->getNamedTag()->getString("Bonus");
					}
				}				
			}
		}
		if(count($relative) == 1 and $count >= 4 and count($bonus) == 1){
			if(!isset($this->plugin->armorUsing[$player->getName()])){
				$this->plugin->armorUsing[$player->getName()] = "";
			}
			$result = ["FULLSET" => true, "RELATIVE" => implode(" ", $result)];
		}
		return $result;
	}
	
	public function caculateBonus(array $data, Player $player){		
		foreach($data as $bonus){         	
            switch($bonus["TYPE"]){
				case $this->bonus["LEGION"][0]:
				    $this->legion($player, $bonus);
				break;
				case $this->bonus["DOUBLEJUMP"][0]: 
				    $player->setMotion(new Vector3(0, 0.8, 0));
				break;
			}
		}
	}
	
	public function onJump(PlayerJumpEvent $event){
        $player = $event->getPlayer();
		/*foreach($player->getArmorInventory()->getContents() as $index => $item){
		    if($item->getNamedTag()->getTag("Bonus", StringTag::class) != null) if($this->plugin->checkArmorPermission($player) and $this->checkTimeCountDown($player)){
			    $bonuscheck = $this->getDataBonus($item->getNamedTag()->getString("Bonus"));			    	
			    foreach($bonuscheck as $bonus) if($bonus["EVENT"] == self::JUMP){
					$this->caculateBonus(self::JUMP, $bonus, $player, $player);
				}
			}
		}*/
	}
	
	public function onDamage(EntityDamageEvent $event){
		
	}
	
	public function legion(Player $player, array $data){
		if(isset(PlayerStats::getInstance()->add[$player->getXuid()])){
			$percent = (float) $data["VALUE"];
		    PlayerStats::getInstance()->add[$player->getXuid()] = $percent;
		}
	}
}