<?php

namespace Puzzle\Admin\NewsletterBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 */
class AbstractTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'newsletter.template.name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('eventName', ChoiceType::class, array(
                'translation_domain' => 'admin',
                'label' => 'newsletter.template.eventName',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'select'],
                'choices' => [
                    'newsletter.trigger.one' => 'newsletter.trigger.one'
                ]
            ))
            ->add('content', TextareaType::class, [
                'translation_domain' => 'admin',
                'label' => 'newsletter.template.content',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control summernote'],
            ])
        ;
    }
}