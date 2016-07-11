<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoriesRepository")
 */
class Categories
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
     *
     * @ORM\Column(name="category_name", type="string", length=150)
     */
    private $categoryName;

    /**
     * @var text
     *
     * @ORM\Column(name="category_description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="category_img", type="string", length=300)
     */
    private $image;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="active", type="string", columnDefinition="enum('yes', 'no')")
     */
    private $active;

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
     * Set category name
     *
     * @param string $categoryName
     * @return this
     */
    public function setCategoryName($categoryName)
    {
    	$this->categoryName = $categoryName;

    	return $this;
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getCategoryName()
    {
    	return $this->categoryName;
    }

    /**
     * Set category description
     * 
     * @param text $description
     * @return this
     */
    public function SetDescription($description)
    {
    	$this->description = $description;

    	return $this;
    }

    /**
     * Get category description
     *
     * @return text
     */
    public function getDescription()
    {
    	return $this->description;
    }

    /**
     * Set category image
     *
     * @param string $image
     * @return this
     */
    public function setImage($image)
    {
    	$this->image = $image;

    	return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
    	return $this->image;
    }

    /**
     * Set created date
     * 
     * @param datetime $createdDate
     * @return this
     */
    public function setCreatedDate($createdDate)
    {
    	$this->createdDate = $createdDate;

    	return $this;
    }

    /**
     * Get created date
     *
     * @return datetime
     */
    public function getCreatedDate()
    {
    	return $this->createdDate;
    }

    /**
     * Set active
     *
     * @param string $active
     * @return this
     */
    public function setActive($active)
    {
    	$this->active = $active;

    	return $this;
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
    	return $this->active;
    }
}