<?php

namespace hachkingtohach1\CoinsAPI\provider\sql;

use mysqli;
use pocketmine\Player;
use hachkingtohach1\CoinsAPI\CoinsAPI;
use hachkingtohach1\CoinsAPI\provider\DataBase;
use hachkingtohach1\CoinsAPI\utils\SQLUtils;

class SQL implements DataBase{

    private $db;
    private $plugin;
    public $dbName;
   
    public function __construct(string $dbName){ $this->plugin = CoinsAPI::getInstance();
        $this->dbName = $dbName;
        $config = $this->plugin->getConfig()->getNested("MySQL-Info");

        $this->db = new mysqli(
			$config["Host"] ?? "127.0.0.1",
			$config["User"] ?? "root",
			$config["Password"] ?? "",
			$config["Database"] ?? "1",
			$config["Port"] ?? 3306
		);
			
		if($this->db->connect_error){
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
			return;
		}
		
		if(!$this->db->query("CREATE TABLE if NOT EXISTS user_profile(
			    username VARCHAR(20) PRIMARY KEY,
			    coins FLOAT,
				consumptionhistory FLOAT
		    );"
		)){
		    $this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
		    return;
		}		
    } 
	
	public function getDatabaseName(): string{
        return $this->dbName;
    }

    public function getData(): SQLUtils{}
   
    public function close(): void{}
  
    public function reset(): void{} 	

    public function accountExists($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$result = $this->db->query("SELECT * FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		return $result->num_rows > 0 ? true:false;
	}	
	
	public function createProfile($player) :bool{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(!$this->accountExists($player)){
			$this->db->query("INSERT INTO user_profile (username, coins, consumptionhistory)
			VALUES ('".$this->db->real_escape_string($player)."', 0.0, 0.0);");
			return true;
		}
		return false;
	}
	
	public function removeProfile($player) :bool{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if($this->db->query("DELETE FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'") === true) return true;
		return false;
	}
	
	public function setCoins($player, $coins){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$coins = (float)$coins;
		return $this->db->query("UPDATE user_profile SET coins = $coins WHERE username='".$this->db->real_escape_string($player)."'");
	}
	
	public function reduceCoins($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE user_profile SET coins = coins - $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}
	
	public function giveCoins($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE user_profile SET coins = coins + $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}
	
	public function getCoins($player) :float{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT coins FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function consumptionHistory($player) :float{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT consumptionhistory FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function addConsumptionHistory($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE user_profile SET consumptionhistory = consumptionhistory + $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}
}