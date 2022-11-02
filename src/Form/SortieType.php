<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\EtatRepository;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Positive;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-control'],
                'label' => 'Nom de la sortie :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'date_label' => 'Starts On',
            ])
            ->add('duree', IntegerType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Durée :',
                'constraints' => [new Positive()],
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control',
                ]])
            ->add('dateLimiteInscription')
            ->add('nbInscriptionsMax', IntegerType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Nombre maximum de participants :',
                'constraints' => [new Positive()],
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control',
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-control'],
                'label' => 'Infos sur le sortie :',
            ])
            ->add('photoSortie',FileType::class,[
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[new File(['maxSize'=>'10240k','mimeTypes'=>['image/*'],'mimeTypesMessage'=>'Enregistre une photo valide'])],
                'attr' => ['class' => 'form-control'],
                'label' => 'Photo de la sortie :',

            ])
//            ->add('idEtat',  EntityType::class, [
//                'class'=>Etat::class,'choice_value'=>'id','choice_label'=>'libelle',
//                'attr' => ['class' => 'form-control'],
//                'label_attr' => ['class' => 'label-control'],
//                'label' => 'État de la sortie :',
//            ])
            ->add('nomLieu', TextType::class, [
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'label-control'],
                'label' => 'Nom du lieu :',
            ])
            ->add('confirmer', SubmitType::class, [
                'label' => 'Confirmer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
