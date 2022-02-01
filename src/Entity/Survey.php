<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 */
class Survey
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
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $difficulty;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $successPercent;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $questionsToAsk;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @var Lesson|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Lesson")
     * @ORM\JoinColumn(nullable=true)
     */
    private $lesson;

    /**
     * @var SubCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subCategory;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var Question[]|Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="Question",
     *      mappedBy="survey",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $questions;

    /** @var int */
    private $nbSurveySuccess;

    /**
     * @Vich\UploadableField (mapping="survey_image", fileNameProperty="image.name", size="image.size", mimeType="image.mimeType", originalName="image.originalName", dimensions="image.dimensions")
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTimeInterface|null
     */
    private $updatedAt;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->status = false;
        $this->questions = new ArrayCollection();
        $this->nbSurveySuccess = 0;
        $this->updatedAt = new \DateTime();
        $this->image = new EmbeddedFile();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setStatusAfterChange(): void
    {
        if ($this->getQuestions()->count() < $this->getQuestionsToAsk()) {
            $this->setStatus(false);
        }
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

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(?int $difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    public function getSuccessPercent(): ?int
    {
        return $this->successPercent;
    }

    public function setSuccessPercent(?int $successPercent): void
    {
        $this->successPercent = $successPercent;
    }

    public function getQuestionsToAsk(): ?int
    {
        return $this->questionsToAsk;
    }

    public function setQuestionsToAsk(?int $questionsToAsk): void
    {
        $this->questionsToAsk = $questionsToAsk;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(Lesson $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): void
    {
        $this->subCategory = $subCategory;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): void
    {
        $question->setSurvey($this);
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
        }
    }

    public function removeQuestion(Question $question): void
    {
        $this->questions->removeElement($question);
    }

    public function nbAnswersOfSuccess(): int
    {
        return ceil($this->getQuestionsToAsk() * ($this->getSuccessPercent() / 100));
    }

    public function getNbSurveysToPass(): int
    {
        return ceil(
            $this->getQuestions()->count() /
            $this->getQuestionsToAsk()
        );
    }

    public function getCompletionPercent(): int
    {
        if ($this->getNbSurveySuccess() >= $this->getNbSurveysToPass()) {
            return 100;
        }
        return ceil(($this->getNbSurveySuccess() / $this->getNbSurveysToPass()) * 100);
    }

    public function getNbSurveySuccess(): ?int
    {
        return $this->nbSurveySuccess;
    }

    public function setNbSurveySuccess(?int $nbSurveySuccess): void
    {
        $this->nbSurveySuccess = $nbSurveySuccess;
    }

    /**
     * @param File|UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(EmbeddedFile $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }
}
