<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository
{
    public function findFiveUser(){
        $qb = $this->createQueryBuilder('u');

        $qb->select("u.id, u.username")
        ->orderby('u.username', 'asc')
        ->setMaxResults(5);

        return $qb->getQuery()->getArrayResult();
    }
}
