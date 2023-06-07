<?php

namespace hachkingtohach1\MyItem\customskill{

use hachkingtohach1\MyItem\MyItem;
	
class Jerry extends MyItem{

    public function handle(Player $player): void{
       $player->sendMessage("Welcome to Jerry!");
    }
}