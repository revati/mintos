<?php

namespace App\DataFixtures;

use App\Domain\Access\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $u1 = new User('alice','alice@example.com');
        $u2 = new User('bob','bob@example.com');

        $this->addReference('user-alice',$u1);
        $this->addReference('user-bob',$u2);

        $manager->persist($u1);
        $manager->persist($u2);
        $manager->flush();
    }
}
