<?php
declare(strict_types=1);

namespace revivalpmmp\pureentities\commands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use revivalpmmp\pureentities\entity\BaseEntity;
use revivalpmmp\pureentities\PureEntities;

class RemoveEntitiesCommand extends PureEntitiesXCommand{

	public function __construct(){
		parent::__construct("removeentities");
		$this->setPermission("pureentities.command.peremove");
		$this->setDescription("Removes non-player entities from all loaded levels (by default, only entities generated by PEX)");
		$this->setUsage(TextFormat::ITALIC . TextFormat::YELLOW . "Usage: peremove <opt:all>");
		$this->setAliases(["peremove"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) > 1){
			$this->sendUsage($sender);
			return;
		}
		$all = false;
		if(isset($args[0]) and strcmp(strtolower($args[0]), "all") == 0){
			$all = true;
		}
		$counterLivingEntities = 0;
		$counterOtherEntities = 0;
		foreach(Server::getInstance()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if($entity instanceof Player){
					continue;
				}
				if(!$all and !$entity->namedtag->hasTag("generatedByPEX")){
					continue;
				}
				$entity->close();
				if($entity instanceof BaseEntity){
					$counterLivingEntities++;
				}else{
					$counterOtherEntities++;
				}
			}
			$sender->sendMessage("Removed entities. BaseEntities removed: $counterLivingEntities, other Entities: $counterOtherEntities");
			PureEntities::logOutput("PeRemove: Removed $counterLivingEntities living entities and $counterOtherEntities other entities: ");
		}
	}
}