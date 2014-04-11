<?php

namespace Skimia\AngularBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MultiSelectType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new \Skimia\AngularBundle\Form\EventListener\MultiSelectFormSubscriber();

        // ajoute un champ texte normal, mais y ajoute aussi votre convertisseur
        //$builder->addEventSubscriber($transformer,999);
    }
    public function buildView(FormView $view, FormInterface $form, array $options){
        $view->vars['property'] = $options['property'];
        $view->vars['hide'] = $options['hide'];
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'multiple' => true,
                'expanded' => false,
                'hide'=>false
        ));
    }
    public function getParent() {
        return 'entity';
    }

    public function getName()
    {
        return 'multiselect';
    }
}