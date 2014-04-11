<?php

namespace Skimia\AngularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DataController extends Controller
{
    public function getSyncAction($time)
    {
        $syncManager = $this->get('skimia_angular.data.manager');
        $entity = $this->get('doctrine')->getManager()->getRepository('SkimiaProjectManagerBundle:Announcement')->findOneById(2);
        $syncManager->editForAll($entity);

        $mods = $syncManager->getModifications($time);
        debug($mods);
        die();
    }
    
}
