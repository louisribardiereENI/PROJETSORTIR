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
                'label_attr' => ['class' => 'form-label'],
                'label' => 'Nom de la sortie :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, array(
                'widget' => 'single_text',
                'model_timezone' => 'Europe/Madrid',
                'view_timezone' => 'Europe/Madrid',
                'attr' => array('placeholder' => 'yyyy-mm-dd hh:mi:ss', 'class' => 'form-control'),
                'label' => 'Date du début de la sortie :',
                'label_attr' => ['class' => 'form-label'])
            )
            ->add('duree', IntegerType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Durée :',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [new Positive()],
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control',
                ]])
            ->add('dateLimiteInscription', DateTimeType::class ,array(
                'widget' => 'single_text',
                'model_timezone' => 'Europe/Madrid',
                'view_timezone' => 'Europe/Madrid',
                'attr' => array('placeholder' => 'yyyy-mm-dd hh:mi:ss', 'class' => 'form-control'),
                'label' => "Date limite d'inscription :",
                'label_attr' => ['class' => 'form-label',
                ]))
            ->add('nbInscriptionsMax', IntegerType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Nombre maximum de participants :',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [new Positive()],
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control',
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'label_attr' => ['class' => 'form-label'],
                'label' => 'Infos sur la sortie :',
            ])
            ->add('photoSortie',FileType::class,[
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[new File(['maxSize'=>'10240k','mimeTypes'=>['image/*'],'mimeTypesMessage'=>'Enregistre une photo valide'])],
                'attr' => ['class' => 'form-control'],
                'label' => 'Photo de la sortie :',
                'label_attr' => ['class' => 'form-label'],

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
                'label_attr' => ['class' => 'form-label'],
                'label' => 'Nom du lieu :',
            ])
            ->add('confirmer', SubmitType::class, [
                'label' => 'Créer la sortie',
                'attr' => ['class' => 'btn btn-primary primary-button'],
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
