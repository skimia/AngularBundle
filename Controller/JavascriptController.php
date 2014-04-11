<?php

namespace Skimia\AngularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Skimia\AngularBundle\Components\FileGenerator\MyJsMin;

use Doctrine\ORM\Mapping\ClassMetadata;

class JavascriptController extends Controller {


    public function homeAction() {
        $generator = $this->get('skimia_angular.main_generator');
        $app = $generator->generate(array());
        if(!in_array($this->get('kernel')->getEnvironment(), array('test', 'dev'))) {
            $jsmin = new MyJsMin($app);
            $app = $jsmin->minify();
            file_put_contents(WEB_DIRECTORY.DIRECTORY_SEPARATOR.'app.js', $app);
        }
        $response = new Response($app);
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
        
    }


}
