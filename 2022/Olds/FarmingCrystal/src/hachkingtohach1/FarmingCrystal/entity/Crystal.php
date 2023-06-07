<?php
/*
HOẠT ĐỘNG 8 - NGÀY 6.12.21
1. Anh/Chị hãy nghiên cứu nội dung 6 cặp phạm trù
trong phép biện chứng duy vật và cho mỗi cặp
phạm trù 1 ví dụ cụ thể (chủ yếu để phân biệt khái niệm).
2. Lưu với cú pháp: Hoạt động 8 
3. Tải lên thư mục cá nhân trong HOẠT ĐỘNG CỦA SINH VIÊN.
*/
declare(strict_types=1);

namespace hachkingtohach1\FarmingCrystal\entity;

use pocketmine\Player;
use pocketmine\entity\Human;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\EntityIds;
use pocketmine\network\mcpe\protocol\AddActorPacket as AddEntityPacket;
use pocketmine\entity\{Effect, EffectInstance};
use hachkingtohach1\FarmingCrystal\FarmingCrystal;

class Crystal extends Human{
	
	public function getName() : String{
		return "Crystal";
	}
	
	public function onUpdate(int $currentTick):bool{
		if(isset(FarmingCrystal::getInstance()->register[$this->getId()])){
		    $this->setImmobile(true);
		    $this->setNameTagVisible(true);
            $this->setNameTagAlwaysVisible(true);
		    $this->setMaxHealth(1000000);		
		    $this->setHealth(1000000);  
		    $this->yaw += 20;
		    $this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 999));
		    $vector = new Vector3($this->x, $this->y + 2, $this->z);
		    $particle = new DustParticle($vector, 255, 255, 255);
		    $this->getLevel()->addParticle($particle);
		    $this->move($this->motion->x, $this->motion->y, $this->motion->z);
		    $this->updateMovement();			    
		}
		return parent::onUpdate($currentTick);
	}
	
	public function attack(EntityDamageEvent $source) : void{
		$source->setCancelled(true);
	}
}