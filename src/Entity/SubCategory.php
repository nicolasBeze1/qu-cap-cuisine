<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryRepository")
 */
class SubCategory
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
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var Survey[]|Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="Survey",
     *      mappedBy="subCategory"
     * )
     */
    private $surveys;

    /** @var int */
    private $nbSurveySuccess;

    /** @var int */
    private $nbSuccess;

    public function __construct()
    {
        $this->surveys = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): void
    {
        $survey->setSubCategory($this);
        if (!$this->surveys->contains($survey)) {
            $this->surveys->add($survey);
        }
    }

    public function removeSurvey(Survey $survey): void
    {
        $this->surveys->removeElement($survey);
    }

    public function getNbSurveySuccess(): int
    {
        return $this->nbSurveySuccess;
    }

    public function initializeIncrement(): void
    {
        $this->nbSurveySuccess = 0;
        $this->nbSuccess = 0;
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

    public function getSurveysActive(): Collection
    {
        return $this->getSurveys()->filter(function (Survey $survey){
            return $survey->isStatus();
        });
    }

    public function getCompletionPercent(): int
    {
        if($this->getNbSuccess() >= $this->getSurveysActive()->count()){
            return 100;
        }
        return ceil(($this->getNbSuccess()/$this->getSurveysActive()->count()) * 100);
    }
}
