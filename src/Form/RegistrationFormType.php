<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('username', TextType::class, [
			'label' => $options['labels']?'Username':false,
			'constraints' => [
				new Regex([
					'pattern' => '/^[a-zA-Z0-9 \-_]*$/',
					'message' => 'Must be alphanumeric. Only spaces, dashes, and undesrcores for special characters.',
				]),
			],
			'attr' => [
				'placeholder' => 'username'
			]
		]);
		$builder->add('plainPassword', RepeatedType::class, [
			'type' => PasswordType::class,
			# instead of being set onto the object directly,
			# this is read and encoded in the controller
			'mapped' => false,
			'options' => ['attr' => ['password-field']],
			'required' => true,
			'invalid_message' => 'Passwords do not match.',
			'first_options' => [
				'label' => $options['labels']?'Password':false,
				'attr' => [
					'placeholder' => 'password'
				]
			],
			'second_options' => [
				'label' => $options['labels']?'Confirm Password':false,
				'attr' => [
					'placeholder' => 'password'
				]],
			'constraints' => [
				new Length([
					'min' => 8,
					'minMessage' => 'Must be atleast 8 characters. More is better.',
					# max length allowed by Symfony for security reasons
					'max' => 4096,
				]),
			],
		]);
		$builder->add('email', EmailType::class, [
			'label' => $options['labels']?'Email':false,
			'constraints' => [
				new Email([
					'message' => 'Must be a valid email address.',
				]),
			],
			'attr' => [
				'placeholder' => 'you@yourhost.com'
			]
		]);
		$builder->add('agreeTerms', CheckboxType::class, [
			'label' => $options['labels']?'Agree to Terms of Service?':false,
			'mapped' => false,
			'constraints' => [
				new IsTrue([
					'message' => 'You must agree to the Terms of Service in order to have an account on this site.',
				]),
			],
		]);
		$builder->add('newsletter', CheckboxType::class, [
			'label' => $options['labels']?'Receive Newsletter?':false,
			'mapped' => false,
		]);
		$builder->add('submit', SubmitType::class, [
			'label' => 'Sign Up!'
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([
			'data_class' => User::class,
			'labels' => true,
		]);
	}
}
