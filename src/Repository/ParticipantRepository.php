<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Exception\UsernameNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Exception\UserNotActiveException;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findUserByPseudo(string $pseudo): ?Participant{
        return $this->findOneBy(['pseudo' => $pseudo]);
    }

    public function loadUserByIdentifier(string $usernameOrEmail) : ?Participant{

        $Participant = $this->createQueryBuilder('u')
            ->where('u.email = :query')
            ->orWhere('u.pseudo = :query')
            ->setParameter('query', $usernameOrEmail)
            ->getQuery()
            ->getOneOrNullResult();

        //dd($Participant);

        if (!$Participant | $Participant == null ) {
            throw new UsernameNotFoundException();
        }
        if (!$Participant->isActif()) {
            throw new UserNotActiveException();
        }

        return $Participant;
    }
}
