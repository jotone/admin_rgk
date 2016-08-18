<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Symfony\Component\Yaml\Parser;
/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createTokenAt", type="datetime", nullable=true)
     */
    private $createTokenAt;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parentUser", referencedColumnName="id",nullable=true,onDelete="SET NULL")
     * })
     */
    private $parentUser;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createTokenAt
     *
     * @param \DateTime $createTokenAt
     * @return User
     */
    public function setCreateTokenAt($createTokenAt)
    {
        $this->createTokenAt = $createTokenAt;

        return $this;
    }

    /**
     * Get createTokenAt
     *
     * @return \DateTime
     */
    public function getCreateTokenAt()
    {
        return $this->createTokenAt;
    }

    /**
     * Set parentUser
     *
     * @param User $target
     * @return User
     */
    public function setParentSection(User $target = null)
    {
        $this->parentUser = $target;

        return $this;
    }

    /**
     * Get parentUser
     *
     * @return User
     */
    public function getParentUser()
    {
        return $this->parentUser;
    }
}

