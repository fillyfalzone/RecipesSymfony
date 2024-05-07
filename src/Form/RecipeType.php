<?php

namespace App\Form;

use App\Entity\Recipe;
use PhpParser\Node\Expr\Empty_;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => '' //Valeur par defaut si rien n'est renseigné
            ])
            ->add('slug', TextType::class, [
                "required" => false,
                'constraints' => new Length(min: 10 )
            ])
            ->add('duration', IntegerType::class)
            ->add('content', TextareaType::class, [
                'empty_data' => ''
            ])
            ->add('submit', SubmitType::class, [
                "label" => "Modifier",
                "attr" => [
                    "class" => "btn btn-lg d-block mx-auto btn-primary"
                ]
            ])
            // Ajout d'un evenement de PRESUBMIT pour modifier les data du formulaire avant soumission
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...))
            //exo Ajout d'un evenement POST_SUBMIT pour mettre à jours les date de creation et de update
            ->addEventListener(FormEvents::POST_SUBMIT, $this->addTime(...))
        ;
    }
    // La fonction callable qui permet de générer un slug à partir du title de la recette 
    public function autoSlug(PreSubmitEvent $event) : void
    {
        $data = $event->getData();

        if (empty($data['slug'])) {
            $slugger = new AsciiSlugger();
            $data['slug'] = strtolower($slugger->slug($data['title']));
            $event->setData($data);
        }
    }

    // La fonction callable qui permet d'ajouter createdAt et updateAt

    public function addTime(PostSubmitEvent $event): void
    {
        $data = $event->getData();
        if (!($data instanceof Recipe)) {
            return;
        }

        $data->setUpdatedAt(new \DateTimeImmutable());
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            // Les groups de validation sont mises sur les attributs de la class Recipe
            'validation_groups' => ['Default', 'Extra'],
        ]);
    }
}
