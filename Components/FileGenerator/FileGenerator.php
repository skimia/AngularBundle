<?php

namespace Skimia\AngularBundle\Components\FileGenerator;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FileGenerator extends Generator{

    /**
     *
     * @var Skimia\AngularBundle\Components\FileGenerator\DependencyManager 
     */
    protected $_depsManager;
    
    protected $_container;
    
    public function __construct(ContainerInterface $container) {
        
        parent::__construct($container->get('kernel'), $container->get('templating'));
        $this->_depsManager = $container->get('skimia_angular.dependency_manager');
        $this->_container = $container;
        $this->init();
    }
    
    public function generate($options,$path=null){
        $file = $this->gen($options);
        if(isset($path)){
            $this->saveFile($file, $path);
        }
        else{
            return $file;
        }
    }
    
    abstract protected function gen($options);
    
    protected function init(){
        
    }
}
