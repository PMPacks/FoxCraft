<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands;

use Vecnavium\SkyBlocksPM\commands\subcommands\SetWorldCommand;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\AcceptSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\CreateSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\DeleteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\TpSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\InviteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\VisitSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\SetWeatherCommand;
use pocketmine\command\CommandSender;

class SkyBlockCommand extends BaseCommand
{

    public function prepare(): void
    {
        $this->setPermission('skyblockspm.command');
        $this->registerSubCommand(new AcceptSubCommand('accept', 'Chấp nhận lời mời Co-op đảo cùng người khác'));
        $this->registerSubCommand(new CreateSubCommand('create', 'Tạo đảo cho chính bạn'));
        $this->registerSubCommand(new DeleteSubCommand('delete', 'Xóa thành viên khỏi đảo của bạn'));
        $this->registerSubCommand(new SetWorldCommand('setworld', 'Thiết lập thế giới mặc định làm bản sao cho các đảo còn lại'));
        $this->registerSubCommand(new TpSubCommand('tp', 'Dịch chuyển đến đảo của bạn'));
        $this->registerSubCommand(new InviteSubCommand('coop', 'Thêm thành viên vào đảo của bạn'));
        $this->registerSubCommand(new VisitSubCommand('visit', 'Tham quan đảo của mọi người'));
		$this->registerSubCommand(new SetWeatherCommand('setweather', 'Thay đổi mùa trong đảo của bạn'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
    }

}
