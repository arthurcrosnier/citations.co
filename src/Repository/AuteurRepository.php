<?php

namespace App\Repository;

use App\Entity\Auteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Auteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auteur[]    findAll()
 * @method Auteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auteur::class);
    }

    /**
     * @param $letter
     * @param $type
     * @return array
     */
    public function findAllAuteurByGenreAndLetter($letter, $type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT DISTINCT *
        FROM auteur a
        WHERE a.type = :type AND a.name LIKE "'.$letter.'%"
        ORDER BY a.name ASC';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array("type" => $type));

        return $stmt->fetchAll();
    }

    /**
     * @param $type
     * @return array
     */
    public function findAllAuteurByPop($type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT a.id, (SELECT count(id) FROM citation_v2 WHERE auteur_id = a.id) as total, a.name FROM auteur a
                WHERE a.type = :type group by a.id ORDER BY total DESC LIMIT 0, 100';

        $stmt = $conn->prepare($sql);
        $stmt->execute(array("type" => $type));

        return $stmt->fetchAll();
    }
}
