<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table(name: "addresses")]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\OneToOne(inversedBy: "address", targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "string", length: 255)]
    private string $addressLine1;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $addressLine2;

    #[ORM\Column(type: "string", length: 100)]
    private string $city;

    #[ORM\Column(type: "string", length: 20)]
    private string $postalCode;

    #[ORM\Column(type: "string", length: 100)]
    private string $stateProvince;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: "country_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?Country $country = null;

    public function setAddressLine1(mixed $addressLine1): self
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function setAddressLine2(mixed $addressLine2): self
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getStateProvince(): string
    {
        return $this->stateProvince;
    }

    public function setStateProvince(string $stateProvince): void
    {
        $this->stateProvince = $stateProvince;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

}
