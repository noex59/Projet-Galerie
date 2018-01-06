<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository
{
    public function findByNothingUserForMenu(){
        $qb = $this->createQueryBuilder('u');

        $qb->select("u.id, u.username")
        ->orderby('u.username', 'asc');

        return $qb->getQuery()->getArrayResult();
    }

    public function findUsers(){
        $qb = $this->createQueryBuilder('u');

        $qb->select("u.id, u.username, u.email, u.enabled")
        ->where("u.username != 'admin'")
        ->orderby('u.username', 'asc');

        return $qb->getQuery()->getArrayResult();
    }

    public function findById($id){
        $qb = $this->createQueryBuilder('u');

        $qb->select("*")
        ->where("u.id = :id")
        ->setParameters(array('id' => $id));

        return $qb->getQuery()->getResult();
    }
}
