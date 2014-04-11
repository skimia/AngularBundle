<?php

namespace Skimia\AngularBundle\Components\BundleManager;

use Symfony\Component\HttpKernel\Kernel;

class Bundle{
    public function __construct(Kernel $kernel, array $config){
        $this->_kernel = $kernel;
        $this->_bundleName = $config['bundle_name'];
        $this->_shortName = $config['short_name'];
        $this->_modules = $config['modules'];
        $this->_directory = $kernel->locateResource($config['directory']);
        $this->_namespace = str_replace('\\'.$config['bundle_name'],'',get_class($kernel->getBundle($config['bundle_name'])));
        $kernel->getContainer()->get('twig.loader')->addPath($this->_directory, $this->_shortName);
    }
    protected $_kernel;
    protected $_bundleName;
    protected $_shortName;
    protected $_modules;
    protected $_directory;
    protected $_namespace;
    
    public function getResourcePath($type = null, $name = null){
        if(func_num_args()==0){
            return $this->_directory;
        }
        switch ($type){
            case 'partial':
                return '@'.$this->_shortName.'/partials/'.$name;
            case 'module':
                return $this->_directory.'/'.$name;
            case 'module_view':
                return '@'.$this->_shortName.'/'.$name;
        }
    }
    
    public function getShortName(){
        return $this->_shortName;
    }
    public function getName(){
        return $this->_bundleName;
    }
    public function getNamespace(){
        return $this->_namespace;
    }
    public function getModules(){
        return $this->_modules;
    }
    
}
