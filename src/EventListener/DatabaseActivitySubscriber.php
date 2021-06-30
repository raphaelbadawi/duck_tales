<?php

namespace App\EventListener;

use App\Entity\Quack;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DatabaseActivitySubscriber implements EventSubscriber
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->syncWithElasticSearch('persist', $args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->syncWithElasticSearch('remove', $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->syncWithElasticSearch('update', $args);
    }

    private function syncWithElasticSearch(string $action, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Quack) {
            return;
        }

        switch ($action) {
            case "persist":
                $this->addToElasticSearch($args->getObject());
                break;
            case "update":
                $this->updateInElasticSearch($args->getObject());
                break;
            case "remove":
                $this->removeFromElasticSearch($args->getObject());
                break;
        }
    }

    private function addToElasticSearch(Quack $quack): void
    {
    }

    private function updateInElasticSearch(Quack $quack): void
    {
    }

    private function removeFromElasticSearch(Quack $quack): void
    {
    }
}
