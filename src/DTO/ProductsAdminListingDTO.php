<?php

namespace App\DTO;

class ProductsAdminListingDTO
{
    public $id ;
    public $title ;
    public $isActivied;

    public function __construct(int $id, string $title, bool $isActivied)
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->isActivied = $isActivied;

    }
    public function getId()
    {
        return $this->id;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getActivied()
    {
        return $this->isActivied;
    }

}
