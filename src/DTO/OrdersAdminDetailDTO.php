<?php 
namespace App\DTO ;
class OrdersAdminDetailDTO{
    
    public function __construct(public int $id,
    public object $isCreatedAt,
    public string $firstName,
    public string $lastName,
    public string $states)
    {
        $this->id = $id;
        $this->isCreatedAt = $isCreatedAt;
        $this->firstName= $firstName;
        $this->lastName=  $lastName;
        $this->states= $states;
    }
    public function getId (){
        return $this->id ;
    }
    public function getCreatedAt (){
        return $this->isCreatedAt;
    }
    public function getFirstname (){
        return $this->firstName;
    }
    public function getLastName (){
        return $this->lastName ;
    }
    public function getStates (){
        return $this->states ;
    }
}