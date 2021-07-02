<?php

namespace App\EventListener;

use App\Entity\Quack;
use App\Entity\QuackHistory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class QuackListener
{
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(Quack $quack, LifecycleEventArgs $event)
    {
        if ($quack->getParent() === NULL) {
            $this->addQuackHistory($quack);
        }
    }

    public function postUpdate(Quack $quack, LifecycleEventArgs $event)
    {
        if ($quack->getParent() === NULL) {
            $this->addQuackHistory($quack);
        }
    }

    private function addQuackHistory(Quack $quack)
    {
        $quackHistory = new QuackHistory();
        $originalQuack = null !== $quack->getHistory() && !empty($quack->getHistory()) ? $quack->getHistory()[0]->getOriginalQuack() : $quack;

        $quackHistory->setOriginalQuack($originalQuack);
        $quackHistory->setContent($quack->getContent());
        $quackHistory->setCreatedAt($quack->getCreatedAt());

        $quack->addHistory($quackHistory);

        $this->entityManager->persist($quackHistory);
        $this->entityManager->persist($quack);
        $this->entityManager->flush();
    }
}
