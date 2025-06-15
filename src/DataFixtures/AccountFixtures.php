<?php

namespace App\DataFixtures;

use App\Domain\Accounting\Account;
use App\Domain\Access\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $u1 = $this->getReference('user-alice', User::class);
        $u2 = $this->getReference('user-bob', User::class);

        $a1 = new Account($u1,'Checking','USD', 1000);
        $a2 = new Account($u1,'Savings','EUR', 1000);
        $a3 = new Account($u2,'Primary','USD', 1000);

        $manager->persist($a1);
        $manager->persist($a2);
        $manager->persist($a3);

        $this->addReference('acc-1',$a1);
        $this->addReference('acc-2',$a2);
        $this->addReference('acc-3',$a3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
