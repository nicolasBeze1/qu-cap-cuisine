<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class QuestionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.question_title',
            ])
            ->add('answer', null, [
                'label' => 'label.question_answer',
            ])
            ->add('otherAnswer', null, [
                'label' => 'label.question_other_answer',
                'help' => 'help.question_other_answer',
            ])
            ->add('answerHelp', null, [
                'label' => 'label.question_answerHelp',
                'help' => 'help.question_answerHelp',
                'required' => false,
            ])
            ->add('answerDetail', null, [
                'label' => 'label.question_answerDetail',
                'help' => 'help.question_answerDetail',
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'label.question_image',
                'help' => 'help.question_image',
                'required' => false,
            ])
            ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
