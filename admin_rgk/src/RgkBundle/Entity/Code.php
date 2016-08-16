<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Code
 *
 * @ORM\Table(name="code")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\CodeRepository")
 */
class Code
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="code", type="string", length=2048)
     */
    private $code;

    /**
     * @var Rival
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Rival")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rival", referencedColumnName="id",nullable=true,onDelete="CASCADE")
     * })
     */
    private $rival;

    /**
     * @var boolean
     * @ORM\Column(name="def", type="boolean")
     */
    private $def=false;

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
     * Set code
     *
     * @param string $code
     *
     * @return Code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set rival
     *
     * @param \RgkBundle\Entity\Rival $target
     * @return Code
     */
    public function setRival(\RgkBundle\Entity\Rival $target = null)
    {
        $this->rival = $target;

        return $this;
    }

    /**
     * Get rival
     *
     * @return \RgkBundle\Entity\Rival
     */
    public function getRival()
    {
        return $this->rival;
    }


    /**
     * Set def
     *
     * @param boolean $def
     * @return Code
     */
    public function setDef($def)
    {
        $this->def = $def;

        return $this;
    }

    /**
     * Get def
     *
     * @return boolean
     */
    public function getDef()
    {
        return $this->def;
    }
}

