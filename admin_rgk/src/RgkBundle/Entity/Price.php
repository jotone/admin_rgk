<?php

namespace RgkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Price
 *
 * @ORM\Table(name="price")
 * @ORM\Entity(repositoryClass="RgkBundle\Repository\PriceRepository")
 */
class Price
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
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime",nullable=true)
     */
    private $date;

    /**
     * @var float
     * @ORM\Column(name="price", type="float",nullable=true)
     */
    private $price;

    /**
     * @var Product
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product", referencedColumnName="id",nullable=false,onDelete="CASCADE")
     * })
     */
    private $product;

    /**
     * @var Code
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Code")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code", referencedColumnName="id",nullable=true,onDelete="CASCADE")
     * })
     */
    private $code;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="url", type="string", length=4096)
     */
    private $url;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Price
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Price
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
     * Set product
     *
     * @param \RgkBundle\Entity\Product $product
     * @return Price
     */
    public function setProduct(\RgkBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \RgkBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set code
     *
     * @param \RgkBundle\Entity\Code $code
     * @return Price
     */
    public function setCode(\RgkBundle\Entity\Code $code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return \RgkBundle\Entity\Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Price
     */
    public function setUrl($url = '')
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
     * Get percent
     *
     * @return number
     */
    function getPercent(){
        if(!$this->getProduct() || !$this->getProduct()->getPrice() || !$this->getPrice())
            return 0;

        return round(($this->getPrice()/$this->getProduct()->getPrice() - 1)*100);
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Price
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
     * Get label
     *
     * @return string
     */
    function getLabel(){
        $percent = $this->getPercent();
        switch (true){
            case ($percent >50):
                return '#40ae49';
            case ($percent >0):
                return '#e6e831';
            case ($percent > -50):
                return '#f7a743';
            default:
                return '#f74343';
        }
    }
}

