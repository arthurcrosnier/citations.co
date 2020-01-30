<?php
namespace App\Service;

use App\Entity\Article;
use App\Entity\Auteur;
use App\Entity\CitationV2;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SiteMap
{
    private $router;
    private $em;

    public function __construct(UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

/**
* Génère l'ensemble des valeurs des balises <url> du sitemap.
    *
    * @return array Tableau contenant l'ensemble des balise url du sitemap.
    */
    public function generer()
    {
        $urls = [];

        $repoCitation = $this->em->getRepository(CitationV2::class);
        $citations = $repoCitation->findAll();
        $auteurs = $this->em->getRepository(Auteur::class)->findAll();
        $thematiques = $this->em->getRepository(Tag::class)->findAll();
        $articles = $this->em->getRepository(Article::class)->findAll();

        foreach ($citations as $citation) {
            $urls[] = [
            'loc' => $this->router->generate('citation_unique', ['id' => $citation->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ];
        }
        foreach ($auteurs as $auteur) {
            if ($auteur->getType() == 1)
            {
                $urls[] = [
                    'loc' => $this->router->generate('citations_auteur_front_theme', ['slug' => $auteur->getName()], UrlGeneratorInterface::ABSOLUTE_URL)
                ];
            }
            if ($auteur->getType() == 2)
            {
                $urls[] = [
                    'loc' => $this->router->generate('citations_personnage_front_theme', ['slug' => $auteur->getName()], UrlGeneratorInterface::ABSOLUTE_URL)
                ];
            }
        }
        foreach ($thematiques as $tag) {
            $urls[] = [
                'loc' => $this->router->generate('citations_thematique_front_theme', ['slug' => $tag->getName()], UrlGeneratorInterface::ABSOLUTE_URL)
            ];
        }
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $this->router->generate('blogShow', ['article' => $article->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ];
        }
        return $urls;
    }
}