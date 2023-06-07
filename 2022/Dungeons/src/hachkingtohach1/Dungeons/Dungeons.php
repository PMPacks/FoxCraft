<?php

namespace hachkingtohach1\Dungeons;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use hachkingtohach1\Dungeons\task\ArenaScheduler;

class Dungeons extends PluginBase implements Listener {
	
	// FLOORS
	public const FLOOR_ENTRANCE = 0;
	public const FLOOR_ONE = 1;
	public const FLOOR_TWO = 2;
	public const FLOOR_THREE = 3;
	public const FLOOR_FOUR = 4;
	public const FLOOR_FIVE = 5;
	public const FLOOR_SIX = 6;
    public const FLOOR_SEVEN = 7;
	// MODES
    public const MODE_NORMAL = 0;
    public const MODE_MASTER = 1;	
	// CLASS
	public const BERSERK = 0;
	public const ARCHER = 1;
	public const MAGE = 2;
	public const TANK = 3;
	public const HEALER = 4;
	// DATA
	public $rooms = [];
	public $arenas = [];
	public $ingame = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new ArenaScheduler($this), 20);
    }
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($command->getName() == "dungeons"){
			if(!$sender->isOp()){
				$sender->sendMessage(TextFormat::RED."This is command for admin!");
				return false;
			}
			///
			return true;
        }
		return true;
	}
	
	public function setMode(string $name) :string{
		switch($this->arenas[$name]['mode']){
			case self::MODE_NORMAL:
			    $mode = "Normal";
			    break; 
		    case self::MODE_MASTER:
			    $mode = "Master";
			    break;
            default:
			    $mode = "error";
                echo "\n[<-> Some error occurred in ".var_dump($this->arenas[$name]['mode'])." <->]";   			
		}
		return $mode;
	}
	
	public function getFloor(string $name) :string{
		switch($this->arenas[$name]['floor']){
			case self::FLOOR_ENTRANCE:
			    $floor = "Entrance";
			    break;
			case self::FLOOR_ONE:
			    $floor = "F1";
			    break; 
		    case self::FLOOR_TWO:
			    $floor = "F2";
			    break;
			case self::FLOOR_THREE:
			    $floor = "F3";
			    break;
			case self::FLOOR_FOUR:
			    $floor = "F4";
			    break;
			case self::FLOOR_FIVE:
			    $floor = "F5";
			    break;
			case self::FLOOR_SIX:
			    $floor = "F6";
			    break;
			case self::FLOOR_SEVEN:
			    $floor = "F7";
			    break;
			default:
			    $floor = "error";
                echo "\n[<-> Some error occurred in ".var_dump($this->arenas[$name]['mode'])." <->]";   
		}
		return $floor;
	}
	
	public function setData(string $name, string $nameData, $data){
		if(!empty($this->arenas[$name])){
			$this->arenas[$name][$nameData] = $data;
		}else{
			echo "\n[<-> Some error occurred in ".var_dump($this->arenas[$name][$nameData])." <->]";
		}
	}
	
	public function loadMap(string $folderName) :?Level{
		$DS = DIRECTORY_SEPARATOR;
		$path = $this->getServer()->getDataPath();		
        if(!file_exists($path."worlds".$DS.$folderName)) return null;    
        if(!$this->getServer()->isLevelGenerated($folderName)) return null;		
        if($this->getServer()->isLevelLoaded($folderName)) {
            $this->getServer()->getLevelByName($folderName)->unload(true);
        }
        $zipPath = $this->getDataFolder()."saves".$DS.$folderName.".zip";
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo($path."worlds");
        $zipArchive->close();
        $this->getServer()->loadLevel($folderName);
        return $this->getServer()->getLevelByName($folderName);
    }

    public function getEmptyData(string $name){
		$data = [		    
		    'door_1' => false,
			'door_2' => false,
			'door_3' => false,
			'door_4' => false,
			'door_5' => false,
			'door_6' => false,
			'door_7' => false,
			'door_8' => false,
			'door_9' => false,
			'door_10' => false,
			'count_bless' => 10,
			'boss_watcher' => false,
			'boss_zombie' => false,
			'boss_creeper' => false,
			'boss_skeleton' => false,
			'boss_ender' => false,
			'boss_wither' => false,
		    'items_chest_dungeons' => [],
			'players' => [],
			'the_spawn' => false,
			'score' => 0,
			'floor' => 0,
			'mode' => 0,
			'time_elapsed' => 0,
			'status_clear' => 0,
			'id' => 0,
			'enable' => false
		];
		$this->arenas[$name] = $data;
	}		
}