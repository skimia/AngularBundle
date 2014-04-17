<?php

namespace Skimia\AngularBundle\Components\FileGenerator;
use Doctrine\Common\Inflector\Inflector;
class AppGenerator extends FileGenerator {

    protected function gen($options) {

        $options = array_merge($options, array(
            'files' => $this->_depsManager->getFilesDependencies(),
            'deps' => $this->_depsManager->getDependencies(),
            'relations' => $this->generateFile('SkimiaAngularBundle:Javascript:relations.js.twig',array('relations'=>json_encode($this->getRelations(array())))),
            'forms' => $this->generateFile('SkimiaAngularBundle:Javascript:forms.js.twig',array('forms'=>json_encode($this->getForms()))),
            'translations'  => $this->generateFile('SkimiaAngularBundle:Javascript:trans_catalogue.js.twig',array('catalogue'=>$this->_container->get('angular_translator_generator')->getCatalogue()))
        ));
        return $this->generateFile('SkimiaAngularBundle:Javascript:app.js.twig', $options);
    }

    protected function init() {
        $this->_depsManager->addDependencies(array(
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular.min.js'),
            array('name'=>'ngRoute', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-route.min.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-resource.min.js'),
            array('name' => 'ui.router', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-ui-router.min.js'),
            array('name' => 'angular-loading-bar', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-loading-bar.min.js'),
            array('name'=> 'ngAnimate', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-animate.min.js'),
            array('name'=> 'ngSanitize', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-sanitize.min.js'),
            array('name'=> 'message-center', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-flash.js'),
            array('name'=> 'multi-select', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-multi-select.js'),
            array('name'=> 'ui.gravatar', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-gravatar.js'),
            array('name'=> 'angular-md5', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-md5.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/prettify/prettify.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/showdown.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/showdown-modules/github.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/showdown-modules/prettify.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/showdown-modules/twitter.js'),
            array('name'=> 'angular-markdown', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-markdown.js'),
            array('name'=> 'angular-repo', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-repo.js'),
            array('name'=> 'pascalprecht.translate','resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-translate.min.js')
            
            ));

        $this->addConfigDependencies();
    }

    protected function addConfigDependencies() {
        $config = $this->_container->getParameter('skimia_angular.global_config');
        $this->_depsManager->addDependencies($config['dependencies']);
    }

    protected function getRelations($relations){
        $em = $this->_container->get('doctrine')->getManager();
        $cmf = $em->getMetadataFactory();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $namespaces = $em->getConfiguration()->getEntityNamespaces();
        $entities = array();
        foreach ($meta as $m) {
            $str = str_replace(array_values($namespaces),array_keys($namespaces),$m->getName());
            if(substr_count($str,'\\') == 1){
                $entities[] = str_replace('\\',':',$str);
            }
        }
        foreach ($entities as  $entity) {

            $class = $cmf->getMetadataFor($entity);
            foreach ($class->associationMappings as $relation) {
                if(isset($relation['inversedBy'])){
                    $entity_a_name = $relation['sourceEntity'];
                    $entity_b_name = $relation['targetEntity'];
                    if(isset($entity_a_name::$__type) && isset($entity_b_name::$__type)){

                        $entity_a = $entity_a_name::$__type;
                        $field_a = Inflector::tableize($relation['fieldName']);
                        $entity_b = $entity_b_name::$__type;
                        $field_b = Inflector::tableize($relation['inversedBy']);

                        switch($relation['type']){
                            case 8:
                            $type = 'MM';
                            break;
                            case 2:
                            if($relation['isOwningSide'])
                                $type = 'MO';
                            else
                                $type = 'OM';

                            break;
                        }
                        $insert = true;
                        foreach ($relations as  $r) {
                            if(
                                $r['entity_a'] == $entity_a && 
                                $r['field_a']  == $field_a && 
                                $r['entity_b'] == $entity_b && 
                                $r['field_b']  == $field_b
                                )
                                $insert = false;
                            if(
                                $r['entity_b'] == $entity_a &&
                                $r['entity_a'] == $entity_b &&
                                $r['field_b'] == $field_a &&
                                $r['field_a'] == $field_b
                                )
                                $insert = false;

                        }
                        if($insert){
                            $relations[] = array(
                                'entity_a'=> $entity_a,
                                'field_a'=> $field_a,

                                'entity_b'=> $entity_b,
                                'field_b'=> $field_b,

                                'type'=> $type,
                                );
                        }
                    }
                    
                    //debug($relation);
                }
            }
        }
        return $relations;
        
    }

    protected function getForms(){
        $kernel = $this->_container->get('kernel');
        $bundles = $this->_container->get('skimia_angular.bundle_manager')->getBundles();
        $forms = array();
        foreach ($bundles as $bundle) {

            $path = $kernel->locateResource('@'.$bundle->getName());
            $files = $this->getDirectory($path.DIRECTORY_SEPARATOR.'Form'.DIRECTORY_SEPARATOR);
            foreach ($files as $file) {
                $name = str_replace(array('.php',$path), array('',''), $file);
                $form_class = $bundle->getNamespace().'\\Form\\'.$name;

                if(file_exists($path.DIRECTORY_SEPARATOR.'Entity'.DIRECTORY_SEPARATOR.str_replace('Type', '.php', $name))){
                    $form = $this->_container->get('form.factory')->create(new $form_class());
    
                    $class =  $form->getConfig()->getDataClass();
                    if(isset($class) && isset($class::$__type)){
                        $key = $class::$__type;
                        $forms[$key] = array();
                        foreach ($form->all() as $field) {
                            //die();
                            //$field->getName() to underscore
                            /*if($field->getName() == 'options_')
                            debug($field->getConfig());
                            die();*/
                            $forms[$key][$field->getName()] = $field->getConfig()->getType()->getName();
                        }
                    }
                }
            }
        }
        return $forms;

    }

    function decamelize($word) {
  return preg_replace(
    '/(^|[a-z])([A-Z])/e', 
    'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")',
    $word 
  ); 
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
