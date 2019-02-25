<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-02-24
 * Time: 19:58
 */

namespace App\Form;

use App\Entity\Motd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MotdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label'=>'Title',
                'required' => true,
                'trim' => true
            ])
            ->add('message', null, [
                'attr' => [
                    'rows' => '10',
                    'style' => 'resize: vertical;',
                    'placeholder' => 'Write your content, use Markdown to format your message.'
                ],
                'label' => 'Message Content',
                'help' => 'Use Markdown to format your content',
                'trim' => true
            ])
            ->add('showFlag', CheckboxType::class, [
                'label' => 'Mark this MOTD as visible',
                'required' => false,
            ])
            ->add('showLogin', CheckboxType::class, [
                'label' => 'Mark this MOTD as visible on the login screen',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => Motd::class
        ]);
    }

}