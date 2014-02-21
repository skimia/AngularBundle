<?php

namespace Skimia\AngularBundle\Components\JavascriptGenerator;

use Skimia\AngularBundle\Components\BundleManager\Bundle;


class AngularBaseGenerator {
    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    function __construct($container) {
        $this->container = $container;
    }
    
    public function generateModule(Bundle $bundle ){
        $modules = array();
        foreach ($bundle->getModules() as $name=>$module) {
            $modules[] = array('name'=>$this->container->get('skimia_angular.twig.html_helpers')->moduleName($bundle->getName(),ucfirst($name)));
            $path = $bundle->getResourcePath('module',$name);
            if(!file_exists($path)){
                mkdir($path, 0777);
            }
            if(!file_exists($path.'/module.js.twig')){
                //Generation du module
                file_put_contents($path.'/module.js.twig', 
                        $this->container->get('templating')->render('SkimiaAngularBundle:Files:modules.js.twig.twig',
                                array_merge($module, array('name'=>$name,'short_name'=>$bundle->getShortName()))));
            }
        }
        return $modules;
    }
    
}
