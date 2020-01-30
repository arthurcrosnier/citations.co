<?php
namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\CitationInternaute;
use App\Entity\CitationV2;
use App\Entity\LikeCitationCelebre;
use App\Entity\LikeCitationInternaute;
use App\Entity\User;
use App\Service\FileUploader;
use App\Service\ImageTraitement;
use App\Service\ImageVerif;
use App\Service\UtilityTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AjaxController extends AbstractController
{
    /**
     * @Route("/createCitationAdminAjax", name="create_citation_admin_ajax")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param ImageVerif $imageVerif
     * @param ImageTraitement $traitement
     * @return Response
     * @throws \Exception
     */
    public function createCitationAdminAjax(Request $request, ImageVerif $imageVerif, ImageTraitement $traitement)
    {
        // si on a le formulaire et que l'on est connecté
        if ($request->get('auteur') !== null && $request->get('citation') !== null && $request->get('urlImage') !== null && $request->get('tags') !== null && $request->get('type') !== null)
        {
            $repoCitation = $this->getDoctrine()->getManager()->getRepository(CitationV2::class);
            $repoAuteur = $this->getDoctrine()->getManager()->getRepository(Auteur::class);
            $entityManager = $this->getDoctrine()->getManager();
            // on récupère les dossiers d'images
            $imageFolderSave = $this->getParameter('images_citation_save');
            $imageFolderDestLegit = $this->getParameter('images_citation_legit');
            // on vérifie la validité du formulaire (un peu à l'ancienne certe)
            $error = $this->validForm($request->get('citation'), $request->get('auteur'), $request->get('urlImage'), $request->get('tags'), $request->get('type'));
            if (!empty($error))
                return new Response($error);
            // on vérifie que la citation n'éxiste pas
            if ($this->getDoctrine()->getRepository(CitationV2::class)->existByDescription($request->get('citation')))
                return new Response("La citation existe déjà");
            // on récupère le fichier uploadé précédement par l'utilisateur
            $src = $imageFolderSave."/".$request->get('urlImage');
            if (!file_exists($src))
                return new Response("L'image n'existe pas ou plus ==> ". $src);
            $file = new File($src);
            // on revérifie l'image ça ne mange pas de pain
            $imageVerif->init($file->getSize(), $file->guessExtension(), getimagesize($file->getPathName()));
            $verif = $imageVerif->verif_image();

            if ($verif != null)
                return new Response($verif);
            // on traite l'image de la citation
            $tt = UtilityTools::removeAccent(trim(strtolower($request->get('auteur'))));

            //création du dossier
            if (!is_dir($imageFolderDestLegit."/".$tt)) {
                mkdir($imageFolderDestLegit."/".$tt, 0777);
            }
            $dest = $imageFolderDestLegit."/".$tt."/".$request->get('urlImage');
            $traitement->init($src, $dest, $request->get('citation'), $request->get('auteur'), $file->guessExtension());
            $traitement->traitement();

            // on supprime l'image dans save (tmp)
            unlink ($src);
            // on ajoute la citation dans la bdd
            $citation = new CitationV2();
            $auteur = $repoAuteur->findOneByName($request->get('auteur'));
            if ($auteur != null)
                $citation->setAuteur($auteur);
            else
            {
                $auteur = new Auteur();
                $auteur->setName($request->get('auteur'));
                $auteur->setBio("Bio");
                $auteur->setUpload(new \DateTime());
                $auteur->setType($request->get('type'));
                $citation->setAuteur($auteur);
            }
            $repoCitation->insertNew($citation, explode(",", $request->get('tags')), 0, $request->get('citation'), substr($request->get('urlImage'), 0, -4), $this->getDoctrine()->getManager());
            return new Response("valid");
        }
        return new Response("not valid");
    }

    /**
     * @Route("/createAjax", name="create_citation_user_ajax")
     * @param Request $request
     * @param ImageVerif $imageVerif
     * @param ImageTraitement $traitement
     * @param TokenStorageInterface $tokenStorage
     * @return Response
     * @throws \Exception
     */
    public function createCitationUserAjax(Request $request, ImageVerif $imageVerif, ImageTraitement $traitement, TokenStorageInterface $tokenStorage)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $user=$tokenStorage->getToken()->getUser();
            // si on a le formulaire et que l'on est connecté
            if ($request->get('auteur') !== null && $request->get('citation') !== null && $request->get('urlImage') !== null)
            {
                $entityManager = $this->getDoctrine()->getManager();
                // on récupère les dossiers d'images
                $imageFolderSave = $this->getParameter('images_citation_save');
                $imageFolderDestUser = $this->getParameter('images_citation_user');
                // on vérifie la validité du formulaire (un peu à l'ancienne certe)
                $error = $this->validForm($request->get('citation'), $request->get('auteur'), $request->get('urlImage'), false, false);
                if(!empty($error))
                    return new Response($error);
                // on récupère le fichier uploadé précédement par l'utilisateur
                $src = $imageFolderSave."/".$request->get('urlImage');
                if (!file_exists($src))
                    return new Response("L'image n'existe pas ou plus");
                $file = new File($src);
                // on revérifie l'image ça ne mange pas de pain
                $imageVerif->init($file->getSize(), $file->guessExtension(), getimagesize($file->getPathName()));
                $verif = $imageVerif->verif_image();
                if ($verif != null)
                    return new Response($verif);
                // on traite l'image de la citation
                $dest = $imageFolderDestUser."/".$request->get('urlImage');
                $traitement->init($src, $dest, $request->get('citation'), $request->get('auteur'), $file->guessExtension());
                $traitement->traitement();
                // on supprime l'image dans save (tmp)
                unlink ($src);
                // on ajoute la citation dans la bdd
                $citation = new CitationInternaute();
                $citation->setDescription($request->get('citation'));
                $citation->setAuteur($request->get('auteur'));
                $citation->setUrl(substr($request->get('urlImage'), 0, -4));
                $citation->setUpload(new \DateTime);
                $citation->setUserAjout($this->getDoctrine()->getRepository(User::class)->find($user->getId()));
                $citation->setCountLikes(0);
                $entityManager->persist($citation);
                $entityManager->flush();
                return new Response("valid");
            }
        }
        return new Response(null);
    }

    /**
     * @param $citation
     * @param $auteur
     * @param $urlimage
     * @param $tags
     * @param $type
     * @return string
     */
    function validForm($citation, $auteur, $urlimage, $tags, $type)
    {
        $error = "";
        if ($type != 1 && $type != 2 && $type != false)
            $error .= "Erreur sur le type <br>";
        if ((strlen($tags) > 80 || strlen($tags) < 2) && $tags != false)
            $error .= "les tags sont invalides <br>";
        if (strlen($auteur) > 80 || strlen($auteur) < 2)
            $error .= "l'auteur est invalide <br>";
        if (strlen($citation)  < 2 || strlen($citation) > 400)
            $error .= "La citation est invalide<br>";
        if (strlen($urlimage)  < 13)
            $error .= $urlimage."Aucune image n'a été ajouté";
        return $error;
    }



    /**
     * @Route("/uploadImageUser", name="upload_image_user_ajax")
     *
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param ImageVerif $imageVerif
     * @return Response|null
     */
    public function uploadImageAjaxUser(Request $request, FileUploader $fileUploader, ImageVerif $imageVerif)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return null;
        $imageFolder = $this->getParameter('images_citation_save');
        $file = $request->files->get('citationImage');
        if (isset($file))
        {
            $imageVerif->init($file->getSize(), $file->guessExtension(), getimagesize($file->getPathName()));
            $verif = $imageVerif->verif_image();
            if ($verif == null)
            {
                $fileName = uniqid(). '.' .$file->guessExtension();
                try {
                    $file->move(
                        $imageFolder,
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
                return new Response($fileName);
            }
            else
                return new Response($verif);
        }
        else
        {
            return null;
        }
    }

    /**
     * @Route("/likeCitationAjax", name="like_citation_ajax")
     */
    public function likeCitationAjax(Request $request)
    {
        $idCitation = $request->get('idCitation');
        if (!isset($idCitation))
            return null;
        $em = $this->getDoctrine()->getManager();
        $repoLike = $this->getDoctrine()->getRepository(LikeCitationCelebre::class);
        $request = Request::createFromGlobals();
        $clientIp = $request->getClientIp();
        // combien le client à t'il liké de citations aujourd'hui ?
        if ($repoLike->countLikeIpToday($clientIp) > 100)
            return null;
        $citation = $this->getDoctrine()->getRepository(CitationV2::class)->find($idCitation);
        //la citation existe ?
        if ($citation != null)
        {
            //Le client a t'il déjà liké cette citation ?
            if ($repoLike->ipLikedCitation($clientIp, $idCitation) == false)
            {
                $citation->setCountLikes($citation->getCountLikes() + 1);
                $like = new LikeCitationCelebre();
                $like->setCitation($citation);
                $like->setIp($clientIp);
                $like->setLikeDate(new \DateTime);
                $em->persist($citation);
                $em->persist($like);
                $em->flush();
                return new Response("like");
            }
            else
            {
                return new Response("dislike");
                // on enlève le like ?
            }
        }
        else
            return null;
    }

    /**
     * @Route("/likeCitationInternauteAjax", name="like_citation_internaute_ajax")
     */
    public function likeCitationInternauteAjax(Request $request)
    {
        $idCitation = $request->get('idCitation');
        if (!isset($idCitation))
            return null;
        $em = $this->getDoctrine()->getManager();
        $repoLike = $this->getDoctrine()->getRepository(LikeCitationInternaute::class);
        $request = Request::createFromGlobals();
        $clientIp = $request->getClientIp();
        // combien le client à t'il liké de citations aujourd'hui ?
        if ($repoLike->countLikeIpToday($clientIp) > 100)
            return null;
        $citation = $this->getDoctrine()->getRepository(CitationInternaute::class)->find($idCitation);
        //la citation existe ?
        if ($citation != null)
        {
            //Le client a t'il déjà liké cette citation ?
            if ($repoLike->ipLikedCitation($clientIp, $idCitation) == false)
            {
                $citation->setCountLikes($citation->getCountLikes() + 1);
                $like = new LikeCitationInternaute();
                $like->setCitation($citation);
                $like->setIp($clientIp);
                $like->setLikeDate(new \DateTime);
                $em->persist($citation);
                $em->persist($like);
                $em->flush();
                return new Response("like");
            }
            else
            {
                return new Response("dislike");
                // on enlève le like ?
            }
        }
        else
            return null;
    }
}
