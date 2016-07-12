<?php

namespace FantasySports\AdminBundle\DataFixtures;

use Nelmio\Alice\Fixtures;

class UserFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__ . '/users.yml', $manager);
    }
}