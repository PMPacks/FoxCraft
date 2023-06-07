<?php

namespace hachkingtohach1\Market\provider;

interface DataBase{

   public function getDatabaseName(): string;
   
   public function close(): void;
   
   public function reset(): void;   
}