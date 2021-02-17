<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return array
     */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('t')
            ->addSelect('u')
            ->leftJoin('t.user', 'u')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findTasksIsDoneWithUser(): array
    {
        return $this->createQueryBuilder('t')
            ->addSelect('u')
            ->leftJoin('t.user', 'u')
            ->Where('t.isDone = :val')
            ->setParameter('val', true)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
