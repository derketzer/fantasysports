<?php

namespace FantasySports\AdminBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SportMatchFixtureLoader extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__.'/sport_matches.yml', $manager);
    }

    public function getDependencies()
    {
        return array('Tea'); // fixture classes fixture is dependent on
    }
}