<?php
namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @return array
     */
    public function findAllTag(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT ct.tag_id, COUNT(ct.tag_id) as total, t.name FROM `citation_tag` ct 
                LEFT JOIN tag t on ct.tag_id = t.id group by tag_id ORDER BY t.name';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array());

        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function findAllTagByPop(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT ct.tag_id, COUNT(ct.tag_id) as total, t.name FROM `citation_tag` ct 
                LEFT JOIN tag t on ct.tag_id = t.id group by tag_id ORDER BY total DESC LIMIT 0, 100';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array());

        return $stmt->fetchAll();
    }
}
