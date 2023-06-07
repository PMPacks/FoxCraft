<?php

namespace onebone\Gems\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;

use onebone\Gems\Gems;

class TakeMoneyCommand extends Command
{

    public function __construct(private Gems $plugin)
    {
        $desc = $plugin->getCommandMessage("takegems");
        parent::__construct("takegems", $desc["description"], $desc["usage"]);

        $this->setPermission("gems.command.takemoney");

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

        if ($amount < 0) {
            $sender->sendMessage($this->plugin->getMessage("takemoney-invalid-number", [$amount], $sender->getName()));
            return true;
        }

        $result = $this->plugin->reduceMoney($player, $amount, false, 'Gems.command.take');
        switch ($result) {
            case Gems::RET_INVALID:
                $sender->sendMessage($this->plugin->getMessage("takemoney-player-lack-of-money", [$player, $amount, $this->plugin->myMoney($player)], $sender->getName()));
                break;
            case Gems::RET_SUCCESS:
                $sender->sendMessage($this->plugin->getMessage("takemoney-took-money", [$player, $amount], $sender->getName()));

                if ($p instanceof Player) {
                    $p->sendMessage($this->plugin->getMessage("takemoney-money-taken", [$amount], $sender->getName()));
                }
                break;
            case Gems::RET_CANCELLED:
                $sender->sendMessage($this->plugin->getMessage("takemoney-failed", [], $sender->getName()));
                break;
            case Gems::RET_NO_ACCOUNT:
                $sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
                break;
        }

        return true;
    }
}
