<?php

namespace Skimia\AngularBundle\Components\TwigServices;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Inflector\Inflector;
class HtmlHelpers extends \Twig_Extension{
    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    function __construct($container)
    {
        $this->container = $container;
    }
    
    
    public function getFunctions() {
        return array(
            'app_name' => new \Twig_Function_Method($this, 'appName'),
            'module_name'=> new \Twig_Function_Method($this,'moduleName'),
            'plurialise'=> new \Twig_Function_Method($this,'plurialise'),
            'adapt_angular'=> new \Twig_Function_Method($this,'adaptName',array(
                'is_safe' => array('html')
            )),
            'app_javascript'=> new \Twig_Function_Method($this,'appJavascript',array(
                'is_safe' => array('html')
            )),
            'get_form' => new \Twig_Function_Method($this, 'getForm',array(
                'is_safe' => array('html')
            )),
            'render_form' => new \Twig_Function_Method($this, 'renderForm',array(
                'is_safe' => array('html')
            )),
        );
    }
    
    
    
    
    
    /******************************\
     * Functions
    \******************************/
    
    public function appName(){
        return $this->container->getParameter('skimia_angular.global_config')['app_name'];
    }
    
    public function appJavascript(){
    	if(in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'))) {
  			$url = $this->container->get('router')->generate('skimia_angular_get_app');
		}else{
			$url ="app.js";
		}
		return '<script type="text/javascript" src="'.$url.'" ></script>';
    }
    public function moduleName($bundleName,$module,$concat = false){
        $bundleManager = $this->container->get('skimia_angular.bundle_manager');
        if($bundleManager->hasBundle($bundleName)){
            $bundle = $bundleManager->getBundle($bundleName);
            if($concat){
                return $this->appName().'_'.$bundle->getShortName().'_'.ucfirst($module);
            }
            return $this->appName().'.'.$bundle->getShortName().'.'.ucfirst($module);
        }
        return $this->container->getParameter('skimia_angular.global_config')['app_name'];
    }
    
    public function renderForm($bundle,$formType,$entity=true){

        return $this->container->get('templating')->render('SkimiaAngularBundle:Form:angularForm.html.twig',array(
            'form' => $this->getForm($bundle, $formType,$entity)
        ));
    }
    
    public function getForm($bundle,$formType,$entity=true){
                global $kernel;
        
        $form_class =  $kernel->getBundle($bundle)->getNamespace().'\\Form\\'.$formType.'Type';
        if($entity)
        {
            $entity_class =  $kernel->getBundle($bundle)->getNamespace().'\\Entity\\'.$formType;
            $form =  $this->container->get('form.factory')->create(new $form_class(), new $entity_class(), array());
        }  else {
            $form =  $this->container->get('form.factory')->create(new $form_class());
        }
        $form->add('save', 'submit');
        return $form->createView();
    }
    public function  plurialise($name){
        return \Doctrine\Common\Util\Inflector::pluralize($name);
    }
    
    public function adaptName($form_field){
        if(strpos($form_field, '[')!==false)
        {
            $field = explode('[', $form_field, 2);
            $dt = 'data[\''.$field[0].'\']'.'['.$field[1];
            $dt.= '" ng-init="'.'data[\''.$field[0].'\']'.' = {}';
            return $dt;
        }
        return 'data[\''.$form_field.'\']';
    }
    public function getName()
    {
        return 'angular_html_helper';
    }
}
