<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Survey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SurveyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.survey_title',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'label' => 'label.survey_category',
                'placeholder' => 'placeholder.survey_category'
            ])
            ->add('difficulty', NumberType::class, [
                'label' => 'label.survey_difficulty',
                'help' => 'help.survey_difficulty',
                'attr' => array(
                    'placeholder' => 1
                )
            ])
            ->add('successPercent', NumberType::class, [
                'label' => 'label.survey_successPercent',
                'help' => 'help.survey_successPercent',
                'attr' => array(
                    'placeholder' => 80
                )
            ])
            ->add('questionsToAsk', NumberType::class, [
                'label' => 'label.survey_questionsToAsk',
                'help' => 'help.survey_questionsToAsk',
                'attr' => array(
                    'placeholder' => 15
                )
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'label.survey_image',
                'help' => 'help.survey_image',
                'required' => false,
            ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();

                $data = $event->getData();

                $category = $data->getCategory();
                $subCategories = null === $category ? [] : $category->getSubCategories();

                $form->add('subCategory', EntityType::class, [
                    'class' => SubCategory::class,
                    'placeholder' => 'placeholder.survey_subCategory',
                    'choice_label' => 'title',
                    'label' => 'label.survey_sub_category',
                    'choices' => $subCategories,
                ]);
            }
        );

        $formModifier = function (FormInterface $form, Category $category = null){
            $subCategories = null === $category ? [] : $category->getSubCategories();

            $form->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choices' => $subCategories,
                'choice_label' => 'title',
                'label' => 'label.survey_sub_category',
            ]);
        };

        $builder->get('category')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier){
                $category = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $category);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }
}
