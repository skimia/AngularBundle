<?php

namespace Skimia\AngularBundle\Components\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Finder\Finder;

class AngularTranslatorGenerator{

	protected $_container = null;

	public function __construct($container){
		$this->_container = $container;
	}

	public function getCatalogue(){
		return $this->loadCatalogue();
	}

	protected function loadCatalogue(){
        $dir = $this->getMessagesDir();

        $finder = new Finder();
		$finder->files()->in($dir)->name('*.php');

		$catalogues = array();
		foreach ($finder as $file) {
			$filename = basename($file->getRealpath(), ".php");
			$locale = explode('.',$filename)[1];
			$this->_container->get('translator')->trans("security.login.submit");
			$path = $file->getRealpath();
			$catalogues = array_merge($catalogues, $this->getMessagesFromCatalogue(require($path)));
		}
		return $catalogues;
	}

	protected function getMessagesFromCatalogue($catalogue){
		$ref = new \ReflectionClass($catalogue);
        $prop_messages = $ref->getProperty('messages');
        $prop_messages->setAccessible(true);
        $messages_list = $prop_messages->getValue($catalogue);

        $prop_locale = $ref->getProperty('locale');
        $prop_locale->setAccessible(true);
        $locale = $prop_locale->getValue($catalogue);

        $prop_fallback = $ref->getProperty('fallbackCatalogue');
        $prop_fallback->setAccessible(true);
        $fallbackCatalogue = $prop_fallback->getValue($catalogue);

        $messages_catalogue = array();
        foreach ($messages_list as $domain => $messages) {
        	if(!isset($messages_catalogue[$locale])) $messages_catalogue[$locale] = array();

        	$messages_catalogue[$locale] = array_merge ( $messages_catalogue[$locale], $messages );
        }
        if(isset($fallbackCatalogue))
			return array_merge($this->getMessagesFromCatalogue($fallbackCatalogue), $messages_catalogue);
		else
			return $messages_catalogue;
	}
	protected function getMessagesDir(){
		return $this->_container->get('kernel')->getRootDir().'/../app/Resources/trs';

	}

}