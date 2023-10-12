<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Task;
use App\Validator\TimeRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('timeStart', DateTimeType::class, [
                'constraints' => [
                    new TimeRange(),
                ],
                'widget' => 'single_text',
            ])
            ->add('timeFinish', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
            ])
            ->add('isCompleted', CheckboxType::class, [
                'required' => false,
            ])
            ->add('inProgress', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
