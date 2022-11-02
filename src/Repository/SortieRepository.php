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

    public function findByArgs(?Participant $user,?Campus $campus,?\DateTime $datedebut,?\DateTime $datefin,?string $nom,bool $condition1,bool $condition2,bool $condition3,bool $condition4)
    {
      $query = "SELECT s FROM \App\Entity\Sortie s ";
      $compteur=0;
      $args=[];
      if(isset($user)&& ($condition2||$condition3)){
          if($condition2){
              $args[$compteur]="INNER JOIN s.idParticipant sp WHERE sp.id IN(".$user->getId().") ";
              $compteur++;
              $condition3=false;
            }
            if($condition3){
                $args[$compteur]="INNER JOIN s.idParticipant sp WHERE sp.id NOT IN(".$user->getId().")";
                $compteur++;
            }
      }

      if(isset($campus)){
          $args[$compteur]="s.idSiteOrganisateur=".$campus->getId()." ";
          $compteur++;

      }
        if(isset($datedebut)||isset($datefin)){
           $args[$compteur]="s.dateHeureDebut BETWEEN '".$datedebut->format('Y-m-d H:i:s')."' AND '".$datefin->format('Y-m-d H:i:s')."' ";
           $compteur++;
        }
        if(isset($nom)&&$nom!=""){
            $args[$compteur]="s.nom LIKE '%".$nom."%' ";
            $compteur++;
        }
        if($condition1){
            $args[$compteur]="s.idOrganisateur=".$user->getId()." ";
            $compteur++;
        }
        $actual=new \DateTime();
        $actual->format('Y-m-d H:i:s');
        if($condition4) {
            $args[$compteur]="s.dateHeureDebut < '".$actual->format('Y-m-d H:i:s')."' ";
            $compteur++;
        }
        for($i=0;$i<$compteur;$i++){
            if($i==0){
                if(str_starts_with($args[$i],"INNER")){
                    $query.=$args[$i];
                }
                else{
                    $query.="WHERE ".$args[$i];
                }
            }
            else{
                $query.="AND ".$args[$i];
            }
        }
        return $this->getEntityManager()->createQuery($query)->getResult();
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
