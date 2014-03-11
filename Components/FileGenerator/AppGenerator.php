<?php

namespace Skimia\AngularBundle\Components\FileGenerator;

class AppGenerator extends FileGenerator {

    protected function gen($options) {
        $options = array_merge($options, array(
            'files' => $this->_depsManager->getFilesDependencies(),
            'deps' => $this->_depsManager->getDependencies()
        ));
        return $this->generateFile('SkimiaAngularBundle:JavaScript:app.js.twig', $options);
    }

    protected function init() {
        $this->_depsManager->addDependencies(array(
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular.min.js'),
            array('name'=>'ngRoute', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-route.min.js'),
            array('resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-resource.min.js'),
            array('name' => 'ui.router', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-ui-router.min.js'),
            array('name' => 'angular-loading-bar', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-loading-bar.min.js'),
            array('name'=> 'ngAnimate', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-animate.min.js'),
            array('name'=> 'message-center', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-flash.js'),
            array('name'=> 'multi-select', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/angular-multi-select.js'),
            array('name'=> 'skimia-auth', 'resource' => '@SkimiaAngularBundle/Resources/public/js/lib/skimia-auth.js'),
            
            ));

        $this->addConfigDependencies();
    }

    protected function addConfigDependencies() {
        $config = $this->_container->getParameter('skimia_angular.global_config');
        $this->_depsManager->addDependencies($config['dependencies']);
    }

}
