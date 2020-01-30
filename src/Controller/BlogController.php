<?php

// src/Controller/BlogController.php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Service\UtilityTools;
use App\Form\ArticleType;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BlogController extends AbstractController
{
    private $ARTICLES_PAR_PAGE = 6;
    /**
     * @Route("/blog", name="blogIndex")
     * @return mixed
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $repoArticles = $em->getRepository(Article::class);
        $articles = $repoArticles->findBy(
            ['isPublished' => true],
            ['publicationDate' => 'desc'],
            $this->ARTICLES_PAR_PAGE,
            UtilityTools::getPageStart($this->ARTICLES_PAR_PAGE)
        );
        $totalArticles = $repoArticles->createQueryBuilder('a')
            ->where('a.isPublished = 1')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('citations/blog/index.html.twig' ,[
            'articles' => $articles,
            'pagingHtml' => UtilityTools::pagination($this->ARTICLES_PAR_PAGE,
                UtilityTools::getPage(),
                $totalArticles,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?")
            ]);
    }

    /**
     * @Route("/blog/add", name="blogAdd")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function add(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getPicture() !== null) {
                $file = $form->get('picture')->getData();
                $fileName =  uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans le quel le fichier va etre charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            }

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($article); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return $this->redirectToRoute('admin');
        }
        return $this->render('citations/blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/blog/show/{article}", name="blogShow")
     * @param Article $article
     * @return mixed
     */
    public function show(Article $article)
    {
        return $this->render('citations/blog/show.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/blog/{slug}", name="blogShowSlug")
     * @param $slug
     * @return mixed
     */
    public function showSlug($slug)
    {
        $repoArticles = $this->getDoctrine()->getManager()->getRepository(Article::class);
        $article = $repoArticles->findOneBy(['slug' => $slug]);

        return $this->render('citations/blog/show.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/blog/edit/{article}", name="blogEdit")
     * @IsGranted("ROLE_ADMIN")
     * @param Article $article
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function edit(Article $article, Request $request)
    {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            if ($article->getPicture() !== null && $article->getPicture() !== $oldPicture) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            } else {
                $article->setPicture($oldPicture);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('citations/blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/blog/remove/{article}", name="blogRemove")
     * @IsGranted("ROLE_ADMIN")
     * @param Article $article
     * @return mixed
     */
    public function remove(Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/blog/uploadImage", name="blogUploadImageArticle")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function addImageArticle(Request $request, FileUploader $fileUploader)
    {
        $imageFolder = $this->getParameter('images_directory');
        $file = $request->files->get('file');
        if (isset($file))
        {
            $extension = $file->guessExtension();
            if (!in_array(strtolower($extension), array("gif", "jpg", "png", "jpeg"))) {
                return new Response(json_encode("Error : pas une image ou mauvaise extension : ".$extension));
            }
            $fileName = uniqid(). '.' .$extension;
            try {
                $file->move(
                    $imageFolder,
                    $fileName
                );
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }
            return new Response(json_encode(array('location' => $fileName)));
        }
        else
        {
            return new Response(json_encode("Error : not file"));
        }
    }
}