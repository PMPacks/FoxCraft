<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\utils;

use pocketmine\player\Player;
use pocketmine\math\Vector3;

class Math{
	
	public static function getDistanceHaveY(Vector3 $to, Vector3 $from){
		$dx = $to->getX() - $from->getX();
        $dy = $to->getY() - $from->getY();
		return sqrt(($dx * $dx) + ($dy * $dy));
	}
	
	public static function getDistanceHaveZ(Vector3 $to, Vector3 $from) :float{
		$distX = $to->getX() - $from->getX();
        $distZ = $to->getZ() - $from->getZ();
        $distanceSquared = $distX * $distX + $distZ * $distZ;
        return abs(sqrt($distanceSquared));
	}
	
	public static function getDistance(Position $a, Position $b){
        return sqrt(pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2) + pow($a->z - $b->z, 2));
	}
   
    public static function DistanceY(Vector3 $from, Vector3 $to) :float{
        return $from->getY() - $to->getY();
    }
	
	public static function getPosDistance(int $distance, $entity){
		$delta = $entity->getDirectionVector()->multiply($distance);
		return $entity->getLocation()->add($delta->x, $delta->y, $delta->z);
	}
	
	public static function getEntitiesRadius($distance, $entity) :array{
		$result = [];
		foreach($entity->getWorld()->getEntities() as $target){	
            $vector = new Vector3($entity->getLocation()->x, $entity->getLocation()->y, $entity->getLocation()->z);
            if($target->getPosition()->distance($vector) <= $distance){
				$result[] = $target;
			}
		}
		return $result;
	}
}