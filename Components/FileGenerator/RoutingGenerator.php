<?php


namespace Skimia\AngularBundle\Components\FileGenerator;

class RoutingGenerator extends FileGenerator {

    protected function gen($options) {
        $options = array_merge($options, array(
            'routes' => $this->getRoutes()
        ));
        
        return $this->generateFile('SkimiaAngularBundle:Javascript:states.js.twig', $options);
    }
    
    private function getRoutes(){
        $routes = $this->_container->get('skimia_angular.routing.router')->getRoutes();
        foreach ($routes as $key => $route) {

            unset($route['name']);
            $routes[$key] = $route;
        }
        return $routes;
    }
}
