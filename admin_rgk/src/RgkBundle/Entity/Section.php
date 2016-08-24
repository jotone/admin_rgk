<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Section
 *
 * @ORM\Table(name="section")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\SectionRepository")
 */
class Section
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var Section
     * @ORM\ManyToOne(targetEntity="Section")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parentSection", referencedColumnName="id",nullable=true,onDelete="CASCADE")
     * })
     */
    private $parentSection;

    /**
     * @var boolean
     * @ORM\Column(name="folder", type="boolean")
     */
    private $folder=false;

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
     * Set title
     *
     * @param string $title
     *
     * @return Section
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set parentSection
     *
     * @param Section $target
     * @return Section
     */
    public function setParentSection(Section $target = null)
    {
        $this->parentSection = $target;

        return $this;
    }

    /**
     * Get parentSection
     *
     * @return Section
     */
    public function getParentSection()
    {
        return $this->parentSection;
    }


    /**
     * Set folder
     *
     * @param boolean $folder
     * @return Section
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return boolean
     */
    public function getFolder()
    {
        return $this->folder;
    }
}

