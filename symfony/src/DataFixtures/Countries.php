<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class Countries extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // List of countries to add, including UAE
        $countries = [
            ['name' => 'UAE'],
            ['name' => 'Egypt'],
            ['name' => 'USA'],
            ['name' => 'Germany'],
            ['name' => 'France'],
        ];

        foreach ($countries as $data) {
            $country = new Country();
            $country->setName($data['name']);
            $manager->persist($country);
        }

        // Save to database
        $manager->flush();

        $manager->flush();
    }
}
