<?php

namespace App\Controller;

use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CamilleChatController extends AbstractController
{
    public function __invoke($topic, HubInterface $hub, Request $request)
    {
        $update = new Update($topic, json_encode($request->getContent()));
        $hub->publish($update);
        return new Response('published!');
    }
}
