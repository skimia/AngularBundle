<?php

namespace Skimia\AngularBundle\Components\FileGenerator;


class DependencyManager extends Generator{
    
    protected $_filesNameDependencies = array();
    
    protected $_filesDependencies = array();
    
    protected $_dependencies = array();
    
    public function addDependency($name, $path=null){
        $this->_dependencies[] = $name;
        if(isset($path)){
            $this->addFileDependency($path);
        }
    }
    
    public function addFileDependency($path){
        
        $this->_filesDependencies[] = $this->generateFileDependency($path);
        $this->_filesNameDependencies[] = $path;
    }
    
    public function addDependencies(array $dependencies){
        $generated = $this->generateDependencies($dependencies);
        $this->_dependencies = array_merge($this->_dependencies, $generated['dependencies']);
        $this->_filesDependencies = array_merge($this->_filesDependencies, $generated['files']);
    }
    
    public function generateDependencies(array $dependencies){
        $gen = array('files'=>array(),'dependencies'=>array());
        foreach ($dependencies as $dependency) {
            if(isset($dependency['name'])){
                $gen['dependencies'][] = $dependency['name'];
            }
            if(isset($dependency['resource'])){            
                $gen['files'][] = $this->generateFileDependency($dependency['resource']);
            }
        }
        return $gen;
    }
    
    protected function generateFileDependency($path){
        if(in_array($path,$this->_filesNameDependencies)){
            return;
        }
        $this->_filesNameDependencies[] = $path;
        
        return $this->generateFile($path);
    }
    
    public function getFilesDependencies(){
        return $this->_filesDependencies;
    }
    
    public function getDependencies(){
        return $this->_dependencies;
    }
    
}

