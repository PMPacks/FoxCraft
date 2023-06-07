<?php

namespace hachkingtohach1\FoxEvent;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\entity\{Entity, EntityFactory, EntityDataHelper};
use pocketmine\event\block\BlockBreakEvent;
use hachkingtohach1\FoxEvent\task\Task;

class FoxEvent extends PluginBase implements Listener {
    
	public const JACOB_EVENT = 1;
	private array $topPlayers = [];
    public array $event = [];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): FoxEvent{
        return self::$instance;
    }

    public function onEnable() :void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new Task($this), 20);
    }
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function randomSeed() :int{
		$seeds = [86, 81, 103, 142, 141, 59, 115, 83];
		return $seeds[array_rand($seeds, 1)];
	}
	
	public function randomEvent(){
	    $array = [self::JACOB_EVENT, self::JACOB_EVENT];
		$random = $array[array_rand($array, 1)];	
        switch($random){
			case self::JACOB_EVENT:
			    $seed = $this->randomSeed();
			    $this->event = [
				    "EVENT" => self::JACOB_EVENT,
					"SEED" => $seed,
					"NAME_BLOCK" => $this->getDataItem($seed, 0, 1)->getName()
				];
			break;
		}		
	}
	
	public function getEvent() :int{
		$event = 0;
		if(!empty($this->event)){
			$event = $this->event["EVENT"];
		}
		return $event;
	}
	
	public function resetEvent(){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$player->sendMessage(TextFormat::BOLD.TextFormat::AQUA."[Event] Sự kiện đã kết thúc!");
		}
		$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."------------------");
		$i = 1;
		rsort($this->topPlayers);
		if(count($this->topPlayers) > 0){
			foreach($this->topPlayers as $name => [$count, $playerd]){
				if($i <= 3){
					$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Top ".$i." > ".TextFormat::AQUA.$playerd->getName().TextFormat::YELLOW." với số lượng ".TextFormat::WHITE.number_format($count));
			    	$api = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
					$api->addMoney($player, (1000 * $count)/$i);
					unset($this->topPlayers[$name]);
					$i++;
				}
			}
		}
		$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."------------------");
	    unset($this->topPlayers);
		unset($this->event);
	}
	
	public function onBreak(BlockBreakEvent $event) :void{
		$player = $event->getPlayer();
		$block = $event->getBlock();  
		if($this->getEvent() == self::JACOB_EVENT){
			if($block->getId() == $this->event["SEED"]){
				$count = 0;
				if(count($event->getDrops()) > 0){					
				    foreach($event->getDrops() as $item){
						$count += $item->getCount();
					}
				}
				if(!isset($this->topPlayers[$player->getName()])){
					$this->topPlayers[$player->getName()] = [0, $player];
				}
				$this->topPlayers[$player->getName()][0] += $count;
			}
		}
	}
}