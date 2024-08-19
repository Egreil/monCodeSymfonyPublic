<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findSorties(){
        return $this->createQueryBuilder('s')
            ->select('s', 'campus', 'lieu', 'etat', 'organisateur', 'participants')
            ->leftJoin('s.campus', 'campus')
            ->leftJoin('s.lieu', 'lieu')
            ->leftJoin('s.etat', 'etat')
            ->leftJoin('s.organisateur', 'organisateur')
            ->leftJoin('s.participants', 'participants')
            ->where('etat.libelle NOT IN (:excludedStates)') // Exclure les états "historisée" et "annulée"
            ->setParameter('excludedStates', ['Historisée', 'Annulée','Crée'])
            //->andWhere('(etat.libelle = :etatCree AND organisateur = :user) OR organisateur = :user')  //Sélectionner les états "créé" et vérifier que l'utilisateur courant est l'organisateur
           //->setParameter('etatCree', 'Créé')
            //->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findSortiesAHistoriser()
    {
        //Option3: native query
        //Création de l'identificateur de la sortie
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        //On dit à l'identificateur que la sortie sera de type Sortie
        $rsm->addRootEntityFromClassMetadata('App\Entity\Sortie', 's');

//      Ligne à ajouter si on veut signaler que la table de resultat proviens d'une jointure et isoler les résultats
//      inutile ici puisqu'on ne prend que les colonnes de Sortie
//        $rsm->addJoinedEntityFromClassMetadata('App\Entity\Etat', 'e', 's', 'id', array('id' => 'etat_id'));

        //Recupération des éléments de la sortie uniquement après jointure avec l'état pour pouvoir récupérer un tableau de Sortie
        $sql="SELECT s.* FROM sortie as s join  etat  as e on e.id=s.etat_id  WHERE (TIMESTAMPDIFF(MONTH,s.date_heure_debut, NOW()))>=1 AND e.libelle<>'Historisée';";
        $query=$this->getEntityManager()->createNativeQuery($sql,$rsm);
        
        //Execution de la requête
        $result=$query->getResult();
        return $result;
    }

    public function findSortiesByCityAndPlace(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.lieu', 'lieu')
            ->leftJoin('lieu.ville','ville')
            ->addSelect('lieu')
            ->addSelect('ville')
            ->getQuery()
            ->getResult();
    }


    // Recherche des sorties commencées par création d'un Mapping du type sortant 
    //pour comprendre comment fonctionne Doctrine et utiliser du MySQL natif
    public function findSortiesCommencees(){

        // On crée le Mapping de la sortie
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        //On dit que le Mapping correspond à l'entity Sortie et a pour alias s
        $rsm->addRootEntityFromClassMetadata('App\Entity\Sortie', 's');
        $sql="SELECT s.* FROM sortie as s join  etat  as e on s.etat_id=e.id  
                WHERE (TIMESTAMPDIFF(MINUTE,s.date_heure_debut, NOW()))>=0 AND (e.libelle='Cloturée' OR e.libelle='Ouverte');";
        $query=$this->getEntityManager()->createNativeQuery($sql,$rsm);
        $result=$query->getResult();
        return $result;
    }

    public function findSortiesPassees(){
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Sortie', 's');
        $sql="SELECT s.* FROM sortie as s join  etat  as e on s.etat_id=e.id  
                WHERE (TIMESTAMPDIFF(MINUTE,ADDDATE(s.date_heure_debut, INTERVAL s.duree HOUR), NOW()))>=0 AND e.libelle='Activité en cours';";
        $query=$this->getEntityManager()->createNativeQuery($sql,$rsm);
        $result=$query->getResult();
        return $result;
    }

}
