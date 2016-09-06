<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Rival
 *
 * @ORM\Table(name="rival")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\RivalRepository")
 */
class Rival
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
     * @ORM\Column(name="name", type="string", length=255, unique=false)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="url", type="string", length=255, unique=false)
     */
    private $url;

    /**
     * @ORM\OneToMany(targetEntity="Code", mappedBy="rival",cascade={"persist"},orphanRemoval=true)
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(name="sections", type="string", length=2048, unique=false, nullable=true)
     */
    private $sections;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->code = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Rival
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Rival
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Add code
     *
     * @param \RgkBundle\Entity\Code $code
     * @return Rival
     */
    public function addCode(\RgkBundle\Entity\Code $code)
    {
        $code->setRival($this);
        $this->code[] = $code;

        return $this;
    }

    /**
     * Remove code
     *
     * @param \RgkBundle\Entity\Code $code
     */
    public function removeCode(\RgkBundle\Entity\Code $code)
    {
        $this->code->removeElement($code);
    }

    /**
     * Get code
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * Set sections
     *
     * @param string $Sections
     *
     * @return Rival
     */
    public function setSections($Sections)
    {
        $this->sections = $Sections;

        return $this;
    }

    /**
     * Get sections
     *
     * @return string
     */
    public function getSections()
    {
        return $this->sections;
    }
}

