<?php

namespace Skimia\AngularBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('angular:generate')
                ->setDescription('Generer les vues les controlleurs et services')
                ->addArgument('prefix', InputArgument::OPTIONAL, 'Prefixe des routes au format x.y.z')
        ;
    }

    protected $_input;
    protected $_output;

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->_input = $input;
        $this->_output = $output;
        $bundles = $this->getContainer()->get('skimia_angular.bundle_manager')->getBundles();
        foreach ($bundles as $bundle) {
            $this->generateBundle($bundle);
        }
    }

    protected function generateBundle(\Skimia\AngularBundle\Components\BundleManager\Bundle $bundle) {
        global $kernel;
        $bundle_name = $bundle->getName();
        $this->_output->writeln('Bundle : ' . $bundle_name);
        $path = $kernel->getBundle($bundle_name)->getPath() . DIRECTORY_SEPARATOR . 'Controller';
        $namespace = $kernel->getBundle($bundle_name)->getNamespace() . '\\' . 'Controller';
        $controllers = $this->getDirectory($path);
        foreach ($controllers as $controller) {
            $nsp = $namespace . '\\' . basename($controller, '.php');
            $class = new $nsp();
            if (is_a($class, '\FOS\RestBundle\Controller\FOSRestController')) {
                $this->generateController($bundle, $class);
            }
        }
    }

    protected function generateController(\Skimia\AngularBundle\Components\BundleManager\Bundle $bundle, \FOS\RestBundle\Controller\FOSRestController $controller) {
        $name = str_replace('Controller', '', $this->get_real_class($controller));
        $this->_output->writeln(' Controller : ' . $name);
        $directory = $bundle->getResourcePath();

        //Check controller
        if (!file_exists($directory . 'controllers' . DIRECTORY_SEPARATOR . $name . 'Controller.js.twig')) {
            $this->_output->writeln('   > Generate Controller ' . $name . 'Controller');
            $this->generateControllerJs($name, $directory . 'controllers' . DIRECTORY_SEPARATOR . $name . 'Controller.js.twig');
        }
        //Check service
        if (!file_exists($directory . 'services' . DIRECTORY_SEPARATOR . $name . 'Factory.js.twig')) {
            $this->_output->writeln('   > Generate Service ' . $name . 'Factory');
            $this->generateFactoryJs($name, $directory . 'services' . DIRECTORY_SEPARATOR . $name . 'Factory.js.twig');
        }
        //Check Partials
        if (!file_exists($directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'form.html.twig')) {
            $this->_output->writeln('   > Generate Partial ' . $name . '/form.html');
            $this->generateViewForm($bundle->getName(), $name, $directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'form.html.twig');
        }
        if (!file_exists($directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'list.html.twig')) {
            $this->_output->writeln('   > Generate Partial ' . $name . '/list.html');
            $this->generateViewShow($bundle->getName(), $name, $directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'list.html.twig');
        }
        if (!file_exists($directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'show.html.twig')) {
            $this->_output->writeln('   > Generate Partial ' . $name . '/show.html');
            $this->generateViewList($bundle->getName(), $name, $directory . 'partials' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'show.html.twig');
        }
        //Check Routing
    }

    protected function generateControllerJs($name, $path) {
        $file = $this->getContainer()->get('templating')->render('SkimiaAngularBundle:Files:controllers.js.twig.twig', array(
            'name' => $name
                )
        );
        file_put_contents($path, $file);
    }

    protected function generateFactoryJs($name, $path) {
        $file = $this->getContainer()->get('templating')->render('SkimiaAngularBundle:Files:factory.js.twig.twig', array(
            'name' => $name
                )
        );
        file_put_contents($path, $file);
    }

    protected function generateViewForm($bundle_name, $name, $path) {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), '0777');
        }
        $file = $this->getContainer()->get('templating')->render('SkimiaAngularBundle:Files/Views:form.html.twig.twig', array(
            'name' => $name,
            'bundle_name' => $bundle_name
                )
        );
        file_put_contents($path, $file);
    }

    protected function generateViewList($bundle_name, $name, $path) {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), '0777');
        }
        $file = $this->getContainer()->get('templating')->render('SkimiaAngularBundle:Files/Views:list.html.twig.twig', array(
            'name' => $name,
            'bundle_name' => $bundle_name
                )
        );
        file_put_contents($path, $file);
    }

    protected function generateViewShow($bundle_name, $name, $path) {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), '0777');
        }
        $file = $this->getContainer()->get('templating')->render('SkimiaAngularBundle:Files/Views:show.html.twig.twig', array(
            'name' => $name,
            'bundle_name' => $bundle_name
                )
        );
        file_put_contents($path, $file);
    }

    protected function get_real_class($obj) {
        $classname = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    protected function getDirectory($path = '.', $prefix = '') {

        $ignore = array('cgi-bin', '.', '..', '.svn');
        // Directories to ignore when listing output. Many hosts 
        // will deny PHP access to the cgi-bin. 

        $files = array();
        $dh = @opendir($path);
        // Open the directory to the handle $dh 

        while (false !== ( $file = readdir($dh) )) {
            // Loop through the directory 

            if (!in_array($file, $ignore)) {
                // Check that this file is not to be ignored 
                // Just to add spacing to the list, to better 
                // show the directory tree. 

                if (is_dir("$path/$file")) {
                    // Its a directory, so we need to keep reading down... 

                    $files = array_merge($files, $this->getDirectory("$path/$file", $prefix . $file . '/'));
                    // Re-call this same function but on a new directory. 
                    // this is what makes function recursive. 
                } else {
                    $files[] = $prefix . $file;
                    // Just print out the filename 
                }
            }
        }

        closedir($dh);
        // Close the directory handle 
        return $files;
    }

}
