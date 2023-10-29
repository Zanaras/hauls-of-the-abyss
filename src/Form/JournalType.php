<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class JournalType extends AbstractType {

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'intention'       => 'journal_22020329',
			'translation_domain' => 'messages',
		));
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('topic', TextType::class, array(
			'required' => true,
			'label' => 'Topic',
			'attr' => array('size'=>40, 'maxlength'=>80)
		));
		$builder->add('entry', TextareaType::class, array(
			'label' => 'Entry',
			'trim' => true,
			'required' => true
		));
		$builder->add('public', CheckboxType::class, array(
			'label'=>'Publicly Viewable?',
			'required'=>false,
			'data'=>false,
		));
		$builder->add('ooc', CheckboxType::class, array(
			'label'=>'Consider this OOC?',
			'required'=>false,
			'data'=>false,
		));
		$builder->add('graphic', CheckboxType::class, array(
			'label'=>'Contains Graphic Depictions?',
			'required'=>false,
			'data'=>false,
		));

		$builder->add('submit', SubmitType::class, array('label'=>'Submit'));
	}

}
