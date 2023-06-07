<?php

namespace hachkingtohach1\EnderSword;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use hachkingtohach1\PlayerStats\PlayerStats;

class EnderSword extends PluginBase implements Listener {
	
	public $countDown = [];
	public $haveBlock = [];	
	private static $instance = null;
	
	public function onLoad(){
        self::$instance = $this;
	}
	
	public static function getInstance(): EnderSword{
        return self::$instance;
    }

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($command->getName() == "getes"){
			if(!$sender->isOp()){
				$sender->sendMessage(TextFormat::RED."This is command for admin!");
				return false;
			}
			$sender->getInventory()->addItem($this->getEnderSword());
			return true;
        }
		return true;
	}

    public function getEnderSword(): Item{
		$item = Item::get(ITEM::DIAMOND_SWORD);		
		$item->setCustomName("§l§l§r".TextFormat::DARK_PURPLE."Ender Sword".TextFormat::RED."§l§l§l§1");
		$item->setLore([
		    TextFormat::GRAY."Damage: ".TextFormat::RED."+100",
			TextFormat::GRAY."Strength: ".TextFormat::RED."+150","",			
			TextFormat::BOLD.TextFormat::GOLD."Tiệt Chiêu: Dịch chuyển".TextFormat::BOLD.TextFormat::YELLOW." Nhấp chuột phải",
			TextFormat::BOLD.TextFormat::GRAY."Cho phép bạn dịch chuyển đến 10 blocks trước mặt.",
			TextFormat::BOLD.TextFormat::DARK_GRAY."Mana Cost: ".TextFormat::DARK_AQUA."50","",
            TextFormat::BOLD.TextFormat::DARK_PURPLE."EPIC SWORD"			
		]);
		$nbt = $item->getNamedTag();
		$nbt->setString("Endersword", "ENDER_SWORD");
        $nbt->setString("Reforge", "nope");
		$nbt->setString("Star", "nope");
        $nbt->setInt("Damages", 100);		
        $nbt->setInt("Strength", 150);
		$nbt->setInt("Mana", 50);
        $item->setNamedTag($nbt);
		return $item;		
	}
	
	public function clickToTeleport(int $distance, Player $player){
		$delta = $player->getDirectionVector()->multiply($distance);
		$pos = $player->add($delta);
		return $pos;
	}
	
	public function getForwardBlock(int $distance, Player $player){
		$delta = $player->getDirectionVector()->multiply($distance);
		$pos = $player->add($delta);
		return $pos;
	}
	
	public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
		
		if($event->getAction() === $event::RIGHT_CLICK_AIR and $item->getCustomName() === "§l§l§r".TextFormat::DARK_PURPLE."Ender Sword".TextFormat::RED."§l§l§l§1"){
			$nbt = $item->getNamedTag();
			if($nbt->hasTag("Mana", IntTag::class) == true){
			    if(PlayerStats::getInstance()->checkMana($nbt->getInt("Mana"), $player) === false){
					$player->getLevel()->broadcastLevelSoundEvent($player->asVector3(), LevelSoundEventPacket::SOUND_TELEPORT);
					return;
				}
				$player->getLevel()->broadcastLevelSoundEvent($player->asVector3(), LevelSoundEventPacket::SOUND_TELEPORT);
				if(empty($this->countDown[$player->getName()])){
				    $this->countDown[$player->getName()] = microtime(true);
			    }
			    $countdown = microtime(true) - $this->countDown[$player->getName()];
			    if($countdown >= 0){
				    $teleport = 0;
				    for($check = 0; $check <= 10; $check++){
					    if(!in_array($player->getLevel()->getBlock($this->getForwardBlock($check, $player))->getId(), [0, 17, 18, 78, 171])){
						    if($teleport <= 0){
						        $teleport = $check;
						    }
					    }						
				    }
				    if($teleport <= 0){
					    $teleport = 11;
				    }
				    if($teleport >= 1){
					    if(empty($this->haveBlock[$player->getName()])){
						    $this->haveBlock[$player->getName()] = microtime(true);
						    $player->sendMessage(TextFormat::BOLD.TextFormat::RED."Chỗ này có block!");
					    }
					    if(microtime(true) - $this->haveBlock[$player->getName()] >= 3){
					        $player->sendMessage(TextFormat::BOLD.TextFormat::RED."Chỗ này có block!");
						    $this->haveBlock[$player->getName()] = microtime(true);
						}
					}  		
                    $player->teleport($this->clickToTeleport(($teleport - 1), $player));									
				    $this->countDown[$player->getName()] = microtime(true);
				}
			}
		}
	}
}