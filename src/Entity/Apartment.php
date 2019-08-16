<?php
namespace App\Entity;

class Apartment
{
    private $offer;

    /**
     * @return mixed
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param mixed $location
     */
    public function setOffer($offer): void
    {
        $this->offer = $offer;
    }

}