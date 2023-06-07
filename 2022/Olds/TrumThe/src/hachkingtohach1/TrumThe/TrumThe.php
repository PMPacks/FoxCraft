<?php

namespace hachkingtohach1\TrumThe;

use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\Listener;
use hachkingtohach1\CoinsAPI\CoinsAPI;

Class TrumThe extends PluginBase implements Listener{
	
	public $coinsapi;
	public $partnerId = "9165196261";
	public $partnerKey = "c6d092fcda054d00ee944a9883740d49";
	
	public function onEnable(){
		$this->coinsapi = $this->getServer()->getPluginManager()->getPlugin("CoinsAPI");
		if($this->coinsapi == null){
			$this->getLogger()->warning("You need install CoinsAPI plugin to use this plugin!");
			$this->getServer()->shutdown();
			return;
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);	
	}

	public function onCommand(CommandSender $sender, Command $cmd,string $label, array $args) :bool{
		if($cmd->getName() == "napthe"){
			if(!(isset($args[0]) || isset($args[1]) || isset($args[2]) || isset($args[3]))){
				$sender->sendMessage(TextFormat::GREEN."/napthe <PIN> <SERI> <GIÁ TRỊ TỪ 10000 - 500000> <NHÀ MẠNG GỒM 1:Viettel - 2:Mobifone - 3:Vinaphone - 4:Vietnammobi - 5:Zing - 6:Gate>");
			    return true;
			}
			if(!(is_numeric($args[0]) || is_numeric($args[1]) || is_numeric($args[2]))){
			    return true;
			} 
			switch($args[3]){
				case "1": $telco = "VIETTEL"; break;
				case "2": $telco = "MOBIFONE"; break;
				case "3": $telco = "VINAPHONE"; break;
			    case "4": $telco = "VIETNAMMOBI"; break;
			    case "5": $telco = "ZING"; break;
				case "6": $telco = "GATE"; break;
			}		 
            $api_url = "https://trumthe.vn//chargingws/v2";		        						
			$arrayPost = array(
	            "telco" => trim($telco),
	            "code" => trim($args[0]),
	            "serial" => trim($args[1]),
	            "amount" => trim($args[2]),
	            "request_id" => intval(time()),
	            "partner_id" => trim($this->partnerId),
	            "sign" => trim("NULL"),
	            "command" => "charging"
            );
            $data_sign = md5($this->partnerKey . trim($args[0]) . trim($args[1]));
            $arrayPost["sign"] = $data_sign;
            $curl = curl_init($api_url);
            curl_setopt_array($curl, array(
	            CURLOPT_POST => true,
	            CURLOPT_HEADER => false,
	            CURLINFO_HEADER_OUT => true,
	            CURLOPT_TIMEOUT => 120,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_SSL_VERIFYPEER => false,
	            CURLOPT_POSTFIELDS => http_build_query($arrayPost)
            ));
            $data = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $result = json_decode($data, true);
            if($status == 200){
				if($result["status"] == 1){
					$sender->sendMessage(TextFormat::BOLD.TextFormat::AQUA."Nạp thành công! chúc bạn chơi vui vẻ ^^");
					$sender->sendMessage(TextFormat::GREEN.".-.|-.-. Thanks you for your purchase .-.-|.-.-.");
				    $coins = ($args[2]/200);
					CoinsAPI::getInstance()->giveCoins($sender, $coins);
				}elseif($result["status"] == 2){
					$sender->sendMessage(TextFormat::BOLD.TextFormat::RED."Nạp thành công! nhưng sai mệnh giá cần chụp và thông báo cho Admin ngay!");
					$sender->sendMessage(TextFormat::YELLOW.".-.|-.-. Thanks you for your purchase .-.-|.-.-.");
				}elseif($result["status"] == 4){
					$sender->sendMessage(TextFormat::BOLD.TextFormat::RED."Nhà mạng này đang bảo trì!");
				}else{
					$sender->sendMessage(TextFormat::BOLD.TextFormat::RED."--------------");
					$sender->sendMessage(TextFormat::BOLD.TextFormat::GREEN."Nhà mạng: ".TextFormat::AQUA.$telco);
					$sender->sendMessage(TextFormat::BOLD.TextFormat::GREEN."Mã thẻ: ".TextFormat::YELLOW.$args[0]);
					$sender->sendMessage(TextFormat::BOLD.TextFormat::GREEN."Seri thẻ: ".TextFormat::YELLOW.$args[1]);
					if($result["status"] == 99){
						$sender->sendMessage(TextFormat::GREEN."-> ".TextFormat::BOLD.TextFormat::RED."Thẻ đang chờ xử lý hoặc đã hết hạn!");
					}else{
					    $sender->sendMessage(TextFormat::GREEN."-> ".TextFormat::BOLD.TextFormat::RED.$result["message"]);
					}
					$sender->sendMessage(TextFormat::BOLD.TextFormat::RED."--------------");
				}
			}
			return true;
		}
		return false;
	}
}