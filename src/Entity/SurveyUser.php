<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyUserRepository")
 */
class SurveyUser
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey")
     * @ORM\JoinColumn(nullable=false)
     */
    private $survey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $finishedAt;

    /**
     * @var bool
     * Si toutes les questions ont été posées et le nombre de bonne réponse atteinte
     * @ORM\Column(type="boolean")
     */
    private $success;

    /**
     * @var int
     * nombre de réussites d'affilée après le succès
     * @ORM\Column(type="integer")
     */
    private $successInARow;

    /**
     * @var Answer[]|Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="Answer",
     *      mappedBy="surveyUser",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $answers;

    /**
     * @var SurveyAnswers[]|Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="SurveyAnswers",
     *      mappedBy="surveyUser",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $surveyAnswers;

    public function __construct()
    {
        $this->success = false;
        $this->successInARow = 0;
        $this->finishedAt = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->surveyAnswers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function getFinishedAt(): \DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getSuccessInARow(): ?int
    {
        return $this->successInARow;
    }

    public function setSuccessInARow(?int $successInARow): void
    {
        $this->successInARow = $successInARow;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): void
    {
        $answer->setSurveyUser($this);
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
        }
    }

    public function getSurveyAnswers(): Collection
    {
        return $this->surveyAnswers;
    }

    public function addSurveyAnswers(SurveyAnswers $sa): void
    {
        $sa->setSurveyUser($this);
        if (!$this->surveyAnswers->contains($sa)) {
            $this->surveyAnswers->add($sa);
        }
    }

    public function getNbCorrectSurvey(): int
    {
        return $this->getSurveyAnswers()->filter(function (SurveyAnswers $surveyAnswers){
            return $surveyAnswers->isSuccess();
        })->count();
    }
}
