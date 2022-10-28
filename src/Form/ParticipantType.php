<?php

namespace App\Form;

use App\Entity\Participant;
use SebastianBergmann\CodeCoverage\Report\Text;
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
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('pseudo', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mdp',TextType::class,
                array("mapped"=>false,
                    'attr' => ['class' => 'form-control'],
                    ))
            ->add('mdp2',TextType::class,
                array("mapped"=>false,
                    'attr' => ['class' => 'form-control'],
                    ))
            ->add('photoParticipant',FileType::class,[
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[new File(['maxSize'=>'10240k','mimeTypes'=>['image/*'],'mimeTypesMessage'=>'Enregistre une photo valide'])],
                'attr' => ['class' => 'form-control'],

            ])
            ->add('submit',SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary primary-button centrer'],
                'label' => 'Valider',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
