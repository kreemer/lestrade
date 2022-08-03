<?php

namespace App\Form;

use App\Entity\Frame;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class FrameType extends AbstractType
{
    private ?User $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', null, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('endAt', null, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('tags')
            ->add('project', null, [
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.createdBy = :user')
                        ->setParameter('user', $this->user);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Frame::class,
        ]);
    }
}
