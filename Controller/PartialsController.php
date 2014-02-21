<?php

namespace Skimia\AngularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PartialsController extends Controller
{
    public function getPartialAction($bundle, $path)
    {
        $bundleManager = $this->get('skimia_angular.bundle_manager');
        if($bundleManager->hasBundle($bundle)){
            $Bundle = $bundleManager->getBundle($bundle);
            $resource = $Bundle->getResourcePath('partial',$path.'.html.twig');
            return $this->renderPartial($resource);
        }
        else {
            throw $this->createNotFoundException();
        }
        return $this->render('SkimiaAngularBundle:Default:index.html.twig');
    }
    
    protected function renderPartial($path){
        return $this->render($path);
    }
}
