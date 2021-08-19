<?php


namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class ContactController
 */
class ArticleController extends BaseController
{
    /**
     * @Route( "admin/new", name="article_new")
     */
    public function new(EntityManagerInterface $em, Request $request,SluggerInterface $slugger)
    {
        $form = $this->createForm(ArticleFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Article $article */
            $article = $form->getData();



            /** @var UploadedFile $mediaFile */
            $mediaFile = $form->get('media')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($mediaFile) {
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$mediaFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $mediaFile->move(
                        $this->getParameter('medias_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setMedia($newFilename);
                $article->setCreationDate(new \DateTime("now"));
            }


            $article->setSlug($article->getId()."-".$article->getSlug());

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', $this->trans("article_created"));

            return $this->redirectToRoute('article_list');
        }

        return $this->render('article/new.html.twig', [
            'articleForm' => $form->createView(),
        ]);

    }



    /**
     * @Route("/admin", name="article_list")
     */
    public function list(ArticleRepository $articleRepo)
    {

        $articles = $articleRepo->findAll();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @route("/admin/{id}/edit",name="article_edit")
     */
    public function articleEdit(Article $article, EntityManagerInterface $em,Request $request,SluggerInterface $slugger)
    {
        $fileName=$article->getMedia();
        $article->setMedia(
            new File($this->getParameter('medias_directory').'/'.$article->getMedia())
        );
        $form = $this->createForm(ArticleFormType::class, $article);


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){


                /** @var Article $articleType */
                $articleType = $form->getData();

                /** @var  UploadedFile $mediaFile */
                $mediaFile = $form['media']->getData();

                if($mediaFile){
                    $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$mediaFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $mediaFile->move(
                            $this->getParameter('medias_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $article->setMedia($newFilename);
                } else {
                    $articleType->setMedia($fileName);
                }

                $em->persist($articleType);
                $em->flush();

            $this->addFlash('success', $this->trans("article_updated_success_message"));

            return $this->redirectToRoute('article_list');

            }

            return $this->render('article/edit.html.twig',[
                'articleForm' => $form->createView(),
                'article'=>$article
            ]);
    }

    /**
     * @route("/admin/{id}/delete",name="article_delete")
     */
    public function articleDelete(Article $article, EntityManagerInterface $em)
    {

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', $this->trans("success_message_article_deleted"));

        return $this->redirectToRoute('article_list');
    }
    /**
     * @route("/admin/{id}/show",name="article_show")
     */
    public function articleShow(Article $article, EntityManagerInterface $em)
    {
        return $this->render('article/show.html.twig',[
            'article'=>$article
        ]);
    }

    /**
     * @route("/{slug}",name="article_show_front")
     */
    public function article_show_front(Request $request,ArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $slug=$request->get('slug');
        $article=$articleRepository->findOneBy(['slug'=>$slug]);
        return $this->render('article/show.html.twig',[
            'article'=>$article
        ]);
    }

}