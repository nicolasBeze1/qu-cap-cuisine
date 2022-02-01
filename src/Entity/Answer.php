<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
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
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Question")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $answer;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $success;

    public function __construct()
    {
        $this->success = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveyUser(): ?SurveyUser
    {
        return $this->surveyUser;
    }

    public function setSurveyUser(?SurveyUser $surveyUser): void
    {
        $this->surveyUser = $surveyUser;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }
}
