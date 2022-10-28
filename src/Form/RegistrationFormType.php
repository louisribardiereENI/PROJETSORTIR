<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-form'],
                'label' => 'Email',
            ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-form'],
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-form'],
                'label' => 'Prénom',
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-form'],
                'label' => 'Téléphone'
            ])
            ->add('administrateur', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'label' => 'Administrateur',
            ])
            ->add('actif', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'label' => 'Activation du compte'
            ])
            ->add('pseudo', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-form'],
                'label' => 'Pseudo'
            ])
            ->add('idCampus',EntityType::class,[
                'class'=>Campus::class,'choice_value'=>'id','choice_label'=>'nom',
                'label' => 'Campus',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('photoParticipant',FileType::class,[
                'label'=>'Photo de profil',
                'mapped'=>false,
                'required'=>false,
                'attr' => ['class' => 'form-control'],
                'constraints'=>[new File(['maxSize'=>'10240k','mimeTypes'=>['image/*'],'mimeTypesMessage'=>'Enregistre une photo valide'])]

            ])
            ->add('plainPassword', PasswordType::class, [

                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password','class' => 'form-control'],
                'label' => 'Mot de passe',
                'label_attr' => ['class' => 'label-form'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe ne doit pas dépasser la limite de  {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'label' => "Conditions d'utilisations",
                'constraints' => [
                    new IsTrue([
                        'message' => "Veuillez accepter les termes d'utilisation.",
                    ]),
                ],
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
