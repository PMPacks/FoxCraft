<?php

namespace hachkingtohach1\Market\utils;

use pocketmine\utils\TextFormat;

class Math{

    public static function calculateTime($time) :string{
		$timeNow = time();
		if($time > $timeNow){	
			$timecal = $time - $timeNow;
			$day = floor($timecal / 86400);
			$hourSeconds = $timecal % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$secW = $minuteSec % 60;
			$second = ceil($secW);
			if($day >= 1){
				$expiration = $day."d ".$hour."h ".$minute."m ".$second."s";				
			}
			else{
				if($hour >= 1){
					$expiration = "00d ".$hour."h ".$minute."m ".$second."s";
				}
				else{
					if($minute >= 1){
						$expiration = "00d "."00h ".$minute."m ".$second."s";
					}
					elseif($second >= 1){
						$expiration = "00d "."00h "."00m ".$second."s";
					}
					else{
					    $expiration = TextFormat::GREEN."Ended!";
					}
				}
			} 
            return $expiration;			
		}
		return "Error";
	}
}