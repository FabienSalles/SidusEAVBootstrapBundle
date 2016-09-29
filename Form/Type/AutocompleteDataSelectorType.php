<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Samson\Bundle\AutocompleteBundle\Form\Listener\AutoCompleteTypeListener;
use Samson\Bundle\AutocompleteBundle\Form\Type\AutoCompleteType;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

class AutocompleteDataSelectorType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var EntityRepository */
    protected $repository;

    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * @param string                     $dataClass
     * @param EntityRepository           $repository
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     */
    public function __construct(
        $dataClass,
        EntityRepository $repository,
        FamilyConfigurationHandler $familyConfigurationHandler
    ) {
        $this->dataClass = $dataClass;
        $this->repository = $repository;
        $this->familyConfigurationHandler = $familyConfigurationHandler;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['auto_init']) {
            if (empty($view->vars['attr']['class'])) {
                $view->vars['attr']['class'] = '';
            } else {
                $view->vars['attr']['class'] .= ' ';
            }
            $view->vars['attr']['class'] .= 'select2';
            if (!$options['required']) {
                $view->vars['attr']['class'] .= ' force-allowclear';
            }
        }
        $view->vars['attr']['data-placeholder'] = $options['placeholder'];
        $view->vars['family'] = $options['family'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $qb = $this->repository->createQueryBuilder('d');
        $qb->innerJoin('d.values', 'v');

        /** @var FamilyInterface $family */
        $family = $options['family'];
        if ($family) {
            $qb
                ->andWhere('d.family = :family')
                ->setParameter('family', $family->getCode())
            ;
            if ($family->getAttributeAsLabel()) {
                $qb
                    ->andWhere('v.attributeCode = :attributeCode')
                    ->setParameter('attributeCode', $family->getAttributeAsLabel()->getCode())
                ;
            }
        }
        $builder->setAttribute('query-builder', $qb);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws UnexpectedValueException
     * @throws MissingFamilyException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => $this->dataClass,
            'search_fields' => ['v.stringValue'],
            'template' => 'SidusEAVModelBundle:Data:data_autocomplete.html.twig',
            'family' => null,
            'auto_init' => true,
        ]);

        $resolver->setNormalizer('family', function (Options $options, $value) {
            if (null === $value) {
                return null;
            }
            if ($value instanceof FamilyInterface) {
                return $value;
            }

            return $this->familyConfigurationHandler->getFamily($value);
        });
        $resolver->setNormalizer('disabled', function (Options $options, $value) {
            if (null === $options['family']) {
                return true;
            }

            return $value;
        });
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return AutoCompleteType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_autocomplete_data_selector';
    }
}
