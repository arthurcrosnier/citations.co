<?php
namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\CitationInternaute;
use App\Entity\CitationV2;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Service\ImageTraitement;
use App\Service\ImageVerif;
use App\Service\SiteMap;
use App\Service\UtilityTools;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class AdminController extends AbstractController
{
    /**
     * @Route("/adminvog3", name="admin")
     * @IsGranted("ROLE_ADMIN")
     * @return mixed
     */
    public function admin()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' => 'DESC']
        );

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
            'users' => $users
        ]);
    }

    /**
     * @Route("/adminvog3/setCitationDuJour", name="set_citation_du_jour")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response
     */
    public function setCitationDuJourAdmin(Request $request)
    {
        if($request->get('datecitationdujour') !== null && $request->get('idCitation'))
        {
            $em = $this->getDoctrine()->getManager();
            $repoCitation = $this->getDoctrine()->getManager()->getRepository(CitationV2::class);
            $citation = $repoCitation->find($request->get('idCitation'));
            if ($citation != null)
            {
                $citation->setCitationDuJour(true);
                $citation->setDateCitationDuJour(\DateTime::createFromFormat('Y-m-d', $request->get('datecitationdujour')));
                $em->persist($citation);
                $em->flush();
                return $this->redirectToRoute('citation_unique', ["id" => $request->get('idCitation')]);
            }
            return new Response("citation is null");
        }
        return new Response("not data");
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/parse/deleteUselessTag", name="delete_useless_tag")
     * @return Response
     */
    public function deleteUselessTag()
    {
        $em = $this->getDoctrine()->getManager();
        $repoCitation = $em->getRepository(CitationV2::class);
        $thematiques = $em->getRepository(Tag::class)->findAll();
        $i = 0;
        foreach ($thematiques as $tag) {
            if ($repoCitation->countAllCitationByTag($tag->getName()) <= 0)
            {
                UtilityTools::var_dump($tag->getName());
                $em->remove($tag);
                $i++;
            }
        }
        if ($i > 0)
            $em->flush();
        return new Response($i. " Tag removed");
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/parse/deleteUselessAuteur", name="delete_useless_auteur")
     * @return Response
     */
    public function deleteUselessAuteur()
    {
        $em = $this->getDoctrine()->getManager();
        $repoCitation = $em->getRepository(CitationV2::class);
        $auteurs = $em->getRepository(Auteur::class)->findAll();
        $i = 0;
        foreach ($auteurs as $auteur) {
            if ($repoCitation->countAllCitationByAuteur($auteur->getName()) <= 0)
            {
                UtilityTools::var_dump($auteur->getName());
                $em->remove($auteur);
                $i++;
            }
        }
        if ($i > 0)
            $em->flush();
        return new Response($i. " Auteurs removed");
    }
}
