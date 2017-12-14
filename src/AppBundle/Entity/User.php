<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="Entrez un nom d'utitlisateur svp.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=1,
     *     max=3,
     *     minMessage="Age trop court.",
     *     maxMessage="Age trop long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $age;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set age
     *
     * @param integer $age
     *
     * @return User
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }
}
