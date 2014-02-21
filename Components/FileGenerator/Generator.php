<?php

namespace Skimia\AngularBundle\Components\FileGenerator;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
class Generator{
    /**
     *
     * @var Symfony\Component\HttpKernel\Kernel 
     */
    protected $_kernel;
    
    /**
     *
     * @var Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine 
     */
    protected $_twig;
    
    public function __construct(Kernel $kernel, EngineInterface $twig){
        $this->_kernel = $kernel;
        $this->_twig = $twig;
    }
    
    protected function generateFile($path, $vars=null){
        if($this->endsWith($path,'.twig')){
            return $this->generateTwigFile($path, $vars);
        }
        else{
            return $this->generateSystemFile($path);
        }
    }
    
    private function generateTwigFile($path,$vars=null){
        if($this->startsWith($path,'@')||$this->contains($path,':')){
            return $this->_twig->render($path, 
                    $this->normaliseContext($path,$vars));
        }
        else{
            $twig = new \Twig_Environment(new \Twig_Loader_String());
            return $twig->render(file_get_contents($path),
                    $this->normaliseContext($path,$vars));
        }
    }
    
    private function normaliseContext($path,$vars=null){
        if(!isset($vars)){
            $vars = array();
        }
        return array_merge($vars, array(
            'file' => $path
        ));
    }
    
    private function generateSystemFile($path){
        if($this->startsWith($path,'@')){
            return file_get_contents($this->_kernel->locateResource($path));
        }
        else{
            return file_get_contents($path);
        }
    }
    
    protected function saveFile($file,$path){
        if($this->startsWith($path,'@')||$this->contains($path,':')){
            return file_put_contents($this->_kernel->locateResource($path), $file);
        }
        else{
            return file_put_contents($path, $file);
        }
    }


    protected function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    protected function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
    
    protected function contains($haystack, $needle){
        return strpos($haystack, $needle) !== false;
    }
}