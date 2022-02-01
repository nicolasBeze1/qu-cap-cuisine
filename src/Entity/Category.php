<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var SubCategory[]|Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="SubCategory",
     *      mappedBy="category",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $subCategories;

    /** @var int */
    private $nbSurveySuccess;

    /** @var int */
    private $nbSuccess;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
        $this->initializeIncrement();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): void
    {
        $subCategory->setCategory($this);
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories->add($subCategory);
        }
    }

    public function removeSubCategory(SubCategory $subCategory): void
    {
        $this->subCategories->removeElement($subCategory);
    }

    public function initializeIncrement(): void
    {
        $this->nbSurveySuccess = 0;
        $this->nbSuccess = 0;
    }

    public function getNbSurveySuccess(): int
    {
        return $this->nbSurveySuccess;
    }

    public function incrementNbSurveySuccess(int $nb): void
    {
        $this->nbSurveySuccess = $this->nbSurveySuccess + $nb;
    }

    public function getNbSuccess(): int
    {
        return $this->nbSuccess;
    }

    public function incrementNbSuccess(bool $success): void
    {
        if($success){
            $this->nbSuccess ++;
        }
    }

    public function getTotalSurveys(): int
    {
        $totalSurveys = 0;
        /** @var SubCategory $subCategory */
        foreach ($this->getSubCategories() as $subCategory){
            $totalSurveys += $subCategory->getSurveysActive()->count();
        }
        return $totalSurveys;
    }
    public function getCompletionPercent(): int
    {
        $totalSurveys = $this->getTotalSurveys();
        if($this->getNbSuccess() >= $totalSurveys){
            return 100;
        }
        return ceil(($this->getNbSuccess()/$totalSurveys) * 100);
    }
}
