<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\SurveyAnswers;
use App\Entity\SurveyUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SurveyAnswersService
{
    /** @var SurveyAnswers  */
    private $surveyAnswers;

    /** @var SurveyUser  */
    private $surveyUser;

    public function __construct(SurveyAnswers $surveyAnswers)
    {
        $this->surveyAnswers = $surveyAnswers;
        $this->surveyUser = $surveyAnswers->getSurveyUser();
    }

    public function getRandomQuestion(): Question
    {
        $questionsNotAsked = $this->getQuestionsNotAsked();
        if(!$questionsNotAsked->isEmpty()) {
            return $questionsNotAsked[array_rand($questionsNotAsked->toArray())];
        }
        $questions= $this->getQuestionsNotUseInActualSurvey();
        $echecQuestions = $this->getEchecQuestions($questions);
        $questions = !$echecQuestions->isEmpty() ? $echecQuestions : $questions;

        return $questions[array_rand($questions->toArray())];
    }

    public function getQuestionsNotAsked(): Collection
    {
        if($this->getNbAnswerFinished() >= $this->getQuestions()->count()){
            return new ArrayCollection();
        }

        return $this->getQuestions()->filter(function(Question $question){
            return !in_array($question->getId(), $this->surveyUser->getAnswers()->map(function(Answer $answer){
                return $answer->getQuestion()->getId();
            })->toArray());
        });
    }

    public function getEchecQuestions($questions): Collection
    {
        return $questions->filter(function(Question $question){
            return in_array($question->getId(), $this->surveyUser->getAnswers()->filter(function(Answer $answer){
                return !$answer->isSuccess();
            })->map(function(Answer $answer){
                return $answer->getQuestion()->getId();
            })->toArray());
        });
    }

    public function getQuestionsNotUseInActualSurvey(): Collection
    {
        return $this->getQuestions()->filter(function (Question $question){
           return !in_array($question->getId(), $this->surveyAnswers->getQuestions());
        });
    }

    public function getSurveySucess(): bool
    {
        return $this->surveyUser->getNbCorrectSurvey() >= $this->surveyUser->getSurvey()->getNbSurveysToPass();
    }

    public function getNbAnswerFinished(): int
    {
        return $this->surveyUser->getAnswers()->count();
    }

    public function getQuestions()
    {
        return $this->surveyUser->getSurvey()->getQuestions();
    }
}