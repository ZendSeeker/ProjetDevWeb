<?php

namespace DEVWEB\ColocationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('title',     TextType::class)
      ->add('content',   TextareaType::class)
      ->add('adress',    TextType::class)
      ->add('nbPlaces',  IntegerType::class)
      ->add('type',      ChoiceType::class, array('choices'  => array('Appartement' => 'Appartement','Maison' => 'Maison')))
      ->add('save',      SubmitType::class);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'DEVWEB\ColocationBundle\Entity\Advert'
    ));
  }
}
