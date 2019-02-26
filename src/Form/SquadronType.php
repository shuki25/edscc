<?php

namespace App\Form;

use App\Entity\Squadron;
use App\Entity\User;
use App\Form\DataTransformer\BooleanToYNTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SquadronType extends AbstractType
{

    /**
     * @var BooleanToYNTransformer
     */
    private $transformer;

    public function __construct(BooleanToYNTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Name of your Squadron',
                'help' => 'Exact spelling as depicted in the game',
                'required' => true
            ])
            ->add('idCode', null, [
                'label' => 'Squadron ID',
                'help' => 'A 4-character indicator for your Squadron in the game',
                'required' => true,
                'attr' => [
                    'maxlength' => 4
                ]
            ])
            ->add('platform', null, [
                'label' => 'Gaming Platform',
                'help' => 'Choose the gaming platform this Squadron plays on'
            ])
            ->add('faction', null, [
                'label' => 'Faction Affiliation',
                'placeholder' => 'Choose a Faction',
            ])
            ->add('power', null, [
                'label' => 'Superpower Affiliation',
                'placeholder' => 'None',
                'help' => 'Choose a Superpower affiliation if applicable',
            ])
            ->add('home_base')
            ->add('description', null, [
                'attr' => [
                    'rows' => '3',
                    'style' => 'resize: vertical;',
                    'help' => 'Write a brief description about your Squadron. New members will see this when picking which squadron to join.'
                ],
            ])
            ->add('welcome_message', null, [
                'attr' => [
                    'rows' => '10',
                    'style' => 'resize: vertical;'
                ],
                'help' => 'The welcome message will be shown to new member after joining the squadron. Use Markdown to format your message.'
            ])
            ->add('RequireApproval', CheckboxType::class, [
                'label' => 'Requires approval from squad leaders to join',
                'required' => false
            ]);
//            ->add('admin', EntityType::class, [
//                'label' => 'ED:SCC Squadron Owner',
//                'class' => User::class,
//                'help' => 'This is the account holder who manages this Squadron. If you change to another user, you will lose privileges.',
//                'disabled' => 'disabled'
//            ]);

        $builder->get('RequireApproval')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Squadron::class,
        ]);
    }
}
