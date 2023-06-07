<?php

namespace hachkingtohach1\Quest\provider;

interface DataBase{

   public function getDatabaseName(): string;
   
   public function close(): void;
   
   public function reset(): void;   
}