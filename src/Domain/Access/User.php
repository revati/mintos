<?php

namespace App\Domain\Access;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User
{
    #[ORM\Id, ORM\Column(type: "uuid")]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $email;

    public function __construct(string $email)
    {
        $this->id = Uuid::v4();
        $this->email  = $email;
    }

    public function getId(): Uuid { return $this->id; }
    public function getEmail(): string { return $this->email; }
}
