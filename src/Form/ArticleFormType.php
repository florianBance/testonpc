<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleFormType extends AbstractType
{
    private $translator;
    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,[
                'required'=>true,
            ])
            ->add('text',TextareaType::class,[
                'required'=>true,
            ])
            ->add('media',FileType::class, [
                'data_class'=>null,
                'label'=>'image',
                'mapped'=>true,
                'required'=>false,
                'constraints'=>[
                    new File([
                        'maxSize'=>'5M',
                        'mimeTypes'=>[
                            'image/*',
                        ],
                        'mimeTypesMessage'=>$this->translator->trans("Veuillez uploader un fichier valide."),
                    ])
                ]
            ])
            ->add('slug',TextType::class,[
                'required'=>true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
