<?php

namespace App\DTO;

class ordersClientListingDTO {
    public $id ;
    public $states ;
    public $userId;
    public $isCreatedAt ;
    public function __construct(int $id ,
    string $states ,
    int $userId,
    object $isCreatedAt){
        $this->id =$id;
        $this->states =$states;
        $this->userId = $userId;
        $this->isCreatedAt = $isCreatedAt ;
    }
   public function  getId(){return $this->id;}
   public function  getStates(){return $this->states;}
   public function  getUser(){return $this->userId;}
   public function  getIsCreatedAt(){return $this->isCreatedAt;}
}