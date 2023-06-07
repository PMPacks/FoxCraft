<?php

namespace hachkingtohach1\CoinsAPI\provider;

interface DataBase{

   public function getDatabaseName(): string;
   
   public function close(): void;
   
   public function reset(): void;   
}