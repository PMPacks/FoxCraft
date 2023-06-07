<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use hachkingtohach1\FCCore\FCCore;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;

class SetWeatherCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.setweather');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        $pure = SkyBlocksPM::getInstance()->getServer()->getPluginManager()->getPlugin('PurePerms');
        $userGroup = $pure->getUserDataMgr()->getGroup($sender);    					
        if($userGroup == "Vip++"){
		    FCCore::getInstance()->formMua($sender);
		}else{
			$sender->sendMessage("§l§cChỉ có VIP++ mới xài được lệnh này!");
		}
    }
}
