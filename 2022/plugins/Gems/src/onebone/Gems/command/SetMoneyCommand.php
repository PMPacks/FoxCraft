<?php

namespace onebone\Gems\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;

use onebone\Gems\Gems;

class SetMoneyCommand extends Command
{

    public function __construct(private Gems $plugin)
    {
        $desc = $plugin->getCommandMessage("setgems");
        parent::__construct("setgems", $desc["description"], $desc["usage"]);

        $this->setPermission("gems.command.setmoney");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $params): bool
    {
        if (!$this->plugin->isEnabled()) return false;
        if (!$this->testPermission($sender)) {
            return false;
        }

        $player = array_shift($params);
        $amount = array_shift($params);

        if (!is_numeric($amount)) {
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return true;
        }

        if (($p = $this->plugin->getServer()->getPlayerByPrefix($player)) instanceof Player) {
            $player = $p->getName();
        }

        $result = $this->plugin->setMoney($player, $amount, false, 'Gems.command.set');
        switch ($result) {
            case Gems::RET_INVALID:
                $sender->sendMessage($this->plugin->getMessage("setmoney-invalid-number", [$amount], $sender->getName()));
                break;
            case Gems::RET_NO_ACCOUNT:
                $sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
                break;
            case Gems::RET_CANCELLED:
                $sender->sendMessage($this->plugin->getMessage("setmoney-failed", [], $sender->getName()));
                break;
            case Gems::RET_SUCCESS:
                $sender->sendMessage($this->plugin->getMessage("setmoney-setmoney", [$player, $amount], $sender->getName()));

                if ($p instanceof Player) {
                    $p->sendMessage($this->plugin->getMessage("setmoney-set", [$amount], $p->getName()));
                }
                break;
            default:
                $sender->sendMessage("WTF");
        }
        return true;
    }
}
