<?php

namespace Skimia\AngularBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MultiSelectFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SUBMIT => 'preSubmit');
    }

    public function preSubmit(FormEvent $event)
    {
        $product = $event->getData();
        if(!isset($product)){
            return;
        }
        $ids = array();
        foreach ($product as $data) {
            $ids[] = $data['id'];                                  
        }
        $event->setData($ids);

    }
}