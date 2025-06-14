<?php

namespace App\Domain;

use App\Domain\Access\User;
use App\Domain\Access\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class Access
{
    private UserRepository $userRepository;

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
        $this->userRepository = new UserRepository($registry);
    }

    /**
     * Fetches all users
     * 
     * @return User[] Returns an array of User objects
     */
    public function listUsers(): array
    {
        return $this->userRepository->listUsers();
    }

    /**
     * Fetches user by id
     * 
     * @return User|null Returns an User object or null if not found
     */
    public function getUser(string $id): ?User
    {
        return $this->userRepository->find($id);
    }
} 