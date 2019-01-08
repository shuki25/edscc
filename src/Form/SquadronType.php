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
            ->add('name',null, array(
                'label' => 'Name of your Squadron',
                'help' => 'Exact spelling as depicted in the game'
            ))
            ->add('idCode', null, array(
                'label' => 'Squadron ID',
                'help' => 'A 4-character indicator for your Squadron in the game'
            ))
            ->add('platform', null, array(
                'label' => 'Gaming Platform',
                'help' => 'Choose the gaming platform this Squadron plays on'
            ))
            ->add('faction', null, array(
                'label' => 'Faction Affiliation',
                'placeholder' => 'Choose a Faction',
            ))
            ->add('power', null, array(
                'label' => 'Superpower Affiliation',
                'placeholder' => 'None',
                'help' => 'Choose a Superpower affiliation if applicable',
            ))
            ->add('home_base')
            ->add('description')
            ->add('welcome_message', null, array (
                'help' => 'The welcome message will be shown to new member after joining the squadron'
            ))
            ->add('RequireApproval', CheckboxType::class, array(
                'label' => 'Requires approval from squad leaders to join',
                'required' => false
            ))
            ->add('admin', EntityType::class, [
                'class' => User::class
            ]);

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
