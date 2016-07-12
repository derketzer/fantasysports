<?php

namespace FantasySports\AdminBundle\DataFixtures;

use Nelmio\Alice\Fixtures;

class TeamFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__.'/teams.yml', $manager);
    }
}