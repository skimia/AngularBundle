<?php

namespace Skimia\AngularBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class SingleSelectType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
    //$transformer = new \Skimia\AngularBundle\Form\EventListener\MultiSelectFormSubscriber();

    }
    public function buildView(FormView $view, FormInterface $form, array $options){
        global $kernel;

        $entity = $kernel->getContainer()->get('doctrine')->getManager()->getRepository($options['class'])->getClassName( );
        $view->vars['property'] = $options['property'];
        $view->vars['repo_class'] = $entity::$__type;
        $view->vars['hide'] = $options['hide'];
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'multiple' => false,
            'expanded' => false,
            'hide'=>false
            ));
    }
    public function getParent() {
        return 'entity';
    }
    
    public function getName()
    {
        return 'singleselect';
    }
}