<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\ProductRepository")
 */
class Product
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
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var Section
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Section")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="section", referencedColumnName="id",nullable=false,onDelete="CASCADE")
     * })
     */
    private $section;

    /**
     * @ORM\OneToMany(targetEntity="Price", mappedBy="product",cascade={"persist"},orphanRemoval=true)
     */
    protected $prices;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Product
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
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * Set section
     *
     * @param \RgkBundle\Entity\Section $target
     * @return Product
     */
    public function setSection(\RgkBundle\Entity\Section $target = null)
    {
        $this->section = $target;

        return $this;
    }

    /**
     * Get section
     *
     * @return \RgkBundle\Entity\Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Add prices
     *
     * @param \RgkBundle\Entity\Price $prices
     * @return Product
     */
    public function addPrices(\RgkBundle\Entity\Price $prices)
    {
        $prices->setProduct($this);
        $this->prices[] = $prices;

        return $this;
    }

    /**
     * Remove prices
     *
     * @param \RgkBundle\Entity\Price $prices
     */
    public function removePrices(\RgkBundle\Entity\Price $prices)
    {
        $this->prices->removeElement($prices);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Get percent
     *
     * @return number
     */
    function getPercent(){
        if(!$this->getPrices() || !$this->getPrice())
            return 0;


        $count = 0;
        $sum = 0;
        /**
         * @var Price $price
         */
        foreach ($this->getPrices()->toArray() as $price){
            if($price->getPrice()){
                $sum += $price->getPrice();
                $count++;
            }
        }
        if($count==0)
            return 0;

        $sum = $sum/$count; // take middle price
        return round(($this->getPrice()/$sum - 1)*100);
    }

    /**
     * Get label
     *
     * @return string
     */
    function getLabel(){
        $percent = $this->getPercent();
        switch (true){
            case ($percent >50):
                return '#f74343';
            case ($percent >0):
                return '#f7a743';
            case ($percent > -50):
                return '#e6e831';
            default:
                return '#40ae49';
        }
    }
}

