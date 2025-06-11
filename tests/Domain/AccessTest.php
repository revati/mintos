<?php

namespace App\Tests\Domain;

use App\Domain\Access;
use App\Domain\Access\User;
use App\Domain\Access\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccessTest extends KernelTestCase
{
    private Access $access;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $registry = self::getContainer()->get(ManagerRegistry::class);
        $this->userRepository = new UserRepository($registry);
        $this->access = new Access($registry);

        // Create schema
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->em->clear();
    }

    public function testListUsers(): void
    {
        // Arrange
        $users = [
            new User('alice@example.com'),
            new User('bob@example.com'),
            new User('charlie@example.com')
        ];

        foreach ($users as $user) {
            $this->em->persist($user);
        }
        $this->em->flush();

        // Act
        $result = $this->access->listUsers();

        // Assert
        $this->assertCount(3, $result);
        $this->assertSame('alice@example.com', $result[0]->getEmail());
        $this->assertSame('bob@example.com', $result[1]->getEmail());
        $this->assertSame('charlie@example.com', $result[2]->getEmail());
    }

    public function testListUsersReturnsEmptyArrayWhenNoUsers(): void
    {
        // Act
        $result = $this->access->listUsers();

        // Assert
        $this->assertEmpty($result);
    }
} 