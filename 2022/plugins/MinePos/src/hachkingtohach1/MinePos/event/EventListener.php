<?php

declare(strict_types=1);

namespace hachkingtohach1\MinePos\event;

use hachkingtohach1\MinePos\{MinePos, math\Math}; 
use pocketmine\block\{Block, BlockFactory};
use pocketmine\player\Player;
use pocketmine\math\{Vector3, Vector2};
use pocketmine\utils\{Config,TextFormat};
use pocketmine\item\{Item,ItemFactory};
use pocketmine\network\mcpe\protocol\{MoveActorAbsolutePacket, LevelSoundEventPacket, LevelEventPacket, MovePlayerPacket}; 
use pocketmine\event\{block\BlockBreakEvent,block\BlockPlaceEvent,Listener}; 
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, DoubleTag, FloatTag, StringTag, ListTag, ShortTag, IntTag};
use hachkingtohach1\PlayerStats\PlayerStats;
use hachkingtohach1\Quest\Quest;

class EventListener implements Listener {

    public $plugin;

    public $total = [];	

    public function __construct(MinePos $plugin){
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }	
	
	public function soundBlockBreak($player, $event) {
		$event->setXpDropAmount(1);
		$block = $event->getBlock();				
		
		$player->getWorld()->dropExperience($player->getLocation(), $event->getXpDropAmount());
		$player->getWorld()->addSound($player->getLocation()->asVector3(), new BlockBreakSound($block), $player->getWorld()->getPlayers());
	}
	
	public function onBlockBreak(BlockBreakEvent $event) : void{	
		$block = $event->getBlock();
		$player = $event->getPlayer();
        $nbt = $player->getInventory()->getItemInHand()->getNamedTag();		
		$miningFortune = PlayerStats::getInstance()->calculateMiningFortune($player);
		if($nbt->getTag("Miningfortune", IntTag::class) != null){
			$miningFortune += (int)$nbt->getInt("Miningfortune");
		}
		$level = $player->getWorld();
		$postion = Math::mathPostion($block->getPosition());	
		if(isset($this->plugin->setup[$player->getName()])){
			switch($this->plugin->setup[$player->getName()]) {
				case 3:
				    $event->cancel();	
					$player->sendMessage("[MinePos] Pos1 for MinePos is done!");
					unset($this->plugin->setup[$player->getName()]);
					$this->plugin->pos[$player->getName()] = $postion;
					$this->plugin->setup[$player->getName()] = 4;
                    $player->sendMessage("[MinePos] Now to break one block to set Pos2 for MinePos!");					
				break;
				case 4:
				    $event->cancel();		
					$player->sendMessage("[MinePos] Pos2 for MinePos is done!");
					unset($this->plugin->setup[$player->getName()]);
					$data4 = $this->plugin->data4[$player->getName()];
					$this->plugin->saveMinePos($this->plugin->pos[$player->getName()],
						[
						    $this->plugin->data1[$player->getName()],
						    $this->plugin->data2[$player->getName()],
							$this->plugin->data3[$player->getName()],							
							"$data4",
							$this->plugin->pos[$player->getName()],
							$postion
						]
					);
					unset($this->plugin->pos[$player->getName()]);
					$player->sendMessage("[MinePos] New MinePos area was created!");
                    unset($this->plugin->datasetup[$player->getName()]);
				break;
			}
		}
        $vector3 = new Vector3($block->getPosition()->x, $block->getPosition()->y + 1, $block->getPosition()->z);
		foreach($this->plugin->minepos->getAll() as $i => [$world, $chance1, $chance2, $blocks, $pos1, $pos2]) {	
		    if($pos1 == null && $pos2 == null) return;
			$pos1 = Math::mathStr($pos1);
		    $pos2 = Math::mathStr($pos2);
			if($level->getFolderName() == $world){
				$event->cancel();
                if(min($pos1->x, $pos2->x) <= $player->getLocation()->x
				&& max($pos1->x, $pos2->x) >= $player->getLocation()->x
			    && min($pos1->y, $pos2->y) <= $player->getLocation()->y
			    && max($pos1->y, $pos2->y) >= $player->getLocation()->y
			    && min($pos1->z, $pos2->z) <= $player->getLocation()->z
			    && max($pos1->z, $pos2->x) >= $player->getLocation()->z) {					
			    $array_block = 
				[
				    1 => 4, 4 => 7,
					16 => 1, 14 => 1,
					15 => 1, 21 => 1,
					153 => 1, 73 => 1,
					74 => 1, 56 => 1,
					49 => 1, 12 => 1
				];			
			    $array_item = 
				[
				    1 => [4, 0, 1], 4 => [4, 0, 1],
					16 => [263, 0, 1], 14 => [266, 0, 1],
				    15 => [265, 0, 1], 21 => [351, 4, 1],
					153 => [406, 0, 1], 73 => [331, 0, 1],
					74 => [331, 0, 1], 56 => [264, 0, 1],
					49 => [49, 0, 1], 12 => [12, 0, 1]
				];
			    $array_block_need = 
				[
				    1, 4, 16, 14, 15, 21, 153, 73, 74, 56, 49, 12
				];
                if(in_array($block->getId(), $array_block_need)){			
				    if($this->plugin->getConfig()->get("autoinv") == true){
						foreach($event->getDrops() as $drop) {
			                $player->getInventory()->addItem($drop);
						}
						$event->setDrops([]);
					}
					else{
						$add = 0;
						if(rand(1, 2000) <= $miningFortune){
					        $add = rand(1, 15);
						}
						$player->getWorld()->dropItem($vector3, $this->getDataItem($array_item[$block->getId()][0], $array_item[$block->getId()][1], $array_item[$block->getId()][2] + $add));
					}
					Quest::getInstance()->checkQuest($player, $event);
					PlayerStats::getInstance()->checkLevel($player, rand(1, 30));
				    $this->soundBlockBreak($player, $event);							   
					$level->setblockAt((int)$block->getPosition()->x, (int)$block->getPosition()->y, (int)$block->getPosition()->z, BlockFactory::getInstance()->get($array_block[$block->getId()], 0));					
				    $this->plugin->blockneedreplace->set(Math::mathPostion($block->getPosition()), $level->getDisplayName());
                    $this->plugin->blockneedreplace->save();
				}			
			}}
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event) : void{	
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$level = $player->getWorld();
        $vector3 = new Vector3($block->getPosition()->x, $block->getPosition()->y + 1, $block->getPosition()->z);
		foreach($this->plugin->minepos->getAll() as $i => [$world, $chance1, $chance2, $blocks, $pos1, $pos2]) {	
		    if($pos1 == null && $pos2 == null) return;
			$pos1 = Math::mathStr($pos1);
		    $pos2 = Math::mathStr($pos2);
			if($level->getFolderName() == $world){
				$event->cancel();
			}
		}
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
}