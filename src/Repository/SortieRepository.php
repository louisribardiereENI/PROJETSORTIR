<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByArgs(?Participant $user,?Campus $campus,?Date $datedebut,?Date $datefin,?string $nom,bool $condition1,bool $condition2,bool $condition3,bool $condition4): array
    {
      $query = $this->createQueryBuilder('s');
      if(isset($user)&& ($condition2||$condition3)){
          if($condition2){
              $query->innerJoin('sortieParticipant','sp','s.id=sp.sortieId');
                $query->where('sp.participantId='.$user->getId());
                $condition3=false;
            }
            if($condition3){
                $query->innerJoin('sortieParticipant','sp','s.id=sp.sortieId');
                $query->where('sp.participantId!='.$user->getId());
            }
      }
      if(isset($campus)){
          $query->where('s.idCampus ='.$campus->getId());

      }
        if(isset($datedebut)||isset($datefin)){
            $query->where('s.dateHeureDebut BETWEEN '.$datedebut.' AND '.$datefin);
      }
        if(isset($nom)&&$nom!=""){
            $query->where('s.nom LIKE "%'.$nom.'"');
        }
        if( $condition1){
            $query->where('idOrganisateur='.$user->getId());
        }
        $actual=new \DateTime();
        $actual->format('Y-m-d h:s:z');
        if($condition4) {
            $query->where('s.dateHeureDebut <' . $actual);
        }
        throw new Exception($nom);
       // return $query->getQuery()->execute();
    }
//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
