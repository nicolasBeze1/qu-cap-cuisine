<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyAnswersRepository")
 */
class SurveyAnswers
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
     * @var SurveyUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SurveyUser")
     * @ORM\JoinColumn(nullable=false)
     */
    private $surveyUser;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $finishedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $success;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $nbCorrectAnswer;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $questions;

    public function __construct()
    {
        $this->finishedAt = new \DateTime();
        $this->status = false;
        $this->success = false;
        $this->nbCorrectAnswer = 0;
        $this->questions = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveyUser(): SurveyUser
    {
        return $this->surveyUser;
    }

    public function setSurveyUser(SurveyUser $surveyUser): void
    {
        $this->surveyUser = $surveyUser;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getFinishedAt(): \DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getNbCorrectAnswer(): int
    {
        return $this->nbCorrectAnswer;
    }

    public function setNbCorrectAnswer(int $nbCorrectAnswer): void
    {
        $this->nbCorrectAnswer = $nbCorrectAnswer;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function addQuestion(int $idQuestion): void
    {
        array_push($this->questions, $idQuestion);
    }
}
