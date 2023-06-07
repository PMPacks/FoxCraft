<?php

namespace hachkingtohach1\PlayerStats\provider;

interface DataBase{

   public function getDatabaseName(): string;
   
   public function close(): void;
   
   public function reset(): void;   
}