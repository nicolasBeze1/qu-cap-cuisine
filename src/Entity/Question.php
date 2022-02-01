<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 */
class Question
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
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $answer;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $answerHelp;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $answerDetail;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey")
     * @ORM\JoinColumn(nullable=false)
     */
    private $survey;


    /**
     * @Vich\UploadableField(mapping="question_image", fileNameProperty="image.name", size="image.size", mimeType="image.mimeType", originalName="image.originalName", dimensions="image.dimensions")
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
        $this->image = new EmbeddedFile();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreRemove()
     */
    public function setStatusAfterRemove(): void
    {
        if($this->getSurvey()->getQuestions()->count() - 1 < $this->getSurvey()->getQuestionsToAsk()){
            $this->getSurvey()->setStatus(false);
        }
    }

    /**
     * @ORM\PrePersist()
     */
    public function setStatusAfterNew(): void
    {
        if(($this->getSurvey()->getQuestions()->count() + 1) >= $this->getSurvey()->getQuestionsToAsk()){
            $this->getSurvey()->setStatus(true);
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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }

    public function getAnswerHelp(): ?string
    {
        return $this->answerHelp;
    }

    public function setAnswerHelp(?string $answerHelp): void
    {
        $this->answerHelp = $answerHelp;
    }

    public function getAnswerDetail(): ?string
    {
        return $this->answerDetail;
    }

    public function setAnswerDetail(?string $answerDetail): void
    {
        $this->answerDetail = $answerDetail;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
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
