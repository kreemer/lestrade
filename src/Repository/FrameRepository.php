<?php

namespace App\Repository;

use App\Entity\Frame;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Frame>
 *
 * @method Frame|null find($id, $lockMode = null, $lockVersion = null)
 * @method Frame|null findOneBy(array $criteria, array $orderBy = null)
 * @method Frame[]    findAll()
 * @method Frame[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FrameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Frame::class);
    }

    public function add(Frame $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Frame $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Frame[] Returns an array of Frame objects
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.project', 'p')
            ->andWhere('p.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('f.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Frame[]
     */
    public function findByStartAndEnd(User $user, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.project', 'p')
            ->andWhere('p.createdBy = :user')
            ->andWhere('f.startAt > :start')
            ->andWhere('f.endAt < :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('f.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Frame
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
