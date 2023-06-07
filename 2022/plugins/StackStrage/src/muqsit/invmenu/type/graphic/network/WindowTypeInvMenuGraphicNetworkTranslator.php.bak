<?php

declare(strict_types=1);

namespace shock95x\auctionhouse\libs\muqsit\invmenu\type\graphic\network;

use shock95x\auctionhouse\libs\muqsit\invmenu\session\InvMenuInfo;
use shock95x\auctionhouse\libs\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class WindowTypeInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	private int $window_type;

	public function __construct(int $window_type){
		$this->window_type = $window_type;
	}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->windowType = $this->window_type;
	}
}