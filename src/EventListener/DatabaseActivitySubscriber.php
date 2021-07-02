<?php

namespace App\EventListener;

use Exception;
use App\Entity\Quack;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DatabaseActivitySubscriber implements EventSubscriber
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::preRemove,
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

    public function preRemove(LifecycleEventArgs $args): void
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
            case "update":
                $this->insertToElasticSearch($args->getObject());
                break;
            case "remove":
                $this->removeFromElasticSearch($args->getObject());
                break;
        }
    }

    private function insertToElasticSearch(Quack $quack): void
    {
        $response = $this->httpClient->request('POST', $_ENV['ELASTICSEARCH_ENDPOINT'] . '/quacks/_doc/' . $quack->getId(), $this->mapQuackToElasticSearch($quack));
    }

    private function mapQuackToElasticSearch(Quack $quack): array
    {
        return [
            "json" => [
                'author' => $quack->getDuck()->getDuckname(),
                'content' => $quack->getContent(),
                'createdAt' => $quack->getCreatedAt(),
                'tags' => array_map(fn ($tag) => $tag->getContent(), $quack->getTags()->toArray())
            ]
        ];
    }

    private function removeFromElasticSearch(Quack $quack): void
    {
        $needle = $this->httpClient->request('POST', $_ENV['ELASTICSEARCH_ENDPOINT'] . '/quacks/_doc/_search', [
            "json" => [
                "query" => [
                    "match" => [
                        "_id" => $quack->getId()
                    ]
                ]
            ]
        ]);
        $needle = json_decode($needle->getContent());
        if ($needle->hits->total->value == 0) {
            return;
        }
        $response = $this->httpClient->request('DELETE', $_ENV['ELASTICSEARCH_ENDPOINT'] . '/quacks/_doc/' . $quack->getId());
    }
}
