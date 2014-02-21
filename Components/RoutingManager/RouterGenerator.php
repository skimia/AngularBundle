<?php

namespace Skimia\AngularBundle\Components\RoutingManager;

class RouterGenerator {

    protected $_config_routes;
    protected $_routes;
    protected $_initialised = false;

    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    function __construct($container) {
        $this->container = $container;
    }

    protected function init() {
        if ($this->_initialised) {
            return;
        }
        $this->_config_routes = $this->container->getParameter('skimia_angular.config_routes');
        if ($this->container->hasParameter('skimia_angular.routes')) {
            $this->_routes = $this->container->getParameter('skimia_angular.routes');
        } else {
            $this->_routes = $this->generateRoutes();
        }
        $this->_initialised = true;
    }

    protected function generateRoutes() {
        $routes = array();
        foreach ($this->_config_routes as $config_route) {

            $route = $this->generateRoute($config_route);
            $routes[$route['name']] = $this->cleanUnusedIndexes($route);
        }

        return array_merge_recursive($routes, $this->addOrphelinStates($routes));
    }

    protected function cleanUnusedIndexes(array $route) {
        unset($route['view']);
        unset($route['pattern']);
        unset($route['prefix']);
        return $route;
    }

    protected function generateRoute(array $config_route) {
        $route = array();
        $route['name'] = $this->generateStateName($config_route);
        if(isset($config_route['pattern'])){
            $route['url'] = $this->generateStateUrl($config_route);
        }
        if(isset($config_route['view'])){
            $route['templateUrl'] = $this->container->get('templating.helper.assets')->getUrl($this->generatePartialPath($config_route['view']));
        }
        if(isset($config_route['controller'])){
            $route['controller'] = $config_route['controller'];
        }
        return array_merge($config_route, $route);
    }

    protected function generateStateName(array $conf) {
        $name = str_replace('_', '.', $conf['name']);
        return $name;
    }

    protected function generateStateUrl(array $config_route) {
        if ($this->isRootRoute($config_route['name'])) {
            if ($this->endsWith($config_route['prefix'], '/') && $this->startsWith($config_route['pattern'], '/')) {
                return $config_route['prefix'] . substr($config_route['pattern'], 1);
            } else {
                return $config_route['prefix'] . $config_route['pattern'];
            }
        } else {
            return $config_route['pattern'];
        }
    }

    protected function addOrphelinStates(array $routes){
        $new_states = array();
        foreach (array_keys($routes) as $value) {
            
            $orphelins = $this->getOrphelin($routes,$value);
            foreach ($orphelins as $orphelin) {
                $new_states[$orphelin] = $this->getNewState();
            }
            
        }
        return $new_states;
    }
    protected function getNewState(){
        return array(
            'templateUrl'=>$this->container->get('templating.helper.assets')->getUrl('blank.html'),
        );
    }
    protected function getOrphelin(array $routes, $name){
        $states = array();
        $states_tree = explode('.', $name);
        if(count($states_tree)> 1){
            $currentState = '';
            for($i = 0;$i<count($states_tree);$i++){
                $currentState .= ($i != 0 ?'.':'').$states_tree[$i];
                if(!isset($routes[$currentState])){
                    $states[] = $currentState;
                }
            }
            return $states;
        }
        return array();
    }
    protected function isRootRoute($name) {

        $tree_c = $this->upTree($name);
        while ($tree_c !== false) {
            if (isset($this->_config_routes[$tree_c])) {
                return false;
            }
            $tree_c = $this->upTree($tree_c);
        }
        return true;
    }

    protected function upTree($name) {
        $names = split('_', $name);
        if (count($names) <= 1) {
            return false;
        }
        unset($names[count($names) - 1]);
        return implode('_', $names);
    }

    protected function generatePartialPath($name) {
        if(!$this->startsWith($name,'@')){
            return $name;
        }
        $path_arr = split(':', str_replace('@', '', $name));
        $bundle_name = $path_arr[0];
        unset($path_arr[0]);
        $path = str_replace(array('.html.twig', '.html'), '', implode('/', $path_arr));

        $bundle = $this->container->get('skimia_angular.bundle_manager')->getBundle($bundle_name);

        return $this->container->get('router')->generate('skimia_angular_get_partial', array(
                    'bundle' => $bundle->getShortName(),
                    'path' => $path
        ));
    }

    protected function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    protected function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    public function getRoutes() {
        $this->init();
        return $this->_routes;
    }

}
