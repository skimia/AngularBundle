<?php

namespace Skimia\AngularBundle\Components\FileGenerator;

class MainGenerator extends FileGenerator{
    
    private $_appGenerator;
    
    private $_routingGenerator;
    
    private $_moduleGenerator;




    protected function init() {
        
        $this->_appGenerator = new AppGenerator($this->_container);
        $this->_routingGenerator = new RoutingGenerator($this->_container);
        $this->_moduleGenerator = new ModuleGenerator($this->_container);
    }
    
    protected function gen($options){
        $options = array_merge($options,array(
            'routing'=>$this->_routingGenerator->generate(array()),
            'modules'=> $this->generateModules(),
            'app'=>$this->_appGenerator->generate(array())
        ));
        return $this->generateFile('SkimiaAngularBundle:JavaScript:main.js.twig', $options);
    }
    
    protected function generateModules(){
        
        $modules = array();
        $jsGenerator = $this->_container->get('skimia_angular.javascript_generator');
        $bundles = $this->_container->get('skimia_angular.bundle_manager')->getBundles();
        foreach ($bundles as $bundle) {
            $this->_depsManager->addDependencies($jsGenerator->generateModule($bundle));
            $bundle_modules = $bundle->getModules();
            foreach ($bundle_modules as $name => $module) {
                $modules[] = $this->_moduleGenerator->generate(array(
                    'bundle'       => $bundle,
                    'bundle_name'  => $bundle->getShortName(),
                    'module_name'  => $name,
                    'dependencies' => $module['dependencies']
                ));
            }
        }
        return $modules;
    }
}

