<?php

declare(strict_types=1);

namespace hachkingtohach1\Alerts;

use pocketmine\utils\Process;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use hachkingtohach1\Alerts\task\sendMessage;
use hachkingtohach1\Alerts\task\sendTip;
use hachkingtohach1\Alerts\task\sendPopup;
use hachkingtohach1\Alerts\task\sendTitle;

class Alerts extends PluginBase {
	
	/** @var null $instance*/
	private static $instance = null;
	
	public $prefix = "[Alerts] ";
	
	public function onLoad(): void{ self::$instance = $this; }
	
	public static function getInstance(): Alerts{ return self::$instance; }
	
	public function onEnable(){
		$sendAlerts = $this->getConfig()->get("sendAlerts")["enable"];
		$sendTips = $this->getConfig()->get("sendTips")["enable"];
		$sendPopup = $this->getConfig()->get("sendPopup")["enable"];
		$sendTitle = $this->getConfig()->get("sendTitle")["enable"];
		if(
		    !in_array($sendAlerts, [true, false]) or !in_array($sendTips, [true, false]) or
			!in_array($sendPopup, [true, false]) or !in_array($sendTitle, [true, false]) 
		){
			$this->getLogger()->warning("You need to review something in config. yml of plugin Alerts!");
		}
		$this->timeCountDown = $this->getConfig()->get("timeCountDown");
		$this->getScheduler()->scheduleRepeatingTask(new sendMessage(), 20);
		$this->getScheduler()->scheduleRepeatingTask(new sendTip(), 20);
	    $this->getScheduler()->scheduleRepeatingTask(new sendPopup(), 20);
	    $this->getScheduler()->scheduleRepeatingTask(new sendTitle(), 20);
	}
	
	public function onDisable(){}

    public function replaceFormat(string $message){
		$text = [
		    "{MAXPLAYERS}",
  		    "{TOTALPLAYERS}",
			"{TIME}",
			"{TODAY}",
			"{PREFIX}"
		];
		$replace = [
		    $this->getServer()->getMaxPlayers(),
			count($this->getServer()->getOnlinePlayers()),
			date("H:i:s"),
			date("d/m/Y"),
			$this->prefix
		];
        $message = str_replace($text, $replace, $message);		
	    return TextFormat::colorize($message);
	}		
}