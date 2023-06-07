<?php

namespace hachkingtohach1\Napthe;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\event\Listener;

Class Napthe extends PluginBase implements Listener{

	/** @var self $instance */
	public static $instance;
	/**
	THESIEURE:
		ID: 4673357261
		KEY: df9751c4eff4070e9695b4d15bba7150
	TRUMTHE:
		ID: 2326357261
		KEY: 75682e5c7932acb0827cd6de91317822
	NHO DOI URL 2 CAI TASK
	2 web trên có cùng api nạp thẻ nên chỉ cần đổi id,key là đc
	*/
	public $partnerId = "9165196261";
	public $partnerKey = "c6d092fcda054d00ee944a9883740d49";
	public $formapi;
	public function onEnable() :void{

		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd,string $label, array $args) :bool{
		if($cmd->getName() == "napthe"){
			$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			if($this->formapi == null){ 
				$sender->sendMessage("Thiếu thư viện, hãy báo lỗi admin");
				return true;
			}
			if(!$sender instanceof Player) return true;
			$this->mainform($sender);			
			return true;
		}
		return false;
	}
	public function mainform(Player $player){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					//case "0";	                  
                     //$this->momoform($player);					 
					//break;
					case "0";	
                     $this->cardinfoForm($player);						
					break;				
                    default:
                    break;					
			}
			});
			$form->setTitle("§l§eNạp thẻ");
			$txt = 
			"§l• §aCảm ơn§f bạn đã ủng hộ server, chỉ cần góp §achút một§f thì nhiều người là §ađủ§f để duy trì server §avĩnh viễn§f\n\n".
			"§l§f• §aShop sẽ ra hệ thống nạp thứ 2 sau, mọi người thông cảm!"
			;
			$form->setContent($txt);
			//$form->addButton("§l§5Momo§r/§2Atm",0,"textures/ui/MCoin");
			$form->addButton("§lCard",0,"textures/ui/mining_fatigue_effect");
			$form->sendToPlayer($player);
			return $form;
	}
	public function momoform(Player $player){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";	                  
                     $this->mainform($player);					 
					break;
					case "1";	
                     						
					break;				
                    default:
                    break;					
			}
			});
			$form->setTitle("§dMoMo§f/§aAtm");
			$txt = 
			"";
			$form->setContent($txt);
			$form->addButton("§lQuay lại",0,"textures/ui/ps4_dpad_right");
			$form->addButton("§lThoát",0,"textures/ui/Ping_Offline_Red_Dark");
			$form->sendToPlayer($player);
			return $form;
	}
	public $chuyendoi =
	[
		"10000" => 10,
		"20000" => 23,
		"50000" => 60,
		"100000" => 135
	];
	public function cardinfoForm(Player $player){
		$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";	                  
						$this->thecaoform($player);
					break;
					case "1";						
					break;				
                    default:
                    break;					
			}
			});
			$form->setTitle("§lThẻ Điện Thoại");
			$txt = 
			"§l§f• §cNếu chọn sai mệnh giá thì sẽ mất thẻ, bạn hãy chú ý!\n\n".
			"§l§f• §eBảng giá: \n\n".
			"§l§f   ➻ §a10.000đ §f= §e10 §2Gem\n\n".
			"§l§f   ➻ §a20.000đ §f= §e23 §2Gem\n\n".
			"§l§f   ➻ §a50.000đ §f= §e60 §2Gem\n\n".
			"§l§f   ➻ §a100.000đ §f= §e135 §2Gem\n\n".
			"§f• §l§eHiện tại đang có event x2 giá trị Gem nhận được !!!\n\n"
			;
			$form->setContent($txt);
			$form->addButton("§lTiếp tục",0,"textures/ui/realms_slot_check");
			$form->addButton("§lThoát",0,"textures/ui/Ping_Offline_Red_Dark");
			$form->sendToPlayer($player);
			return $form;
	}
	public function thecaoform(Player $player,string $loaithe = null,string $menhgia = null,string $seri = null,string $pin = null){
		$loaithe_arr = ["Viettel","Vnmobi","Zing", "Mobifone", "Gate", "Vinaphone"];
		$menhgia_arr = ["10000","20000","50000","100000", "200000"];
		$form = $this->formapi->createCustomForm(function (Player $player, $data) use ($loaithe_arr,$menhgia_arr){
			$result = $data;
			if ($result === null) {
				return true;
			}
			$telco = $loaithe_arr[$result[1]];
			$menhgia = $menhgia_arr[$result[2]];
			$seri = $result[3];
			$pin = $result[4];
			$thongtin = [$telco,$menhgia,$seri,$pin];
			$this->xacnhanform($player,$thongtin);
		});
		
		$form->setTitle("§lThẻ điện thoại");
		$form->addLabel(
			"§f• §l§aHãy đọc và điền thật kỹ các thông tin sau:", 
		);
		$form->addDropdown("§l§f✾§bLoại thẻ:",$loaithe_arr,(int) array_search($loaithe, $loaithe_arr));
		$form->addDropdown("§l§f✾§bMệnh Giá §f(§bchọn sai sẽ mất thẻ§f)§b:",$menhgia_arr,(int) array_search($menhgia, $menhgia_arr));
		$form->addInput("§l§f✾§bSố Seri §f(§bsố được in sẵn bên ngoài§f)§b:","",$seri);
		$form->addInput("§l§f✾§bMã thẻ §f(§bsố bên trong lớp cào§f)§b:","",$pin);		
		$form->sendToPlayer($player);
		return $form;
	}
	public function xacnhanform(Player $player, array $thongtin){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) use($thongtin) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";
						$player->sendTip("§l✾§aĐang kiểm tra thẻ, xin §cđừng chat§a lúc này...");					
						$this->getServer()->getAsyncPool()->submitTask(new NaptheTask([$this->partnerId,$this->partnerKey],strtoupper($thongtin[0]),(string) $thongtin[3],(string) $thongtin[2],(int)$thongtin[1],$player->getName()));			 
					break;
					case "1";
						$this->thecaoform($player,$thongtin[0],$thongtin[1],$thongtin[2],$thongtin[3]);
					break;
					case "2";                  						
					break;					
                    default:
                    break;					
			}
			});
			$form->setTitle("Thông tin thẻ");
			$txt = 
			"§l§f✾Loại thẻ: §l§c".$thongtin[0]."\n\n".
			"§l§f✾Mệnh Giá: §l§c".$thongtin[1]."\n\n".
			"§l§f✾Số Seri: §l§a".$thongtin[2]."\n\n".
			"§l§f✾Mã thẻ: §l§a".$thongtin[3]."\n\n";
			$form->setContent($txt);
			$form->addButton("§lNạp",0,"textures/ui/realms_slot_check");
			$form->addButton("§lQuay lại",0,"textures/ui/recap_glyph_desaturated");
			$form->addButton("§lThoát",0,"textures/ui/Ping_Offline_Red_Dark");
			$form->sendToPlayer($player);
			return $form;
	}
	public function onSuccess(Player $player,string $txt){
		$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if ($result === null) {
				return true;
			}
			});
			$form->setTitle("§lThông tin");
			$form->setContent($txt);
			$form->addButton("§lThoát",-1);
			$form->sendToPlayer($player);
			return $form;
		
	}
	
	public function napThanhCong(string $name,int $giatri, int $gem){	
		$player = $this->getServer()->getPlayerExact($name);
		
		$this->getServer()->broadcastMessage("§l\n=================\n\n§l✾§eCảm ơn bạn §a".$name."§e đã\nnạp thẻ (§a".$giatri."§e) ủng hộ server§r\n\n=================");				
		
		//event x2:
		$gem *=2;
		$api = $this->getServer()->getPluginManager()->getPlugin('Gems');
		$api->addMoney($player, $gem);
		
		$totalmoney = $this->getConfig()->getNested("database.$name.totalmoney");
        if(!isset($totalmoney)){
            $this->getConfig()->setNested("database.$name.totalmoney",$giatri);
			$this->getConfig()->setNested("database.$name.gem",$gem);
        }else{
			$coin = $this->getConfig()->getNested("database.$name.gem");
			$this->getConfig()->setNested("database.$name.totalmoney",$totalmoney + $giatri);
			$this->getConfig()->setNested("database.$name.gem",$gem + $coin);
		}
        $this->getConfig()->save();
		if($player == null){
			return;
		}
		$txt = 
		"§f✾§aNạp thẻ thành công\n\n".
		"§f✾Mệnh giá: §e".$giatri." đồng\n\n".
		"§f✾Bạn nhận được §c".((int) $gem)." Gem\n\n".
		"§f✾§aCảm ơn bạn đã ủng hộ server!!!\n\n";
		$this->onSuccess($player,$txt);
	}
}