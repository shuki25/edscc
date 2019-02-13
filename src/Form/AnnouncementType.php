<?php

namespace App\Form;

use App\Entity\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
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
            ->add('publishAt', DateTimeType::class, [
                'label' => 'Publish At',
                'date_widget' => 'single_text',
                'html5' => true,
                'help' => 'The date that the announcement will automatically appear in the feed'
            ])
            ->add('publishedFlag', CheckboxType::class, [
                'label' => 'Mark this post as published (will be visible in announcements after the published date)',
                'required' => false,
            ])
            ->add('pinnedFlag', CheckboxType::class, [
                'label' => 'This post is pinned (always will appear on the top of page)',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
}
