<?php

namespace Sidus\EAVBootstrapBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'datepicker' => [
                'attr' => [
                    'class' => 'input-group date col-lg-3',
                ],
            ],
        ]);
    }

    public function getParent()
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_date_picker';
    }
}
