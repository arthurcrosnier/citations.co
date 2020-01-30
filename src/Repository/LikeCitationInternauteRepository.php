<?php

namespace App\Repository;

use App\Entity\likeCitationInternaute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LikeCitationInternaute|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikeCitationInternaute|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikeCitationInternaute[]    findAll()
 * @method LikeCitationInternaute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikeCitationInternauteRepository extends ServiceEntityRepository
{
    /**
     * likeCitationInternauteRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikeCitationInternaute::class);
    }

    /**
     * @param $ip
     * @return int
     */
    public function countLikeIpToday($ip): int
    {
        $dateToday = date('Y-m-d')." 00:00:00";
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT DISTINCT id
        FROM LikeCitationInternaute l
        WHERE l.like_date >= :dateToday AND ip = :ip;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array("dateToday" => $dateToday, "ip" => $ip));

        return count($stmt->fetchAll());
    }

    /**
     * @param $ip
     * @param $id
     * @return bool
     */
    public function ipLikedCitation($ip, $id): bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT DISTINCT id
        FROM LikeCitationInternaute l
        WHERE ip = :ip AND l.citation_id = :id;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array("ip" => $ip, "id" => $id));

        return (count($stmt->fetchAll()) > 0) ? true : false;
    }
}
