<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: "payments")]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\OneToOne(inversedBy: "payment", targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "string", length: 16, name: 'card_number')]
    private string $creditCardNumber;

    #[ORM\Column(type: "string", length: 5)]
    private string $expirationDate;

    #[ORM\Column(type: "string", length: 4)]
    private string $cvv;

    public function getCreditCardNumber(): string
    {
        return $this->creditCardNumber;
    }

    public function setCreditCardNumber(string $creditCardNumber): void
    {
        $this->creditCardNumber = $creditCardNumber;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getExpirationDate(): string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getCvv(): string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): void
    {
        $this->cvv = $cvv;
    }

}
