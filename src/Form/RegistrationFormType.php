<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('username');
		$builder->add('plainPassword', PasswordType::class, [
			// instead of being set onto the object directly,
			// this is read and encoded in the controller
			'mapped' => false,
			'attr' => ['autocomplete' => 'new-password'],
			'constraints' => [
				new NotBlank(['message' => 'Please enter a password',]),
				new Length([
					'min' => 6,
					'minMessage' => 'Your password should be at least {{ limit }} characters',
					// max length allowed by Symfony for user reasons
					'max' => 4096,
				]),
			],
		]);
		$builder->add('email', EmailType::class, [
			'label' => $options['labels']?'form.email.email':false,
			'constraints' => [
				new Email([
					'message' => 'form.email.help',
				]),
			],
			'attr' => [
				'placeholder' => 'form.email.email'
			]
		]);
		$builder->add('agreeTerms', CheckboxType::class, [
			'label' => $options['labels']?'form.register.terms':false,
			'mapped' => false,
			'constraints' => [
				new IsTrue([
					'message' => 'form.register.toshelp',
				]),
			],
		]);
		$builder->add('newsletter', CheckboxType::class, [
			'label' => $options['labels']?'form.newsletter.newsletter':false,
			'mapped' => false,
			'constraints' => [
				new IsTrue([
					'message' => 'form.newsletter.help',
				]),
			],
		]);
		$builder->add('submit', SubmitType::class, [
			'label' => 'form.register.submit'
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([
			'data_class' => User::class,
			'labels' => true,
		]);
	}
}
