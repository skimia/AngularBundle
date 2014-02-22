<?php

namespace Skimia\AngularBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Parser;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SkimiaAngularExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //chargement de la configuration globale
        $configuration = new GlobalConfiguration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('skimia_angular.global_config', $config);
        // chargement de la configuration de chaque bundle
        $this->loadBundle($config, $container);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('skimia_angular.config_routes',$this->loadRouting());
    }
    
    public function loadBundle(array $verifiedConfigs, ContainerBuilder $container)
    {
        global $kernel;
        $yaml = new Parser();
        $configuration = new BundleConfiguration();
        $configs = array();

        foreach ($verifiedConfigs['bundles'] as $key => $value) {
            //localiser le fichier
            $path = $kernel->locateResource($value['resource']);
            // parser
            $value = $yaml->parse(file_get_contents($path));
            //verifier
            $config = $this->processConfiguration($configuration, $value);

            $configs[$key] = $config;
        }
        
        $container->setParameter('skimia_angular.bundle_config', $configs);
    }
    
    public function loadRouting()
    {
        global $kernel;
        $path = $kernel->getRootDir().'/config/angular_routing.yml';
        $yaml = new Parser();
        $value = $yaml->parse(file_get_contents($path));
        $config_routes = array();
        foreach ($value as $key=>$routed) {
            $path = $kernel->locateResource($routed['resource']);
            //foreach all files
            $files = $this->getDirectory($path,$path.DIRECTORY_SEPARATOR);
            foreach ($files as $conf) {
                $routes = $yaml->parse(file_get_contents($conf));
                foreach ($routes as $name=>$route) {
                    if(isset($routed['prefix'])){
                        $route['prefix'] = $routed['prefix'];
                    }
                    $route['name'] = $name;
                    if(isset($config_routes[$name])){
                        throw new \Exception('Duplicate entry in routing Angular for key'.$name);
                    }
                    $config_routes[$name] = $route;
                }
            }
            
        }
        return  $config_routes;
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
