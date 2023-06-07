<?php

namespace hachkingtohach1\Quest\provider\sql;

use mysqli;
use pocketmine\Player;
use hachkingtohach1\Quest\Quest;
use hachkingtohach1\Quest\provider\DataBase;
use hachkingtohach1\Quest\utils\SQLUtils;

class SQL implements DataBase{

    private $db;
    private $plugin;
    public $dbName;
   
    public function __construct(string $dbName){ $this->plugin = Quest::getInstance();
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
			    quest FLOAT,
				status FLOAT
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
			$this->db->query("INSERT INTO user_profile (username, quest, status)
			VALUES ('".$this->db->real_escape_string($player)."', 1000.0, 0.0);");
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
	
	public function getQuest($player) :float{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT quest FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function getStatus($player) :float{
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT status FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function setQuest($player, $add){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$add = (float)$add;
		return $this->db->query("UPDATE user_profile SET quest = quest + $add WHERE username='".$this->db->real_escape_string($player)."'");
	}
	
	public function setStatus($player, $status){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$status = (float)$status;
		return $this->db->query("UPDATE user_profile SET status = $status WHERE username='".$this->db->real_escape_string($player)."'");
	}
	
	public function addStatus($player, $status){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$status = (float)$status;
		return $this->db->query("UPDATE user_profile SET status = status + $status WHERE username='".$this->db->real_escape_string($player)."'");
	}
}