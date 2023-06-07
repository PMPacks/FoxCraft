<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\ability;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\entity\effect\{Effect, EffectInstance, StringToEffectParser};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\entity\projectile\Arrow as ArrowEntity;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\world\Position;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\world\sound\BowShootSound;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\entity\object\PrimedTNT;
use DaPigGuy\PiggyCustomEnchants\entities\{HomingArrow, PigProjectile, PiggyFireball, PiggyLightning, PiggyProjectile, PiggyTNT, PiggyWitherSkull};
use slapper\entities\{SlapperEntity, SlapperHuman};
use pocketmine\entity\projectile\{Arrow, Egg, EnderPearl, Snowball, SplashPotion};
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use hachkingtohach1\MyItem\MyItem;
use hachkingtohach1\MyItem\task\RemoveIce;
use hachkingtohach1\MyItem\task\RemoveDarkFire;
use hachkingtohach1\MyItem\task\RemoveEntity;
use hachkingtohach1\MyItem\entity\Bubble;
use hachkingtohach1\MyItem\entity\Throww;
use hachkingtohach1\MyItem\entity\GiantSword;
use hachkingtohach1\MyItem\entity\Bonemerang;
use hachkingtohach1\MyItem\utils\Math;
use hachkingtohach1\MyItem\sounds\Sounds;
use hachkingtohach1\PlayerStats\PlayerStats;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use function intdiv;
use function min;

class Manager implements Listener {		
	
	public const CLICK = 1;
	public const AIM_HAND = 2;
	public const MOVEMENT = 3;
	public const JUMP = 4;
	public const DROP = 5;
	public const ATTACK = 6;
	
    private $ability = [
	    "POISON" => [0, "Make victims poisoned."],
		"WITHER" => [1, "Add wither effect to victims."],
		"LIGHTING" => [2, "Summon lightning to enemy that causing deal more damage."],
		"VAMPIRISM" => [3, "Drain health from victims."],
		"CANNIBALISM" => [4, " Get food from victims."],
		"FREEZE" => [5, "Summon ice pillar that causing enemy frozen."],
		"ROOTS" => [6, "Pull down enemy to the ground."],
		"CURSE" => [7, "Prohibite victims to get healed."],
		"SLOW" => [8, "Make victims slow."],
		"CONFUSE" => [9, "Add nausea effect."],
		"WEAK" => [10, "Reduce melee damage of victims."],
		"BLIND" => [11, "Make victims blinded."],
		"HUNGRY" => [12, " Reduce food level of victims."],
		"HARM" => [13, "Dealt additional damage to victims."],
		"FLAME_WHEEL" => [14, "Add more damage and causing burn."],
		"AIR_SHOCK" => [15, "Pull down enemy and launch above."],
		"DARK_FLAME" => [16, "Burn enemy that cannot be extinguished by water."],
		"DARK_IMPACT" => [17, "Blind the victim and turn view of victim to backward."],
		"BUBBLE_DEFLECTOR" => [18, "Shoot bubble that causing slowness."],
		"TELEPORT" => [19, "Allows the player to teleport at a given distance."],
		"SHIELD" => [20, "Give the player a layer of protective armor."],
		"RAPID_ARROW" => [21, "Shoot countless arrows continuously in the given time."],
		"THROW" => [22, "Throw something at the victim and deal damage to them."],
		"IMPLOSION" => [23, "Deals damage to nearby enemies."],
		"GRAVITY_STORM" => [24, "Create a large rift at your location, pulling all mobs together."],
		"GIANT_SWORD" => [25, "Create a giant sword at your location, damageable all mobs together."],
		"SWING" => [26, "Throw bone a short distance, dealing the damage an arrow would. Deals double damage when coming back. Pierces up to 10 foes."]
	];	
    public $plugin;
	
    public function __construct(MyItem $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
	
	public function checkCustomEnchantments() :bool{
		$plugin = $this->plugin->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants');
		if($plugin == null){
			return false;
		}
		return true;
	}
	
	public function checkEvent(int $id, Player $player) :bool{
		if(!in_array($id, [self::CLICK, self::AIM_HAND, self::MOVEMENT, self::JUMP, self::DROP, self::ATTACK])){
		    $player->sendMessage("-----ID-EVENTS-----");
			$player->sendMessage("1 => Click");
			$player->sendMessage("2 => Aim");
			$player->sendMessage("3 => Movement");
			$player->sendMessage("4 => Jump");
			$player->sendMessage("5 => Drop");
			$player->sendMessage("6 => Attack");
			$player->sendMessage("-------------------");
			return false;
		}		
		return true;
	}
	
	public function listAbility() :array{
		$result = [];
		foreach($this->ability as $name => [$id, $dec]){
			$result[$name] = [$name, $id, $dec];
		}
		return $result;
	}
	
	public function caculateTagAbility(string $tag) :array{
		$result = []; 
		$data = explode(":", $tag);
		foreach($data as $ability){
			$id = explode("|", $ability)[0];
			if(in_array($id, [
			    $this->ability["POISON"][0],
				$this->ability["WITHER"][0],
				$this->ability["SLOW"][0],
				$this->ability["CONFUSE"][0],
				$this->ability["WEAK"][0],
				$this->ability["BLIND"][0],
				$this->ability["HUNGRY"][0],
				$this->ability["HARM"][0],	
				$this->ability["SHIELD"][0]
			])
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
				and !empty(explode("|", $ability)[4])
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"DURATION" => explode("|", $ability)[2],
					"LEVEL" => explode("|", $ability)[3],
					"EVENT" => explode("|", $ability)[4]
				];
			}
			if(in_array($id, [
			    $this->ability["FREEZE"][0],
				$this->ability["DARK_FLAME"][0]
			])
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"TICKS" => explode("|", $ability)[2],
					"EVENT" => explode("|", $ability)[3]
				];
			}
			if(in_array($id, [
			    $this->ability["FLAME_WHEEL"][0],
				$this->ability["CURSE"][0]
			]) 
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])			
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"TIME" => explode("|", $ability)[2],
					"EVENT" => explode("|", $ability)[3]
				];
			}
			if(in_array($id, [
			    $this->ability["LIGHTING"][0],
				$this->ability["GIANT_SWORD"][0]
			])
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
			) $result[] = [
                "TYPE" => explode("|", $ability)[0],
                "STYLE" => explode("|", $ability)[1],
                "DAMAGE" => explode("|", $ability)[2],
                "EVENT" => explode("|", $ability)[3]
            ];
			if(in_array($id, [
			    $this->ability["THROW"][0]
			]) 
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
				and !empty(explode("|", $ability)[4])
				and !empty(explode("|", $ability)[5])
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"DAMAGE" => explode("|", $ability)[2],
					"ID" => explode("|", $ability)[3],
					"META" => explode("|", $ability)[4],
					"EVENT" => explode("|", $ability)[5]
				];
			}
			if(in_array($id, [
			    $this->ability["TELEPORT"][0],
				$this->ability["IMPLOSION"][0],
				$this->ability["GRAVITY_STORM"][0]
			])
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"DISTANCE" => explode("|", $ability)[2],
					"EVENT" => explode("|", $ability)[3]
				];
			}
			if(in_array($id, [
			    $this->ability["VAMPIRISM"][0],
				$this->ability["CANNIBALISM"][0],
				$this->ability["ROOTS"][0],
				$this->ability["AIR_SHOCK"][0],
				$this->ability["DARK_IMPACT"][0],
				$this->ability["BUBBLE_DEFLECTOR"][0],
				$this->ability["SWING"][0]
			])
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
			){
				$result[] = [
				    "TYPE" => explode("|", $ability)[0],
					"STYLE" => explode("|", $ability)[1],
					"EVENT" => explode("|", $ability)[2]
				];
			}
			if($id == $this->ability["RAPID_ARROW"][0]
			    and !empty(explode("|", $ability)[0])
				and !empty(explode("|", $ability)[1])
				and !empty(explode("|", $ability)[2])
				and !empty(explode("|", $ability)[3])
				and !empty(explode("|", $ability)[4])
			) {
                $result[] = [
                    "TYPE" => explode("|", $ability)[0],
                    "STYLE" => explode("|", $ability)[1],
                    "DAMAGE" => explode("|", $ability)[2],
                    "YAW" => explode("|", $ability)[3],
                    "EVENT" => explode("|", $ability)[4]
                ];
            }
		}
		return $result;
	}
	
	public function lightning($entity, Player $player) :void{
		$pos = $entity->getPosition();		
        $light = new AddActorPacket();
		$light->type = "minecraft:lightning_bolt";
		$light->actorRuntimeId = 1;
		$light->actorUniqueId = 1;
		$light->metadata = [];
		$light->motion = null;
		$light->yaw = $entity->getLocation()->getYaw();
		$light->pitch = $entity->getLocation()->getPitch();
		$light->position = new Vector3($pos->getX(), $pos->getY(), $pos->getZ());
		$block = $entity->getWorld()->getBlock($entity->getPosition()->floor()->down());
		$particle = new BlockBreakParticle($block);
        $entity->getWorld()->addParticle($pos->asVector3(), $particle, $entity->getWorld()->getPlayers());
		$sound = new PlaySoundPacket();
		$sound->soundName = "ambient.weather.thunder";
        $sound->x = $pos->getX();
        $sound->y = $pos->getY();
		$sound->z = $pos->getZ();
		$sound->volume = 1;
		$sound->pitch = 1;
		Server::getInstance()->broadcastPackets($entity->getWorld()->getPlayers(), [$light, $sound]);
	}
	
	public function styleAttack(Player $player, $victim, $style) :array{		
		$result = [];
		if($style == "distance"){
			foreach(Math::getEntitiesRadius(5, $player) as $entity){
			    if($this->checkCustomEnchantments() == true){
				    if(!($entity instanceof Bubble) 
					    and !($entity instanceof Throww) 
					    and !($entity instanceof Egg) 
				        and !($entity instanceof SplashPotion) 
					    and !($entity instanceof Arrow) 
					    and !($entity instanceof Snowball) 
				        and !($entity instanceof Projectile)						
					    and !($entity instanceof Painting) 
					    and !($entity instanceof ExperienceOrb) 
					    and !($entity instanceof ItemEntity) 
					    and !($entity instanceof FallingBlock) 
					    and !($entity instanceof PaintingMotive) 
					    and !($entity instanceof PrimedTNT) 
                        and !($entity instanceof Player)                      
                        and !($entity instanceof HomingArrow) 
					    and !($entity instanceof PigProjectile) 
					    and !($entity instanceof PiggyFireball) 
					    and !($entity instanceof PiggyLightning) 
					    and !($entity instanceof PiggyProjectile) 
					    and !($entity instanceof PiggyTNT) 
                        and !($entity instanceof PiggyWitherSkull)
                        and !($entity instanceof SlapperEntity)						
				    ){
				        $result[] = $entity;
				    }
				}else{
					if(!($entity instanceof Bubble) 
					    and !($entity instanceof Throww) 
					    and !($entity instanceof Egg) 
				        and !($entity instanceof SplashPotion) 
					    and !($entity instanceof Arrow) 
					    and !($entity instanceof Snowball) 
				        and !($entity instanceof Projectile)						
					    and !($entity instanceof Painting) 
					    and !($entity instanceof ExperienceOrb) 
					    and !($entity instanceof ItemEntity) 
					    and !($entity instanceof FallingBlock) 
					    and !($entity instanceof PaintingMotive) 
					    and !($entity instanceof PrimedTNT) 
                        and !($entity instanceof Player)
                        and !($entity instanceof HomingArrow) 
					    and !($entity instanceof PigProjectile) 
					    and !($entity instanceof PiggyFireball) 
					    and !($entity instanceof PiggyLightning) 
					    and !($entity instanceof PiggyProjectile) 
					    and !($entity instanceof PiggyTNT) 
                        and !($entity instanceof PiggyWitherSkull)
                        and !($entity instanceof SlapperEntity)							
				    ){
				        $result[] = $entity;
				    }
				}
			}
		}
		if($style == "only"){
			$result[] = $victim;
		}
		return $result;
	}
	
	public function checkTimeCountDown(Player $player) :bool{
		$item = $player->getInventory()->getItemInHand();		
		$nbt = $item->getNamedTag();   
        if(!isset($this->plugin->timeCountDown[$player->getName()])){
            $this->plugin->timeCountDown[$player->getName()] = [];
        }		
        if($nbt->getTag("Timecountdown", IntTag::class) == null){
			return true;
		}			
        if($nbt->getInt("Timecountdown") >= 1){   
            if(empty($this->plugin->timeCountDown[$player->getName()]["ABILITY"])){
                $this->plugin->timeCountDown[$player->getName()]["ABILITY"] = [];
			}        
            if(empty($this->plugin->timeCountDown[$player->getName()]["ABILITY"][$item->getCustomName()])){
                $this->plugin->timeCountDown[$player->getName()]["ABILITY"][$item->getCustomName()] = microtime(true);
			}
            $time = (int)(microtime(true) - $this->plugin->timeCountDown[$player->getName()]["ABILITY"][$item->getCustomName()]);
		    $timeuse = (int) ($nbt->getInt("Timecountdown") - $time);
            if($time < $nbt->getInt("Timecountdown")){
                $message = str_replace("%time", "$timeuse", $this->plugin->getConfig()->get("MESSAGE_TIME_COUNT_DOWN"));
                $player->sendPopup($message);
                return false;
            }else{
                $this->plugin->timeCountDown[$player->getName()]["ABILITY"][$item->getCustomName()] = microtime(true);
			    return true;	
			}		
        }
		return true;
	}
	
	public function caculateAblility(int $type, array $data, $victim, Player $player){
		if(!in_array($type, [self::CLICK, self::AIM_HAND, self::MOVEMENT, self::JUMP, self::DROP, self::ATTACK])) return;	
		$nbt = $player->getInventory()->getItemInHand()->getNamedTag();
		if($nbt->getTag("Mana", IntTag::class) != null){
            if($this->plugin->checkMana($player, $nbt->getInt("Mana")) == false){
                return;
			}
		}
		foreach($data as $ability) if($ability["EVENT"] == $type){           	
            switch($ability["TYPE"]){			
			    case $this->ability["POISON"][0]:					
				    $this->addEffect($victim, "poison", $ability);
                break;
                case $this->ability["WITHER"][0]:	
                    $this->addEffect($victim, "wither", $ability);
                break;
                case $this->ability["LIGHTING"][0]:	
                    $this->summonLightning($victim, $player, $ability);
                break;
                case $this->ability["VAMPIRISM"][0]:	
                    $this->vampirism($victim, $player, $ability);
                break;
                case $this->ability["CANNIBALISM"][0]:	
                    $this->cannibalism($victim, $player, $ability);
                break;
                case $this->ability["FREEZE"][0]:	        
                    $this->freeze($victim, $player, $ability);
                break;
                case $this->ability["ROOTS"][0]:	
                    $this->roots($victim, $player, $ability);
                break;
                case $this->ability["CURSE"][0]:	
                    $this->curse($victim, $player, $ability);
                break;
                case $this->ability["SLOW"][0]:	
				    $this->addEffect($victim, $player, "slowness", $ability);
                break;
                case $this->ability["CONFUSE"][0]:	
				    $this->addEffect($victim, $player, "nausea", $ability);
                break;
                case $this->ability["WEAK"][0]:	
				    $this->addEffect($victim, $player, "weakness", $ability);
                break;
                case $this->ability["BLIND"][0]:	
				    $this->addEffect($victim, $player, "blindness", $ability);
                break;
                case $this->ability["HUNGRY"][0]:	
				    $this->addEffect($victim, $player, "hunger", $ability);
                break;
                case $this->ability["HARM"][0]:	
				    $this->addEffect($victim, $player, "instant_damage", $ability);
                break;
                case $this->ability["FLAME_WHEEL"][0]:	
                    $this->setFire($victim, $player, $ability);
                break;
                case $this->ability["AIR_SHOCK"][0]:	
				    $this->airShock($victim, $player, $ability);
                break;
                case $this->ability["DARK_FLAME"][0]:	
                    $this->darkFire($victim, $player, $ability);
                break;
                case $this->ability["DARK_IMPACT"][0]:	
				    $this->darkImpact($victim, $player, $ability);
                break;
                case $this->ability["BUBBLE_DEFLECTOR"][0]:	
                    $this->addBubble($player);
			    break;
                case $this->ability["TELEPORT"][0]:	
                    $this->teleport($player, $ability);
                break;
                case $this->ability["SHIELD"][0]:	
				    $this->addEffect($player, "absorption", $ability);
			    break;
                case $this->ability["RAPID_ARROW"][0]:	
                    $this->rapidArrow($player, $ability);
                break;
                case $this->ability["THROW"][0]:	
                    $this->createThrow($player, $ability);
			    break;
			    case $this->ability["IMPLOSION"][0]:	
				    $this->implosion($victim, $player, $ability);
			    break;
			    case $this->ability["GRAVITY_STORM"][0]:	
				    $this->gravityStorm($victim, $player, $ability);
			    break;
				case $this->ability["GIANT_SWORD"][0]:	
				    $this->createGiantSword($player, $ability);
			    break;
				case $this->ability["SWING"][0]:	
				    $this->createBonemerang($player, $ability);
			    break;
		    }
        }
	}
	
	public function onEntityRegainHealth(EntityRegainHealthEvent $event){
		$entity = $event->getEntity();
		if(isset($this->curse[$entity->getId()])){
			$timeDiff = microtime(true) - $this->curse[$entity->getId()][0];
			if($timeDiff < $this->curse[$entity->getId()][1]){
			    $event->cancel();
			}else{
				unset($this->curse[$entity->getId()]);
			}
		}
	}
	
	public function receivePacket(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        $origin = $event->getOrigin();        		
		if($packet instanceof AnimatePacket and $origin instanceof Player){
            if($packet->action === AnimatePacket::ACTION_SWING_ARM){
				$item = $origin->getInventory()->getItemInHand();
                if($item->getNamedTag()->getTag("Ability", StringTag::class) != null) if($this->plugin->checkPermission($origin) and $this->checkTimeCountDown($origin)){		
					if(empty($this->plugin->inAbility[$origin->getName()])){						    
						$abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			            foreach($abilities as $ability) if ($ability["EVENT"] == self::AIM_HAND){
							if($this->checkTimeCountDown($origin)){
							    $this->plugin->inAbility[$origin->getName()] = [self::AIM_HAND, $abilities, $origin, $origin];
							}
						}
					}
				}
            }
        }
    }
	
	public function onItemUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();      
		$item = $player->getInventory()->getItemInHand();
		$nbt = $item->getNamedTag();	
		if($nbt->getTag("Sounds", StringTag::class) != null){
			$sound = new Sounds();
			$sound->playSound($player, $nbt->getString("Sounds"));
		}	
        $newLore = [];			
        if($nbt->getTag("Mana", IntTag::class) != null){
			$array1 = ["minecraft:mana", "minecraft:damageability"];
			$array2 = [$nbt->getInt("Mana"), PlayerStats::getInstance()->getIntelligence($player)];
			foreach($item->getLore() as $lore){
			    $newLore[] = str_replace($array1, $array2, $lore);				
			}
			//$newLore[] = TextFormat::DARK_GRAY."Mana Cost: ".TextFormat::AQUA.$nbt->getInt("Mana");
		}	
		/*if($nbt->getTag("Kills", IntTag::class) != null){
			$newLore[] = TextFormat::GRAY."";
			$newLore[] = TextFormat::WHITE."Kills: ".$nbt->getInt("Kills");
			$newLore[] = TextFormat::BLUE."";
		}
		if($nbt->getTag("Reforgeskill", StringTag::class) != null and $nbt->getTag("Reforgename", StringTag::class) != null){
			// example: damage:5|strength:10
			$array = [];
			$reforgeList = explode("|", $nbt->getString("Reforge"));
			foreach($reforgeList as $reforge){
				$type = explode(":", $reforge)[0];
				$value = explode(":", $reforge)[1];
				$array[] = $type." +".$value;
			}
			$newLore[] = TextFormat::PINK.implode(", ", $array);
			if($nbt->getString("Reforgeskill") != ""){
				$newLore[] = TextFormat::RED."";
			    $newLore[] = TextFormat::BLUE.$nbt->getString("Reforgename");
			    $newLore[] = TextFormat::GRAY.$nbt->getString("Reforgeskill");
			    $newLore[] = TextFormat::BLUE."";
			}
		}
        if($nbt->getTag("Rarity", StringTag::class) != null){
			if($nbt->getString("Rarity") != ""){
			    $newLore[] = TextFormat::GRAY."";
			    $newLore[] = $nbt->getString("Rarity");
			}
		}*/  		
		$item->setLore($newLore);
		$player->getInventory()->setItemInHand($item);
        if($item->getNamedTag()->getTag("Ability", StringTag::class) != null){
			if($this->plugin->checkPermission($player)){
				$abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			    foreach($abilities as $ability) if ($ability["EVENT"] == self::CLICK) {
					if($this->checkTimeCountDown($player)){
					    $this->caculateAblility(self::CLICK, $abilities, $player, $player);
					}
				}
			}
		}		
	}
	
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
		/*if($item->getNamedTag()->getTag("Ability", StringTag::class) != null){
			if($this->plugin->checkPermission($player)){
				$abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			    foreach($abilities as $ability) if ($ability["EVENT"] == self::MOVEMENT) {
					if($this->checkTimeCountDown($player)){
					    $this->caculateAblility(self::MOVEMENT, $abilities, $player, $player);
					}
				}
			}
		}*/
	}
	
	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
		/*if($item->getNamedTag()->getTag("Ability", StringTag::class) != null) if($this->plugin->checkPermission($player)){
			$abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			foreach($abilities as $ability) if ($ability["EVENT"] == self::DROP) {
				if($this->checkTimeCountDown($player)){
				    $this->caculateAblility(self::DROP, $abilities, $player, $player);
				}
			}
		}*/
	}
	
	public function onJump(PlayerJumpEvent $event){
        $player = $event->getPlayer();
		$item = $player->getInventory()->getItemInHand();
		/*if($item->getNamedTag()->getTag("Ability", StringTag::class) != null) if($this->plugin->checkPermission($player)){
			$abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			foreach($abilities as $ability) if ($ability["EVENT"] == self::JUMP) {
				if($this->checkTimeCountDown($player)){
				    $this->caculateAblility(self::JUMP, $abilities, $player, $player);
				}
			}
		}*/
	}
	
	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Bubble or $entity instanceof Throww){
			$event->cancel();
		}
		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if($damager instanceof Player){
				if(!$this->plugin->checkPermission($damager)){
					$event->cancel();
				}
				$item = $damager->getInventory()->getItemInHand();
		        if($item->getNamedTag()->getTag("Ability", StringTag::class) != null){
			        $abilities = $this->caculateTagAbility($item->getNamedTag()->getString("Ability"));			    	
			        foreach($abilities as $ability) if ($ability["EVENT"] == self::ATTACK) {
					    if($this->checkTimeCountDown($damager)){
						    $this->caculateAblility(self::ATTACK, $abilities, $entity, $damager);
						}
					}
				}
			}
		}
	}
	
	public function onProjectileHitBlock(ProjectileHitBlockEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof ArrowEntity){
		    $entity->flagForDespawn();
		}
	}
    
	public function addEffect($victim, Player $player, string $effect, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
        if($entities instanceof Player){
			$instance = new EffectInstance(StringToEffectParser::getInstance()->parse($effect), (int)$data["DURATION"], (int)$data["LEVEL"]);
		    $entities->getEffects()->add($instance);
		} else{ 
		    foreach($entities as $entity){
		        $instance = new EffectInstance(StringToEffectParser::getInstance()->parse($effect), (int)$data["DURATION"], (int)$data["LEVEL"]);
		        $entity->getEffects()->add($instance);
			}
		}
	}
	
	public function summonLightning($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $this->lightning($entity, $player);
            $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_MAGIC, (float)($data["DAMAGE"]) + PlayerStats::getInstance()->getDamageAbility($player, $entity, 1));
            $entity->attack($event);
        }
	}
	
	public function vampirism($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $vampirism = ($entity->getHealth() / rand(50, 100)) + 0.01;
            $result = $entity->getHealth() - $vampirism;
            if($result < 0){
                $result = 0;
            }
            $entity->setHealth($result);
            $player->heal(new EntityRegainHealthEvent($player, $vampirism, EntityRegainHealthEvent::CAUSE_REGEN));
        }
	}
	
	public function cannibalism($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $cannibalism = ($entity->getFood() / rand(50, 100)) + 0.01;
            $result = $entity->getFood() - $cannibalism;
            if($result < 0){
                $result = 0;
            }
            $entity->setFood((float)$result);
            $player->addFood($cannibalism);
        }
	}
	
	public function freeze($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
			$x = (int)$entity->getLocation()->x;
            $y = (int)$entity->getLocation()->y;
            $z = (int)$entity->getLocation()->z;
            $pos = "$x,$y,$z";
            if(empty($this->plugin->freeze[$entity->getId()])){
				$this->plugin->freeze[$entity->getId()] = [$entity, $pos, $entity->getWorld()->getName()];
                $this->plugin->getScheduler()->scheduleDelayedTask(new RemoveIce($entity, $pos, $entity->getWorld(), $this->plugin), (int)$data["TICKS"]);
			}
		}
	}
	
	public function roots($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $vector3 = new Vector3($entity->getLocation()->x, $entity->getLocation()->y - 0.75, $entity->getLocation()->z);
            $entity->teleport(Position::fromObject($vector3, $entity->getWorld()));
        }
	}
	
	public function curse($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $this->plugin->curse[$entity->getId()] = [microtime(true), (int)$data["TIME"]];
        }
	}
	
	public function setFire($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
            $entity->setOnFire($ability["TIME"]);
        }
	}
	
	public function airShock($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){
			$motFlat = $player->getDirectionPlane()->normalize()->multiply(5 * 3.75 / 20);
            $mot = new Vector3($motFlat->x, -1, $motFlat->y);
            $entity->setMotion($mot);
            $mot = new Vector3($motFlat->x, 1, $motFlat->y);
            $entity->setMotion($mot);
        }
	}
	
	public function darkFire($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		PlayerStats::getInstance()->damageability[$player->getXuid()] = $player;
        foreach($entities as $entity){
            $this->plugin->darkFire[$player->getName()]["ENTITIES"][$entity->getId()] = $entity;
        }
		$this->plugin->getScheduler()->scheduleDelayedTask(new RemoveDarkFire($player->getName(), $this->plugin), (int)$data["TICKS"]);
	}
	
	public function darkImpact($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		$this->addEffect($victim, "blindness", $data);
        foreach($entities as $entity){
			$entity->setRotation(90.0, 90.0);
        }
	}
	
	public function addBubble(Player $player){
		$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight() - 1, 0), $player->getDirectionVector(), ($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw, -$player->pitch);
        $entity = new Bubble($player->getLocation(), $player->getSkin(), $nbt);
        $entity->setScale(0.01);
        $entity->spawnToAll();
        $this->plugin->getScheduler()->scheduleDelayedTask(new RemoveEntity($entity, $this), 10);
	}
	
	public function teleport(Player $player, array $data){
		$teleport = 0;
		$distance = (int)$data["DISTANCE"];      
        for($check = 0; $check <= $distance; $check++){
            if(!in_array($player->getWorld()->getBlock(Math::getPosDistance($check, $player))->getId(), [0, 17, 18, 78, 171])){
                if($teleport <= 0){
                    $teleport = $check;
                }
            }
        }
        if($teleport <= 0){
            $teleport = $distance + 1;
        }
        if($teleport >= 1){
            if(!isset($this->plugin->haveBlock[$player->getName()])){
                $this->plugin->haveBlock[$player->getName()] = microtime(true);
                $player->sendMessage($this->plugin->getConfig()->get("MSG_BLOCK_IN_TWAY"));
            }
            if(microtime(true) - $this->plugin->haveBlock[$player->getName()] >= 5){
                $player->sendMessage($this->plugin->getConfig()->get("MSG_BLOCK_IN_TWAY"));
                $this->plugin->haveBlock[$player->getName()] = microtime(true);
            }
        }
        $player->teleport(Math::getPosDistance(($teleport - 1), $player));
	}
	
	public function rapidArrow(Player $player, array $data){
		$itemHand = $player->getInventory()->getItemInHand();
		$arrow = VanillaItems::ARROW();
		$inventory = match(true){
			$player->getOffHandInventory()->contains($arrow) => $player->getOffHandInventory(),
			$player->getInventory()->contains($arrow) => $player->getInventory(),
			default => null
		};
		if($player->hasFiniteResources() and $inventory === null){
			$player->sendMessage(TextFormat::RED."No arrow!");
			return;
		}
		$location = $player->getLocation();
		$p = 5 / 20;
		$baseForce = min((($p ** 2) + $p * 2) / 3, 1);
		$entity = new ArrowEntity(Location::fromObject(
		    $player->getEyePos()->add(0, 0, $data["YAW"]),
			$player->getWorld(),
			($location->yaw > 180 ? 360 : 0) - $location->yaw + $data["YAW"],
			-$location->pitch
		), $player, $baseForce >= 1);
		$entity->setMotion($player->getDirectionVector());
		$infinity = $itemHand->hasEnchantment(VanillaEnchantments::INFINITY());
		if($infinity){
			$entity->setPickupMode(ArrowEntity::PICKUP_CREATIVE);
		}
		if(($punchLevel = $itemHand->getEnchantmentLevel(VanillaEnchantments::PUNCH())) > 0){
			$entity->setPunchKnockback($punchLevel);
		}
		if(($powerLevel = $itemHand->getEnchantmentLevel(VanillaEnchantments::POWER())) > 0){
			$entity->setBaseDamage($entity->getBaseDamage() + (($powerLevel + 1) / 2) + (int)$data["DAMAGE"] + PlayerStats::getInstance()->getDamage($player)[0]);
		}else{
			$entity->setBaseDamage($entity->getBaseDamage() + (int)$data["DAMAGE"] + PlayerStats::getInstance()->getDamage($player)[0]);
		}
		if($itemHand->hasEnchantment(VanillaEnchantments::FLAME())){
			$entity->setOnFire(intdiv($entity->getFireTicks(), 20) + 100);
		}
		$ev = new EntityShootBowEvent($player, $itemHand, $entity, $baseForce * 3);
		if($baseForce < 0.1 or $diff < 5 or $player->isSpectator()){
			$ev->cancel();
		}
		$ev->call();
		$entity = $ev->getProjectile(); 
		if($ev->isCancelled()){
			$entity->flagForDespawn();
			return ItemUseResult::FAIL();
		}
		$entity->setMotion($entity->getMotion()->multiply($ev->getForce()));
		if($entity instanceof Projectile){
			$projectileEv = new ProjectileLaunchEvent($entity);
			$projectileEv->call();
		if($projectileEv->isCancelled()){
			$ev->getProjectile()->flagForDespawn();
			return ItemUseResult::FAIL();
		}
		$ev->getProjectile()->spawnToAll();
		$location->getWorld()->addSound($location, new BowShootSound());
		}else{
			$entity->spawnToAll();
		}
		if($player->hasFiniteResources()){
			if(!$infinity){ 
				$inventory?->removeItem($arrow);
			}
			$this->applyDamage(1);
		}
	}
	
	public function createThrow(Player $player, array $data){
		$damage = (int)$data["DAMAGE"] + PlayerStats::getInstance()->getDamage($player)[0];  
		$this->plugin->throw[$player->getName()] = [$damage, $data["ID"], $data["META"]];
		$nbt = $this->plugin->createBaseNBT($player->getPosition()->add(0, $player->getEyeHeight() - 1, 0), $player->getDirectionVector(), ($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw, -$player->getLocation()->pitch);
		$entity = new Throww($player->getLocation(), $nbt);
        $entity->setOwningEntity($player);
        $entity->setScale(1);
        $entity->spawnToAll();                             
        $this->plugin->getScheduler()->scheduleDelayedTask(new RemoveEntity($entity, $this), 20);
	}
	
	public function createGiantSword(Player $player, array $data){
		$damage = (int)($data["DAMAGE"] + PlayerStats::getInstance()->getDamage($player)[0]); 
		$this->plugin->giantSword[$player->getName()] = $damage;
		$nbt = $this->plugin->createBaseNBT($player->getPosition()->add(0, -5, 0), $player->getDirectionVector(), ($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw, -$player->getLocation()->pitch);
		$entity = new GiantSword(Location::fromObject($player->getPosition()->add(0, -5, 0), $player->getWorld()), $nbt);
        $entity->setOwningEntity($player);
        $entity->setScale(5);
        $entity->spawnToAll();      
        $this->lightning($player, $player);		
        $this->plugin->getScheduler()->scheduleDelayedTask(new RemoveEntity($entity, $this), 50);
	}
	
	public function createBonemerang(Player $player, array $data){
		$itemInHand = $player->getInventory()->getItemInHand();
		$damage = (int)(PlayerStats::getInstance()->getDamage($player)[0]); 
		$nbt = $this->plugin->createBaseNBT($player->getPosition(), $player->getDirectionVector(), ($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw, -$player->getLocation()->pitch);
		$entity = new Bonemerang($player->getLocation(), $nbt);
        $entity->setOwningEntity($player);
        $entity->spawnToAll();      
		$microtime = $player->getName().microtime(true);
		$this->plugin->bonemerang[$entity->getId()] = [$damage, $microtime, microtime(true)];
		$this->plugin->bonemerang[$player->getName()][$microtime] = [$itemInHand, $entity];
		$player->getInventory()->setItemInHand($this->plugin->getDataItem(0, 0, 0));
	}
	
	public function implosion($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		$hit = 0;
		$damage = 0;
		$vector = new Vector3($player->getLocation()->x, $player->getLocation()->y, $player->getLocation()->z);
        foreach($entities as $entity){		
			$event = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_MAGIC, 10 + PlayerStats::getInstance()->getDamageAbility($player, $entity, 5));
			$hit += 1;
			$damage += PlayerStats::getInstance()->getDamageAbility($player, $entity, 5);																			
			$entity->attack($event);
		}
		$vectorNew = new Vector3($player->getLocation()->x, $player->getLocation()->y, $player->getLocation()->z);
		$player->getWorld()->addParticle($vectorNew, new HugeExplodeParticle(), $player->getWorld()->getPlayers());
		$player->sendMessage(TextFormat::GRAY."Your implosion hit ".TextFormat::RED.$hit.TextFormat::GRAY." enemies for ".TextFormat::RED.$damage.TextFormat::GRAY." damage.");
	}
	
	public function gravityStorm($victim, Player $player, array $data){
		$entities = $this->styleAttack($player, $victim, $data["STYLE"]);
		foreach($entities as $entity){	
            if(!$entity instanceof Player and !$entity instanceof SlapperHuman){
				$vector = new Vector3($player->getLocation()->x, $player->getLocation()->y, $player->getLocation()->z);
                if($entity->getPosition()->distance($vector) <= 5){
					for($yaw = 0, $y = $vector->y; $y < $vector->y + 4; $yaw += (M_PI * 2) / 20, $y += 1 / 20){
                        $x = -sin($yaw) + $vector->x;
                        $z = cos($yaw) + $vector->z;
						$vectorNew = new vector3($x, $y, $z);
						$player->getWorld()->addParticle($vectorNew, new PortalParticle(), $player->getWorld()->getPlayers());
						$player->getWorld()->addParticle($vectorNew, new SmokeParticle(2), $player->getWorld()->getPlayers());
					}							
			        $entity->teleport(Position::fromObject($vector->add(0, 0.5, 0), $player->getWorld()));
				}
			}
		}
	}
}