<?php

namespace App\Form;

use App\Entity\Origin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterCreatorType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([
			'translation_domain' => 'play'
		]);

	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('name', TextType::class, [
			'label' => 'character.name',
			'required' => true,
			'attr' => [
				'size' => 30,
				'maxlength' => 80,
				'title' => 'character.help.name',
			],
		]);
		$builder->add('gender', ChoiceType::class, [
			'label' => 'character.gender',
			'required' => true,
			'choices' => [
				'g' => 'genderless',
				'f' => 'female',
				'm' => 'male',
				'n' => 'non-binary',
				'o' => 'other'
			],
			'attr' => ['title' => 'character.help.gender']
		]);
		$builder->add('origin', EntityType::class, [
			'label' => 'character.origin',
			'required' => true,
			'multiple' => false,
			'choices' => $options['origins'],
			'class' => Origin::class,
			'choice_label' => function($choice) {
				return 'character.origin.'.$choice->getName();
			},
		]);
	}
}
