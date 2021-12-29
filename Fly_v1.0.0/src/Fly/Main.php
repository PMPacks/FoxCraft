<?php

namespace Fly;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable() {
        
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        if (strtolower($cmd->getName()) === "fly")
            if (empty($args)) {
                if (!$sender->hasPermission("fly.me")) {
                    $sender->sendMessage(TextFormat::RED . "§f[§bFly§f]§c Bạn Không Có Quyền Sử Dụng Lệnh Này");
                    return true;
                } else {
                    if (!$sender instanceof Player) {
                        $sender->sendMessage(TextFormat::RED . "§f[§bFly§f]§c Vui Lòng Sử Dụng Lệnh Trong Trò Chơi");
                        return true;
                    }
                    if ($sender->getAllowFlight()) {
                        $sender->setAllowFlight(false);
                        $sender->sendMessage(TextFormat::RED . "§f[§bFly§f]§a Đã Tắt Chế Độ Bay");
                        return true;
                    } else {
                        $sender->setAllowFlight(true);
                        $sender->sendMessage(TextFormat::GREEN . "§f[§bFly§f]§a Đã Bật Chế Độ Bay");
                        return true;
                    }
                }
            }
        return false;
    }

}
