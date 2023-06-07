<?php

declare(strict_types=1);

namespace shock95x\auctionhouse\libs\muqsit\invmenu\type\util\builder;

use shock95x\auctionhouse\libs\muqsit\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder{

	public function build() : InvMenuType;
}