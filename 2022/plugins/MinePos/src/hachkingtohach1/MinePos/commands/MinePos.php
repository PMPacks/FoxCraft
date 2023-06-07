<?php

namespace hachkingtohach1\MinePos\Commands;

use pocketmine\command\{Command, CommandSender, PluginCommand, CommandExecutor};
use pocketmine\{Player, plugin\Plugin};
use hachkingtohach1\MinePos\{MinePos as MP, math\Math};  

class MinePos extends Command implements PluginCommand {

    public $plugin;

    public function __construct(MP $plugin){
        parent::__construct("minepos", "MinePos", ("/minepos [world]"), []);
        $this->plugin = $plugin;
    }
	
    public function execute(CommandSender $player, string $commandLabel, array $args){
		if(!$player instanceof Player) return true;
	    if(!$player->isOp()) return true;
		if(!isset($args[0]) or !isset($args[1]) or !isset($args[2])) {
			$player->sendMessage("- Remember: 'chance_stone' and 'chance_blocks' it just was 1 -> 5");
			$player->sendMessage("/minepos [chance_stone] [chance_blocks] [blocks_replace]");
			return;
		}
		$this->plugin->data1[$player->getName()] = $player->getLevel()->getName();
		$this->plugin->data2[$player->getName()] = $args[0];
		$this->plugin->data3[$player->getName()] = $args[1];
		$this->plugin->data4[$player->getName()] = $args[2];
        $this->plugin->setup[$player->getName()] = 3;		
        $player->sendMessage("[MinePos] Now to break one block set pos1 for MinePos");       		
    }
	
    public function getExecutor() : CommandExecutor{
		return $this->executor;
	}

	public function setExecutor(CommandExecutor $executor) : void{
		$this->executor = $executor;
	}
}