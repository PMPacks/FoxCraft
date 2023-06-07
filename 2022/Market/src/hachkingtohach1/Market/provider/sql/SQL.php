<?php

namespace hachkingtohach1\Market\provider\sql;

use mysqli;
use pocketmine\player\Player;
use hachkingtohach1\Market\Market;
use hachkingtohach1\Market\provider\DataBase;
use hachkingtohach1\Market\utils\SQLUtils;

class SQL implements DataBase{

    private $db;
    private $plugin;
    public $dbName;
   
    public function __construct(string $dbName){ $this->plugin = Market::getInstance();
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
			    xuid INT PRIMARY KEY,
				items VARCHAR(8000)
		    );"
		) and
		    !$this->db->query("CREATE TABLE if NOT EXISTS items(
			    id VARCHAR(50) PRIMARY KEY,
				xuid INT,
				seller NTEXT,
				item NTEXT,
				time INT,
				sold INT
		    );")
		){
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
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		
		$result = $this->db->query("SELECT * FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		return $result->num_rows > 0 ? true:false;
	}

    public function itemExists($id){
		$id = strtolower($id);	
		$result = $this->db->query("SELECT * FROM items WHERE id='".$this->db->real_escape_string($id)."'");
		return $result->num_rows > 0 ? true:false;
	}		
	
	public function createProfile($player){
		if($player instanceof Player){
			$nameplayer = $player->getXuid();
		}
		$nameplayer = strtolower($nameplayer);
		if(!$this->accountExists($nameplayer)){
			$this->db->query("INSERT INTO user_profile (xuid, items)
			VALUES ('".$this->db->real_escape_string($nameplayer)."', '');");
			return true;
		}
		return false;
	}
	
	public function createItem($player, int $id, string $item, int $time){
		if($player instanceof Player){
			$nameplayer = $player->getXuid();
		}
		$nameplayer = strtolower($nameplayer);
		$name = strtolower($player->getName());
		if(!$this->itemExists($id)){
			$this->db->query("INSERT INTO user_profile (id, xuid, seller, item, time, sold)
			VALUES ('".$this->db->real_escape_string($id)."', '".$this->db->real_escape_string($nameplayer)."', '$name', '$item', $time, 0);");
			return true;
		}
		return false;
	}
	
	public function removeProfile($player){
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		if($this->db->query("DELETE FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'") === true) return true;
		return false;
	}
	
	public function removeItem($id){
		if($this->db->query("DELETE FROM items WHERE id='".$this->db->real_escape_string($id)."'") === true) return true;
		return false;
	}
	
	public function getItemsPlayer($player){
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT items FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function getItem($id){
		$res = $this->db->query("SELECT item FROM items WHERE id='".$this->db->real_escape_string($id)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	public function addItems($player, string $item){
		$items = explode("|", $this->getItemsPlayer($player));
		$items[] = $item;
		$items = implode("|", $items);
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		return $this->db->query("UPDATE user_profile SET items = $items WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	public function setItemIsSold(string $id, int $type){
		return $this->db->query("UPDATE items SET sold = $type WHERE id='".$this->db->real_escape_string($id)."'");
	}
	
	public function getAll(){
		$res = $this->db->query("SELECT * FROM items");
		$ret = [];
		foreach($res->fetch_all() as $val){
			$ret[$val[0]] = $val[1];
		}
		$res->free();
		return $ret;
	}
}