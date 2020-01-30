<?php

// src/Controller/FrontCitationController.php

namespace App\Controller;

use App\Service\ImageTraitement;
use App\Service\SiteMap;
use App\Service\UtilityTools;
use App\Entity\CitationV2;
use App\Entity\CitationInternaute;
use App\Entity\Auteur;
use App\Entity\User;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\FileUploader;
use App\Service\ImageVerif;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

//TODO

//  Mot de passe oublié ?, mail de bienvenue?, pub?, biographie ?, panel utilisateur (changer mdp...), panel utilisateur voir citation fav + citation ajouté, tri par like, commentaire, + de filtres images
class FrontCitationController extends AbstractController
{
    private $CITATION_PAR_PAGE = 12;

    /**
     * @Route("/citation/{id}/{citationUrl}", name="citation_unique_old", requirements={"id"="\d*", "citationUrl"=".+"})
     * @param $id
     * @return mixed
     */
    public function citationUniqueOldAction($id, $citationUrl)
    {
        return $this->redirectToRoute("citation_unique", ["id" => $id], 301);
    }

    /**
     * @Route("/citation/{id}", name="citation_unique", requirements={"id"="\d*"})
     * @param $id
     * @return mixed
     */
    public function citationUniqueAction($id)
    {
        $citation = $this->getDoctrine()->getRepository(CitationV2::class)->find($id);

        return $this->render(
            'citations/front/citation_unique.html.twig', [
            'citation' => $citation,
        ]);
    }

    /**
     * @Route("/citation-internaute/{id}", name="citation_unique_internaute", requirements={"id"="\d*"})
     * @param $id
     * @return mixed
     */
    public function citationUniqueInternauteAction($id)
    {
        $citation = $this->getDoctrine()->getRepository(CitationInternaute::class)->find($id);

        return $this->render(
            'citations/front/citation_unique.html.twig', [
            'citation' => $citation,
                'internaute' => true
        ]);
    }

    /**
     * @Route("/", name="citation_front_home")
     * @return mixed
     */
    public function homeRouteAction()
    {
        $repo = $this->getDoctrine()->getRepository(CitationV2::class);
        $meilleurCitation = $repo->findAllCitation(0, 3, "c.count_likes DESC");
        $citationDuJour = $repo->findCitationDuJour(0, 3, "c.date_citation_du_jour DESC");
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitation(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri());
        $thematiques = $this->getDoctrine()->getRepository(Tag::class)->findAllTagByPop();
        $auteurs = $this->getDoctrine()->getRepository(Auteur::class)->findAllAuteurByPop(1);
        return $this->render(
            'citations/front/home.html.twig', [
            'citations' => $citations,
            'citationDuJour' => $citationDuJour[0],
            'meilleurCitation' => $meilleurCitation[0],
            'thematique_pop' => $thematiques,
            'auteurs' => $auteurs
        ]);
    }

    /**
     * @Route("/{slug}", name="citation_front_theme", requirements={"slug"="fiction|celebre|inspiration"})
     * @param string $slug
     * @return mixed
     */
    public function citationThemeAction(string $slug)
    {
        switch ($slug) {
            case "celebre":
                $theme = "GENRE_CELEBRE";
                $titre = "Citations Célèbres";
                $title = "Citations célèbres et connus d'auteurs, philosophes, figures religieuses et politiques ...";
                $description = "Citations de célébrités classées par auteur, ordre alphabétique et par popularité. Chaque citation est accompagné de son image pour la mettre en valeur.";
                break;
            case "fiction":
                $theme = "GENRE_FICTION";
                $titre = "Citations Fictions";
                $title = "Citation de personnages de fiction (films, séries, déssins animés ...) : Phrase cultes - citation-inspiration.com";
                $description = "Citations de personnages de fictions classées par films, séries, animations. Chaque citation ou phrase culte est accompagné de son image pour la mettre en valeur.";
                break;
            case "inspiration":
                $theme = "GENRE_INSPIRATION";
                $titre = "Citations Inspirations";
                $title = "Citations inspirantes et motivantes sur la réussite et la vie : Citation-inspiration.com - votre dealer d'inspiration";
                $description = "Citations inspirantes classées par auteur, ordre alphabétique et par popularité. Chaque citation inspirante est accompagné de son image pour la mettre en valeur.";
                break;
            default:
                $theme = "GENRE_CELEBRE";
                $titre = "Citations Célèbres";
                $title = "Citations célèbres et connus d'auteurs, philosophes, figures religieuses et politiques ...";
                $description = "Citations de célébrités classées par auteur, ordre alphabétique et par popularité. Chaque citation est accompagné de son image pour la mettre en valeur.";
        }
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationByGenre(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(), $theme);
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $total = $this->getDoctrine()->getRepository(CitationV2::class)->countAllCitationByGenre($theme);
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                            UtilityTools::getPage(),
                            $total,
                        (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/citations-internautes", name="citations_internautes_front_theme")
     * @return mixed
     */
    public function citationInternautesAction()
    {
        $titre = "Citations d'internautes";
        $citations = $this->getDoctrine()->getRepository(CitationInternaute::class)->findAllCitationInternaute(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE, UtilityTools::getTri());
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $total = count($citations);
        $title = "Citation d'internautes : Les citations créé par les internautes : Citation-inspiration.com - votre dealer d'inspiration";
        $description = "citations d'internautes. Chaque citation d'internautes est accompagné de son image pour la mettre en valeur.";
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'internaute' => true,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                UtilityTools::getPage(),
                $total,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/auteur/{slug}", name="citations_auteur_front_theme", requirements={"slug"=".+"})
     * @param string $slug
     * @return mixed
     */
    public function citationAuteurAction(string $slug)
    {
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationByAuteur(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(), $slug);
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $total = $this->getDoctrine()->getRepository(CitationV2::class)->countAllCitationByAuteur($slug);
        $titre = "Citations de <span class='text-info'>".$slug."</span>";
        $title = "Toutes les citations de ".strtoupper($slug)." - Citation-inspiration.com";
        $description = "Liste des citations de ".ucfirst($slug)." classées par popularité. Chaque citation est accompagné de son image pour la mettre en valeur.";
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                UtilityTools::getPage(),
                $total,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/personnage/{slug}", name="citations_personnage_front_theme", requirements={"slug"=".+"})
     * @param string $slug
     * @return mixed
     */
    public function citationPersonnageAction(string $slug)
    {
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationByAuteur(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(), $slug);
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $total = $this->getDoctrine()->getRepository(CitationV2::class)->countAllCitationByAuteur($slug);
        $titre = "Citations de <span class='text-info'>".$slug."</span>";
        $title = "Citation de ".strtoupper($slug)." (personnage de fiction) : phrases cultes de".strtoupper($slug)." - Citation-inspiration.com";
        $description = "Liste des citations de ".ucfirst($slug)." (personnage de fiction). Chaque citation est accompagné de son image pour la mettre en valeur.";
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                UtilityTools::getPage(),
                $total,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/recherche", name="citations_recherche_first_front_theme")
     * @return mixed
     */
    public function citationRechercheFirstAction()
    {
        if (isset($_GET['search']) && strlen($_GET['search']) >= 2 && strlen($_GET['search']) <= 200)
        {
            return $this->redirectToRoute('citations_recherche_front_theme', ['slug' => str_replace(" ", "-", $_GET['search'])]);
        }
        else
        {
            return $this->redirectToRoute('citation_front_home');
        }
    }

    /**
     * @Route("/recherche/{slug}", name="citations_recherche_front_theme", requirements={"slug"=".+"})
     * @param $slug
     * @return mixed
     */
    public function citationRechercheAction($slug)
    {
        $slug = str_replace("-", " ", $slug);
        if (strlen($slug) >= 2 && strlen($slug) <= 200)
        {
            $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationBySearch(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(), $slug);
            $total = $this->getDoctrine()->getRepository(CitationV2::class)->countAllCitationBySearch($slug);
            $titre = "Citations <span class='text-info'>".$slug."</span> (recherche)";
            $title = strtoupper($slug)." : Recherche de citations, proverbes, auteurs, phrases cultes, personnages de fictions ... - Citation-inspiration.com";
            $description = "";

            //ajouts de tags automatiquement quand les users hits cette page
            $em = $this->getDoctrine()->getManager();
            $tagAdded = $this->getDoctrine()->getRepository(Tag::class)->findOneBy(['name' => $slug]);
            if ($tagAdded != null)
            {
                foreach ($citations as $citation)
                {
                    $citation->addTag($tagAdded);
                    $em->persist($citation);
                }
                $em->flush();
            }


            return $this->render(
                'citations/front/citations_list.html.twig', [
                'total' => $total,
                'citations' => $citations,
                'titre' => $titre,
                'title' => $title,
                'description' => $description,
                'tri' => UtilityTools::getTriHuman(),
                'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                    UtilityTools::getPage(),
                    $total,
                    (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
                ),
            ]);
        }
        else
        {
            return $this->redirectToRoute('citation_front_home');
        }
    }

    /**
     * @Route("/thematique/{slug}", name="citations_thematique_front_theme", requirements={"slug"=".+"})
     * @param string $slug
     * @return mixed
     */
    public function citationThematiqueAction(string $slug)
    {
        $slug = str_replace("-", " ", $slug);
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationByTag(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(), $slug);
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $total = $this->getDoctrine()->getRepository(CitationV2::class)->countAllCitationByTag($slug);
        $titre = "Citations <span class='text-info'>".$slug."</span>";
        $title = "Citation d'internautes : Les citations créé par les internautes : Citation-inspiration.com - votre dealer d'inspiration";
        $description = "citations d'internautes. Chaque citation d'internautes est accompagné de son image pour la mettre en valeur.";
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                UtilityTools::getPage(),
                $total,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/citation-du-jour", name="citations_du_jour_front_theme")
     * @return mixed
     */
    public function citationDuJourAction()
    {
        $citations = $this->getDoctrine()->getRepository(CitationV2::class)->findAllCitationByCitationDuJour(UtilityTools::getPageStart($this->CITATION_PAR_PAGE), $this->CITATION_PAR_PAGE , UtilityTools::getTri(true));
        // si on ne trouve pas de citations : dépassement des pages, redirection
        if ($citations == NULL || empty($citations))
            return $this->redirectToRoute('citation_front_home');
        $titre = "Citations du jour";
        $total = count($citations);
        $title = "Citation du jour (ainsi que les citations des jours précédents) - : Citation-inspiration.com - Votre dealer d'inspiration";
        $description = "La citation du jour ainsi que la liste des citations du jour des jours précédents.";
        return $this->render(
            'citations/front/citations_list.html.twig', [
            'total' => $total,
            'citations' => $citations,
            'titre' => $titre,
            'title' => $title,
            'description' => $description,
            'tri' => UtilityTools::getTriHuman(),
            'pagingHtml' => UtilityTools::pagination($this->CITATION_PAR_PAGE,
                UtilityTools::getPage(),
                $total,
                (isset($_GET['tri'])) ? "?tri=".$_GET['tri']."&" : "?"
            ),
        ]);
    }

    /**
     * @Route("/auteurs", name="auteurs_list")
     * @return mixed
     */
    public function auteursListAction()
    {
        if (!(isset($_GET['lettre'])) || (isset($_GET['lettre']) && preg_match("/[a-z]/", $_GET['lettre']) && strlen($_GET['lettre']) == 1))
        {
            $auteursList = array();
            $lettre = (!isset($_GET['lettre']))? "a" : $_GET['lettre'];
            $auteurs = $this->getDoctrine()->getRepository(Auteur::class)->findAllAuteurByGenreAndLetter($lettre, 1);
            $i = 0;
            $j= 0;
            $total = count($auteurs);
            $column = (int)($total / 4) - 1;
            foreach ($auteurs as $auteur) {
                $auteursList[$i][$j] = $auteur;
                if ($j == $column && $i < 3)
                {
                    $j = -1;
                    $i++;
                }
                $j++;
            }
            $title = "Liste des auteurs célèbres : Citation-inspiration.com";
            $description = "Liste des auteurs célèbres commencant par la lettre : ".$lettre;
            //faire les article (wordpress), (faire les j'aimes et commentaire ?) puis la home page et le panel admin puis creer des citations (users)
            return $this->render(
                'citations/front/auteurs_list.html.twig', [
                'auteurs' => $auteursList,
                'title' => $title,
                'description' => $description,
                'lettre' => $lettre,
                'total' => $total
            ]);
        }
        else
            return $this->redirectToRoute('citation_front_home');
    }

    /**
     * @Route("/personnages", name="personnages_list")
     * @return mixed
     */
    public function personnagesListAction()
    {
        if (!(isset($_GET['lettre'])) || (isset($_GET['lettre']) && preg_match("/[a-z]/", $_GET['lettre']) && strlen($_GET['lettre']) == 1))
        {
            $lettre = (!isset($_GET['lettre']))? "a" : $_GET['lettre'];
            $personnages = $this->getDoctrine()->getRepository(Auteur::class)->findAllAuteurByGenreAndLetter($lettre, 2);
            $i = 0;
            $j= 0;
            $total = count($personnages);
            $column = (int)(count($personnages) / 4) - 1;
            foreach ($personnages as $auteur) {
                $personnagesList[$i][$j] = $auteur;
                if ($j == $column && $i < 3)
                {
                    $j = -1;
                    $i++;
                }
                $j++;
            }
            $title = "Liste des personnages de fictions : Citation-inspiration.com";
            $description = "Liste des personnages de fictions (film, série) commencant par la lettre : ".$lettre;
            return $this->render(
                'citations/front/personnages_list.html.twig', [
                'personnages' => $personnagesList,
                'title' => $title,
                'description' => $description,
                'lettre' => $lettre,
                'total' => $total
            ]);
        }
        else
            return $this->redirectToRoute('citation_front_home');
    }

    /**
     * @Route("/thematiques", name="thematiques_list")
     * @return mixed
     */
    public function thematiquesListAction()
    {
        $thematiques = $this->getDoctrine()->getRepository(Tag::class)->findAllTag();

        $thematiquesArray = [];
        foreach ($thematiques as $v)
        {
            if ($v['total'] > 3)
                $thematiquesArray[] = $v;
        }
        $title = "Liste des thématiques populaire : Citation-inspiration.com";
        $description = "Liste des thématiques populaires";
        return $this->render(
            'citations/front/thematique_list.html.twig', [
            'thematiques' => $thematiquesArray,
            'title' => $title,
            'description' => $description,
        ]);
    }

    /**
     * @Route("/image/{url}", name="display_image")
     * @param string $url
     * @return BinaryFileResponse|null
     */
    public function imageAction(string $url)
    {
        $internaute = false;
        if (isset($_GET['internaute']))
            $internaute = true;
        //return null;
        $repository = ($internaute) ? $this->getDoctrine()->getRepository(CitationInternaute::class) : $this->getDoctrine()->getRepository(CitationV2::class);
        $citationOne = $repository->findOneBy(["url" => substr($url, 0, -4)]);
        $directory = __DIR__."/../../assets/upload/citation";
        if ($internaute)
        {
            $directory = __DIR__."/../../assets/upload/citationUser";
            $file = $directory."/".$url;
            if (file_exists($file))
            {
                $response = new BinaryFileResponse($file);
                return $response;
            }
            else
                return null;
        }
        if ($citationOne->getOld() == 1)
        {
            $file = $directory."/oldCitations/".$citationOne->getUrl().".png";
        }
        else
        {
            $folder = $directory."/".UtilityTools::removeAccent(trim(strtolower($citationOne->getAuteur()->getName())));

            $file = $folder."/".$citationOne->getUrl().".png";
            if (file_exists($file))
            {
                $response = new BinaryFileResponse($file);
                return $response;
            }
            //UtilityTools::var_dump($file);
            if (!(file_exists($file)))
                $file = $directory."/".trim(strtolower($citationOne->getAuteur()->getName()))."/".$citationOne->getUrl().".png";
            if (!(file_exists($file)))
                $file = $directory."/".trim($citationOne->getAuteur()->getName())."/".$citationOne->getUrl().".png";
            if (!(file_exists($file)))
                $file = $directory."/".trim($citationOne->getId());
            if (!(file_exists($file))) // si on ne trouve toujours pas
            {
                /*
                // on regarde si le nom de l'auteur est le même que le nom de l'image
                $aa = explode("-", $citationOne->getUrl());
                $aa = $aa[count($aa) - 1];
                $aa = str_replace("-".$aa, "", $citationOne->getUrl());
                $folder = $directory."/".UtilityTools::removeAccent(trim(strtolower($aa)));
                $file = $folder."/".$citationOne->getUrl().".png";
                if (file_exists($file))
                {
                    $em = $this->getDoctrine()->getManager();
                    $citationOne->getAuteur()->setName($aa);
                    $em->flush();
                }
                */
            }
        }

        $response = new BinaryFileResponse($file);
        return $response;
    }

    /**
     * @Route("/create", name="create_citation")
     */
    public function createCitation()
    {
        return $this->render('citations/front/create.html.twig');
    }

    /**
     * Génère le sitemap du site.
     *
     * @Route("/sitemap.{_format}", name="front_sitemap", Requirements={"_format" = "xml"})
     */
    public function siteMapAction(SiteMap $sitemap)
    {
        return $this->render(
            'citations/front/sitemap.xml.twig',
            ['urls' => $sitemap->generer()]
        );
    }
}