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
        $product = $event->getData();
        $form = $event->getForm();
        $fields = array_keys($product);
        foreach ($fields as $field) {
            if(!$form->has($field)){
                $form->add($field, 'text',array(
                    'mapped'=>false
                ));
            }                              
        }
    }
}