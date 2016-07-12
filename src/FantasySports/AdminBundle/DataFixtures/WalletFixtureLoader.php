<?php

namespace FantasySports\AdminBundle\DataFixtures;

use Nelmio\Alice\Fixtures;

class WalletFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__ . '/wallet.yml', $manager);
    }
}