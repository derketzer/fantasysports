<?php

namespace FantasySports\AdminBundle\DataFixtures;

use Nelmio\Alice\Fixtures;

class PhaseFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__ . '/phases.yml', $manager);
    }
}