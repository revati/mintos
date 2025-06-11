<?php

namespace App\Tests\Domain\Access;

use App\Domain\Access\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        // Arrange & Act
        $user = new User('john@example.com');

        // Assert
        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertSame('john@example.com', $user->getEmail());
    }

    public function testUserIdIsUnique(): void
    {
        // Arrange & Act
        $user1 = new User('user1@example.com');
        $user2 = new User('user2@example.com');

        // Assert
        $this->assertNotEquals($user1->getId(), $user2->getId());
    }
}
