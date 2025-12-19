<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\DispatchNoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DispatchNoteRepository::class)]
class DispatchNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $shopwareOrderNumber = null;

    #[ORM\Column(type: Types::JSON)]
    private array $customerDeliveryAddress = [];

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $customerPhoneNumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShopwareOrderNumber(): ?string
    {
        return $this->shopwareOrderNumber;
    }

    public function setShopwareOrderNumber(string $shopwareOrderNumber): static
    {
        $this->shopwareOrderNumber = $shopwareOrderNumber;

        return $this;
    }

    public function getCustomerDeliveryAddress(): array
    {
        return $this->customerDeliveryAddress;
    }

    public function setCustomerDeliveryAddress(array $customerDeliveryAddress): static
    {
        $this->customerDeliveryAddress = $customerDeliveryAddress;

        return $this;
    }

    public function getCustomerPhoneNumber(): ?string
    {
        return $this->customerPhoneNumber;
    }

    public function setCustomerPhoneNumber(?string $customerPhoneNumber): static
    {
        $this->customerPhoneNumber = $customerPhoneNumber;

        return $this;
    }
}
