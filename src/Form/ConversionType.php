<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\ConversionRequest;
use App\Service\Rate\ExchangeRateProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ConversionRequest>
 */
final class ConversionType extends AbstractType
{
    public function __construct(
        private readonly ExchangeRateProviderInterface $rateProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach ($this->rateProvider->getCurrencies() as $code => $name) {
            $choices[sprintf('%s — %s', $code, $name)] = $code;
        }

        $builder
            ->add('amount', TextType::class, [
                'label' => 'Montant',
                'attr' => ['inputmode' => 'decimal', 'autofocus' => true, 'placeholder' => '100.00'],
            ])
            ->add('from', ChoiceType::class, [
                'label' => 'De',
                'choices' => $choices,
            ])
            ->add('to', ChoiceType::class, [
                'label' => 'Vers',
                'choices' => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConversionRequest::class,
        ]);
    }
}
