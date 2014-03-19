<?php

namespace Skimia\AngularBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SUBMIT => 'preSubmit');
    }

    public function preSubmit(FormEvent $event)
    {
        global $kernel;
        $word = $kernel->getContainer()->get('spm.twig.word_transformer_extension');
        $product = $event->getData();
        $form = $event->getForm();

        $fields = array_keys($product);
        foreach ($fields as $field) {
            if(!$form->has($field)){
                if(is_array($product[$field])&& isset($product[$field]['id'])){
                    $product[$word->variablizeFilter($field)] = $product[$field]['id'];
                }elseif(!$form->has($word->variablizeFilter($field))){
                    $form->add($field, 'text',array(
                        'mapped'=>false
                    ));
                }
                else{
                    $product[$word->variablizeFilter($field)] = $product[$field];
                
                }
                unset($product[$field]);
            }                              
        }
        $event->setData($product);
    }
}