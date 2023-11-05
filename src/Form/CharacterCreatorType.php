<?php

namespace App\Form;

use App\Entity\Origin;
use App\Entity\Race;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterCreatorType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([
			'translation_domain' => 'play'
		]);
		$resolver->setRequired(['origins', 'races']);

	}
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('name', TextType::class, [
			'label' => 'form.character.name.title',
			'required' => true,
			'attr' => [
				'size' => 30,
				'maxlength' => 80,
				'title' => 'form.character.name.help',
			],
		]);
		$builder->add('gender', ChoiceType::class, [
			'label' => 'form.character.gender.title',
			'required' => true,
			'choices' => [
				'genderless' => 'g',
				'female' => 'f',
				'male' => 'm',
				'non-binary' => 'n',
				'other' => 'o',
			],
			'attr' => ['title' => 'form.character.gender.help'],
			'choice_label' => function($choice, $key) {
				return 'form.character.gender.choice.'.$key;
			},
		]);
		$builder->add('origin', EntityType::class, [
			'label' => 'form.character.origin.title',
			'required' => true,
			'multiple' => false,
			'choices' => $options['origins'],
			'class' => Origin::class,
			'choice_label' => function($choice) {
				return 'form.character.origin.choice.'.$choice->getName();
			},
			'choice_translation_domain' => true, #So it knows these should translate.
		]);
		$builder->add('race', EntityType::class, [
			'label' => 'form.character.race.title',
			'required' => true,
			'multiple' => false,
			'choices' => $options['races'],
			'class' => Race::class,
			'choice_label' => function($choice) {
				return 'race.'.$choice->getName().'.name';
			},
			'choice_translation_domain' => true, #So it knows these should translate.
		]);
		$builder->add('submit', SubmitType::class, array('label'=>'form.character.submit'));
	}
}
