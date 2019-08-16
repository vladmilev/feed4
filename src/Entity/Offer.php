<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 */
class Offer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $external_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $internal_id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locality_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sub_locality_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="float")
     */
    private $price_value;

    /**
     * @ORM\Column(type="float")
     */
    private $area_value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): ?int
    {
        return $this->external_id;
    }

    public function setExternalId(int $external_id): self
    {
        $this->external_id = $external_id;

        return $this;
    }

    public function getInternalId(): ?int
    {
        return $this->internal_id;
    }

    public function setInternalId(int $internal_id): self
    {
        $this->internal_id = $internal_id;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getLocalityName(): ?string
    {
        return $this->locality_name;
    }

    public function setLocalityName(?string $locality_name): self
    {
        $this->locality_name = $locality_name;

        return $this;
    }

    public function getSubLocalityName(): ?string
    {
        return $this->sub_locality_name;
    }

    public function setSubLocalityName(?string $sub_locality_name): self
    {
        $this->sub_locality_name = $sub_locality_name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPriceValue(): ?float
    {
        return $this->price_value;
    }

    public function setPriceValue(float $price_value): self
    {
        $this->price_value = $price_value;

        return $this;
    }

    public function getAreaValue(): ?float
    {
        return $this->area_value;
    }

    public function setAreaValue(float $area_value): self
    {
        $this->area_value = $area_value;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
