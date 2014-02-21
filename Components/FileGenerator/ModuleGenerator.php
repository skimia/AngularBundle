<?php

namespace Skimia\AngularBundle\Components\FileGenerator;

use Doctrine\Common\Inflector\Inflector;

class ModuleGenerator extends FileGenerator {

    protected function gen($options) {
        $dependencies = $this->generateDependencies($options['dependencies']);
        $options = array_merge($options, array(
            'deps' => $dependencies['files'],
            'module' => $this->generateModuleJavascript($options, $dependencies['dependencies']),
            'files' => $this->generateModuleFiles($options)
        ));
        return $this->generateFile('SkimiaAngularBundle:JavaScript:module.js.twig', $options);
    }

    protected function generateModuleJavascript($options, $deps) {
        $path = $options['bundle']->getResourcePath('module_view', $options['module_name']);

        return $this->_container->get('templating')->render($path . '/module.js.twig', array(
                    'deps' => $deps,
        ));
    }

    protected function generateDependencies($dependencies) {
        return $this->_depsManager->generateDependencies($dependencies);
    }

    protected function generateModuleFiles($infos) {
        $path = $infos['bundle']->getResourcePath('module',$infos['module_name']);
        $files = array();
        $paths = $this->getDirectory($path);
        foreach ($paths as $fPath) {
            $files[] = $this->generateFileModule($fPath, $infos['bundle'], $infos['module_name']);
        }
        return $files;
    }
    
    protected function generateFileModule($path, $bundle, $module_name){
        $twigPath = $bundle->getResourcePath('module_view',$module_name).'/'.$path;
        $base_name = $bundle->getShortName().'.';
        $base_name.= str_replace(ucfirst(Inflector::singularize($module_name)),
                '',
                str_replace('/', '.', $path));
        return $this->generateFile($twigPath, array(
            'module_var' => $this->_container->get('skimia_angular.twig.html_helpers')->moduleName($bundle->getShortName(),$module_name,true),
            'base_name' => str_replace(array('.js.twig','.js'), '', $base_name)
        ));
    }

    protected function getDirectory( $path = '.',$prefix='' ){ 

    $ignore = array( 'cgi-bin', '.', '..', 'module.js.twig','.svn' ); 
    // Directories to ignore when listing output. Many hosts 
    // will deny PHP access to the cgi-bin. 

    $files = array();
    $dh = @opendir( $path ); 
    // Open the directory to the handle $dh 
    
    while( false !== ( $file = readdir( $dh ) ) ){ 
    // Loop through the directory 
     
        if( !in_array( $file, $ignore ) ){ 
        // Check that this file is not to be ignored 
             
            // Just to add spacing to the list, to better 
            // show the directory tree. 
             
            if( is_dir( "$path/$file" ) ){ 
            // Its a directory, so we need to keep reading down... 
                
                $files = array_merge($files,$this->getDirectory( "$path/$file",$prefix.$file.'/' )); 
                // Re-call this same function but on a new directory. 
                // this is what makes function recursive. 
             
            } else { 
                $files[] = $prefix.$file;
                // Just print out the filename 
             
            } 
         
        } 
     
    } 
     
    closedir( $dh ); 
    // Close the directory handle 
    return $files;
} 
}
