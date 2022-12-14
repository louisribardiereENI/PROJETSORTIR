<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;


/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{

    public function loadUserByIdentifier(string $pseudoOrEmail): ?Participant
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\Participant u
                WHERE u.pseudo = :query
                OR u.email = :query'
        )
            ->setParameter('query', $pseudoOrEmail)
            ->getOneOrNullResult();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function save(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }
    public function setActive(Participant $entity,bool $actif):void{
        $entity->setActif($actif);
        $this->save($entity,true);
    }
    public function setAdministration(Participant $entity,bool $admin):void{
        $entity->setAdministrateur($admin);
        if($admin){
            $entity->setRoles(array('ADMIN'));
            $this->save($entity,true);
        }
        else{
            $entity->setRoles(array(''));
            $this->save($entity,true);
        }

    }
    public function deleteParticipant(Participant $entity):void{
        $this->remove($entity,true);
    }

    public function findByEmail(string $email):Participant{
        return $this->findOneBy(array('email'=>$email));
    }
    public function isActiveByMail(string $email):bool{
        return $this->findByEmail($email)->isActif();
    }

    public function setPicture(Participant $entity,FormInterface $form):void{
        $picture=$form->get('photoParticipant')->getData();
            if($picture){
                $picture->move('img/avatar',$entity->getId().'.jpg');
                $entity->setPhotoParticipant($entity->getId().'.jpg');
            }
            else{
                $entity->setPhotoParticipant('default.jpg');
            }
        $this->save($entity,true);
    }








//    /**
//     * @return Participant[] Returns an array of Participant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Participant
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
