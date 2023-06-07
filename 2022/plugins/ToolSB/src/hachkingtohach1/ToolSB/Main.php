<?php

declare(strict_types=1);

namespace hachkingtohach1\ToolSB;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\world\sound\{ClickSound, ExplodeSound};
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use czechpmdevs\buildertools\editors\object\EditorResult;
use czechpmdevs\buildertools\editors\object\FillSession;
use czechpmdevs\buildertools\math\Math;
use czechpmdevs\buildertools\utils\StringToBlockDecoder;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function microtime;

class Main extends PluginBase implements Listener {
	
	private const TAG_BUILDER_WAND = "Builderwand";
	private array $firstBlock = [];
	private array $secondBlock = [];
	private array $block = [];
	private array $fillBlocks = [];
	private array $timeCountDown = [];
	private static $instance;

	public function onLoad() :void{
        self::$instance = $this;
	}
	
    public static function getInstance(): Main{
        return self::$instance;
    }

	public function onEnable() :void{
		$this->saveDefaultConfig();          
        //$this->getScheduler()->scheduleRepeatingTask(new null($this), 20);		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}	
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "builderwand"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(TextFormat::RED."This is command for admin!");
				return false;
			}
			$item = $this->getDataItem(369, 0, 1);
			$item->setCustomName(TextFormat::BOLD.TextFormat::GOLD."Gậy phép của thợ xây dựng");
			$nbt = $item->getNamedTag();
		    $nbt->setString(self::TAG_BUILDER_WAND, "real");
		    $item->setNamedTag($nbt);
			$item->setLore([
			    "",
			    "§6Kĩ năng: Tạo Block §l§eRIGHT CLICK",
				"§7Tạo ra số block mà bạn đã chọn ở điểm 1 và 2,",
				"§7số block phụ thuộc vào túi đồ bạn có bao nhiêu block đó.",
				"",
				"§l§6LEGENDARY",
			]);
			$sender->getInventory()->addItem($item);
			return true;
        }
		return false;
	}
	
	public function banBlocks() :array{
		return [
		    6,
			8,
			9,
			10,
			11,
			26,
			27,
			28,
			31,
			32,
			34,
			37,
			38,
			39,
			40,
			46,
			50,
			51,
			55,
			59,
			63,
			64,
			68,
			69,
			70,
			71,
			72,
			75,
			76,
			77,
			78,
			81,
			83,
			90,
			92,
			93,
			94,
			95,
			96,
			104,
			105,
			106,
			107,
			115,
			117,
			126,
			127,
			131,
			132,
			140,
			141,
			142,
			143,
			144,
			145,
			146,
			147,
			148,
			149,
			150,
			151,
			154,
			167,
			171,
			175,
			178,
			199,
			244,
			250,
			323,
			82,
			112,
			118,
			193,
			194,
			195,
			196,
			197,
			210,
			211,
			212,
			217,
			242,
		];
	}
	
	public function checkTimeCountDown(Player $player) :bool{
		if(!isset($this->timeCountDown[$player->getName()])){
			$this->timeCountDown[$player->getName()] = microtime(true);
			return true;
		}
		$timeDiff = microtime(true) - $this->timeCountDown[$player->getName()];
		if($timeDiff > 2){
			$this->timeCountDown[$player->getName()] = microtime(true);
			return true;
		}
		$time = 2 - $timeDiff;
		$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Chờ ".(int)$time." giây để thực hiện hành động này!");
		return false;
	}
	
	public function onPlayerInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
        $position = $block->getPosition();			
		$nbt = $player->getInventory()->getItemInHand()->getNamedTag();
		if($nbt->getTag(self::TAG_BUILDER_WAND, StringTag::class) != null){	           	
			$item = $player->getInventory()->getItemInHand();
			$item->setLore([
			    "",
			    "§6Kĩ năng: Tạo Block §l§eRIGHT CLICK",
				"§7Tạo ra số block mà bạn đã chọn ở điểm 1 và 2,",
				"§7số block phụ thuộc vào túi đồ bạn có bao nhiêu block đó.",
				"",
				"§l§6LEGENDARY",
			]);
			$player->getInventory()->setItemInHand($item);
			$skyblock = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($player)->getSkyblock();
            if($skyblock == ''){
                return;
            }
            $world = $this->getServer()->getWorldManager()->getWorldByName(SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByUuid($skyblock)->getWorld());	
			if($world->getFolderName() != $player->getWorld()->getFolderName()){
				$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Dụng cụ này chỉ sử dụng trong đảo của bạn!");
				return;
			}
			if(in_array($block->getId(), $this->banBlocks()) or $block->getId() >= 350){
				$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Đây là block không thể thực hiện!");
				return;
			}
			if(!isset($this->firstBlock[$player->getName()])){
				$this->firstBlock[$player->getName()] = $block;
				$this->block[$player->getName()] = $block;
				$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."\n\n\nChọn điểm 2");
				return;
			}
			if(!isset($this->secondBlock[$player->getName()])){
				$this->secondBlock[$player->getName()] = $block;
				$player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."Chạm bất kỳ đâu để khởi tạo!");
				return;
			}
			if(!$this->checkTimeCountDown($player)){
				return;
			}
			$block = $this->block[$player->getName()];
			$blocks = [];
			$startTime = microtime(true);
			$firstBlock = $this->firstBlock[$player->getName()];
			$secondBlock = $this->secondBlock[$player->getName()];		
		    Math::calculateMinAndMaxValues($firstBlock->getPosition(), $secondBlock->getPosition(), true, $minX, $maxX, $minY, $maxY, $minZ, $maxZ);
		    $stringToBlockDecoder = new StringToBlockDecoder($block->getId().":".$block->getMeta(), $player->getInventory()->getItemInHand());
		    if(!$stringToBlockDecoder->isValid()) {
			    return;
			}		 
			for($x = $minX; $x <= $maxX; ++$x) {
				for($z = $minZ; $z <= $maxZ; ++$z) {
					for($y = $minY; $y <= $maxY; ++$y) {
						if(($x !== $minX && $x !== $maxX) && ($y !== $minY && $y !== $maxY) && ($z !== $minZ && $z !== $maxZ)) {
							continue;
						}
						if($player->getWorld()->getBlockAt((int)$x, (int)$y, (int)$z)->getId() == 0){
						    $blocks[(int)$x.(int)$y.(int)$z] = true;
						}
					}
				}
			}
			if(count($blocks) <= 150){
				$countItems = 0;
				foreach($player->getInventory()->getContents() as $case => $checkInventory){
					if($checkInventory->getId() == $block->getId()
						and $checkInventory->getMeta() == $block->getMeta()
						and $checkInventory->getName() == $block->getName()					
					){
						$countItems += $checkInventory->getCount();
					}
				}				
				if($countItems >= count($blocks)){
                    $player->getInventory()->removeItem($this->getDataItem($block->getId(), $block->getMeta(), count($blocks)));			
                    $fillSession = new FillSession($player->getWorld(), false);
		            $fillSession->setDimensions($minX, $maxX, $minZ, $maxZ);
		            $fillSession->loadChunks($player->getWorld());			    
				    for($x = $minX; $x <= $maxX; ++$x) {
				        for($z = $minZ; $z <= $maxZ; ++$z) {
					        for($y = $minY; $y <= $maxY; ++$y) {
						        if(($x !== $minX && $x !== $maxX) && ($y !== $minY && $y !== $maxY) && ($z !== $minZ && $z !== $maxZ)) {
							        continue;
							    }			
                                if($player->getWorld()->getBlockAt((int)$x, (int)$y, (int)$z)->getId() == 0){								
								    $stringToBlockDecoder->nextBlock($fullBlockId);
						            $fillSession->setBlockAt($x, $y, $z, $fullBlockId);
								}
							}	
						}
					}
					$fillSession->reloadChunks($player->getWorld());
		            $fillSession->close();
		            $updates = $fillSession->getChanges();
		            $updates->save();
					$position->getWorld()->addSound($position, new ExplodeSound());
				}else{
					$position->getWorld()->addSound($position, new ClickSound());
					$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Bạn không đủ số lượng block trong túi đồ (".$countItems."/".count($blocks).")");				    
				}
			}else{
				$position->getWorld()->addSound($position, new ClickSound());
				$player->sendMessage(TextFormat::BOLD.TextFormat::RED."Số lượng block bạn đã đặt quá cho phép (".count($blocks)."/150)");
			}
            if(isset($this->firstBlock[$player->getName()])){
				unset($this->firstBlock[$player->getName()]);
			}
			if(isset($this->secondBlock[$player->getName()])){
				unset($this->secondBlock[$player->getName()]);				
			}		
            if(isset($this->block[$player->getName()])){
				unset($this->block[$player->getName()]);				
			}			
		}
	}
}