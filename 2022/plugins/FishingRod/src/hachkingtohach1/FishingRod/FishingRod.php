<?php

namespace hachkingtohach1\FishingRod;

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
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use hachkingtohach1\FishingRod\FishingHook;
use hachkingtohach1\FishingRod\task\CatchTime;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\PlayerStats\PlayerStats;

class FishingRod extends PluginBase implements Listener {

    public array $pullOut = [];
    public array $fishing = [];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): FishingRod{
        return self::$instance;
    }

    public function onEnable() :void{
		$entityfactory = EntityFactory::getInstance();
		$entityfactory->register(FishingHook::class, function(World $world, CompoundTag $nbt) : FishingHook{
			return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['FishingHook']);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new CatchTime($this), 20);
    }
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function reward(Player $player){
		$levelPlayer = PlayerStats::getInstance()->getLevel($player)[0];
		$items = [
		    1 => [MyItem::getInstance()->getItem("GOLD'S_GOD", rand(0, 1)), 1],
			2 => [$this->getDataItem(349, 0, rand(1, 2)), 5],
			3 => [$this->getDataItem(410, 0, rand(1, 3)), 5],
			4 => [$this->getDataItem(409, 0, rand(1, 3)), 5],
			5 => [$this->getDataItem(460, 0, rand(1, 3)), 5],
			6 => [$this->getDataItem(461, 0, rand(1, 3)), 5],
			7 => [$this->getDataItem(466, 0, 1), 3],
			8 => [$this->getDataItem(87, 0, rand(1, 5)), 5],
			9 => [$this->getDataItem(280, 0, rand(1, 5)), 5],		           	
			10 => [MyItem::getInstance()->getItem("FISH_SWORD", 1), 2]
		];
		if($levelPlayer >= 20){
			$items[] = [MyItem::getInstance()->getItem("GOLD'S_GOD", rand(0, 1)), 1];
			$items[] = [MyItem::getInstance()->getItem("SPECIAL_IRON_INGOT", rand(0, 3)), 1];
		    $items[] = [MyItem::getInstance()->getItem("JACOB_HOE", 1), 1];
			$items[] = [MyItem::getInstance()->getItem("YETI_SWORD", rand(0, 1)), 1];
			$items[] = [$this->getDataItem(349, 0, rand(1, 2)), 5];
			$items[] = [$this->getDataItem(410, 0, rand(1, 3)), 5];
		}
		$result = [];
		foreach($items as $case => $item){
			for($i = 1; $i <= $item[1]; $i++){
				$result[] = $item[0];
			}
		}
		$vector3 = new Vector3($player->getLocation()->x, $player->getLocation()->y + 1, $player->getLocation()->z);	
		$item = $result[array_rand($result, 1)];
		if($player->getInventory()->canAddItem($item)){
            $player->getInventory()->addItem($item);
        }else $player->getWorld()->dropItem($vector3, $item);
	}
	
	public function onPlayerItemUse(PlayerItemUseEvent $event){
		$player = $event->getPlayer();
		$itemInHand = $player->getInventory()->getItemInhand();
		if($itemInHand->getId() == 346){
			if(!isset($this->fishing[$player->getName()])){
				$this->fishing[$player->getName()] = [false, null];
			}
			if(!$this->fishing[$player->getName()][0]){
			    $delta = $player->getDirectionVector()->multiply(3);
				$caculate = $player->getLocation()->add($delta->x, $delta->y, $delta->z);
				$vector3 = new Vector3($caculate->x, $caculate->y + $player->getEyeHeight(), $caculate->z);
			    $class = FishingHook::class;
			    $entity = new $class(Location::fromObject($vector3, $player->getWorld()), $player);              
				$entity->spawnToAll();
				$this->fishing[$player->getName()][0] = true;
				$this->fishing[$player->getName()][1] = $entity;
			}else{
				$this->pullOut[$player->getName()] = $player;
				$this->fishing[$player->getName()][0] = false;
				$entity = $this->fishing[$player->getName()][1];
				$entity->close();
			}				
		}
	}
	
	public function onPlayerItemHeld(PlayerItemHeldEvent $event){
		$player = $event->getPlayer();
		$itemInHand = $player->getInventory()->getItemInhand();
		if(isset($this->fishing[$player->getName()])){
			if($this->fishing[$player->getName()][0]){
				$this->fishing[$player->getName()][0] = false;
				$entity = $this->fishing[$player->getName()][1];
				$entity->close();
			}
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
	    $player = $event->getPlayer();
		if(isset($this->fishing[$player->getName()])){
			if($this->fishing[$player->getName()][0]){
				$this->fishing[$player->getName()][0] = false;
				$entity = $this->fishing[$player->getName()][1];
				$entity->close();
			}
			unset($this->fishing[$player->getName()]);
		}
	}
}