<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('pseudo')
            ->add('mdp',TextType::class,array("mapped"=>false,))
            ->add('mdp2',TextType::class,array("mapped"=>false,))
            ->add('photoParticipant',FileType::class,[
                'label'=>'Photo de profil',
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[new File(['maxSize'=>'10240k','mimeTypes'=>['image/*'],'mimeTypesMessage'=>'Enregistre une photo valide'])]

            ])
            ->add('submit',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
