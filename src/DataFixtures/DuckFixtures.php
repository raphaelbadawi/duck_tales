<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Duck;
use App\Entity\ApiToken;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DuckFixtures extends Fixture
{
    private $passwordEncoder;

    /** @var Generator */
    private $faker;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Factory::create();
    }
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $duck = new Duck();
            $duck->setEmail(sprintf('spacebar%d@example.com', $i));
            $duck->setFirstName($this->faker->firstName);
            $duck->setLastName($this->faker->lastName);
            $duck->setDuckName($this->faker->userName);
            $duck->setEmail($this->faker->email);
            $duck->setPassword($this->passwordEncoder->hashPassword(
                $duck,
                'coincoin'
            ));
            $apiToken1 = new ApiToken($duck);
            $apiToken2 = new ApiToken($duck);
            $manager->persist($apiToken1);
            $manager->persist($apiToken2);
            $manager->persist($duck);
        }
        for ($i = 0; $i < 10; $i++) {
            $duck = new Duck();
            $duck->setEmail(sprintf('spacebar%d@example.com', $i));
            $duck->setFirstName($this->faker->firstName);
            $duck->setLastName($this->faker->lastName);
            $duck->setDuckName($this->faker->userName);
            $duck->setEmail($this->faker->email);
            $duck->setRoles(['ROLE_ADMIN']);
            $duck->setPassword($this->passwordEncoder->hashPassword(
                $duck,
                'coincoin'
            ));
            $apiToken1 = new ApiToken($duck);
            $apiToken2 = new ApiToken($duck);
            $manager->persist($apiToken1);
            $manager->persist($apiToken2);
            $manager->persist($duck);
        }
        $manager->flush();
    }
}
