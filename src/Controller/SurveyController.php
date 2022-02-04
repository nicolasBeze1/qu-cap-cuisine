<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Survey;

use App\Entity\SurveyAnswers;
use App\Entity\SurveyUser;
use App\Form\AnswerType;
use App\Repository\QuestionRepository;
use App\Repository\SurveyAnswersRepository;
use App\Repository\SurveyUserRepository;
use App\Service\QuestionService;
use App\Service\SurveyAnswersService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/survey")
 * @IsGranted("ROLE_USER")
 */
class SurveyController extends AbstractController
{
    /**
     * @Route("/{id}/", methods="GET|POST", name="survey")
     */
    public function index(
        Request                 $request,
        EntityManagerInterface  $entityManager,
        Survey                  $survey,
        SurveyUserRepository    $surveyUserRepository,
        SurveyAnswersRepository $surveyAnswersRepository,
        QuestionRepository      $questionRepository,
        QuestionService         $questionService
    ): Response
    {
        $user = $this->getUser();
        //on récupère le questionnaire global de l'utilisateur
        $surveyUser = $surveyUserRepository->findOneBy([
            'user' => $user,
            'survey' => $survey
        ]);
        if (!$surveyUser) {
            $surveyUser = new SurveyUser();
            $surveyUser->setUser($user);
            $surveyUser->setSurvey($survey);
        }

        //on récupère le questionnaire en cour (status = false car sinon questionnaire fini) de l'utilisateur
        $surveyAnswers = $surveyAnswersRepository->findOneBy([
            'surveyUser' => $surveyUser,
            'status' => false
        ]);
        if (!$surveyAnswers) {
            $surveyAnswers = new SurveyAnswers();
            $surveyUser->addSurveyAnswers($surveyAnswers);
        }

        $surveyAnswersService = new SurveyAnswersService($surveyAnswers);
        $question = $request->isMethod('post') ?
            $questionRepository->find($request->get('answer')['question']) :
            $surveyAnswersService->getRandomQuestion();

        //on récupère la réponse  de l'utilisateur
        $answer = $surveyUser->getAnswers()->filter(function (Answer $answer) use ($question) {
            return $answer->getQuestion() === $question;
        })->first();
        if (!$answer) {
            $answer = new Answer();
            $surveyUser->addAnswer($answer);
            $answer->setQuestion($question);
        }

        $form = $this->createForm(AnswerType::class, $answer)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $correctAnswer = $questionService->compare($answer->getQuestion()->getAnswer(), $answer->getAnswer());

            if ($correctAnswer) {
                $surveyAnswers->setNbCorrectAnswer($surveyAnswers->getNbCorrectAnswer() + 1);
            }

            $answer->setSuccess($correctAnswer);
            $surveyAnswers->addQuestion($answer->getQuestion()->getId());

            if (count($surveyAnswers->getQuestions()) === $survey->getQuestionsToAsk()) {
                $surveyAnswers->setFinishedAt(new \DateTime());
                $surveyAnswers->setStatus(true);
                $surveyUser->setFinishedAt(new \DateTime());


                if ($surveyAnswers->getNbCorrectAnswer() >= $survey->getQuestionsToAsk() * ($survey->getSuccessPercent() / 100)) {
                    $surveyAnswers->setSuccess(true);
                    if ($surveyAnswersService->getSurveySucess()) {
                        $surveyUser->setSuccess(true);
                        $surveyUser->setSuccessInARow($surveyUser->getSuccessInARow() + 1);
                    }
                }
            }

            $entityManager->persist($surveyUser);
            $entityManager->flush();

            if(!$correctAnswer){
                return $this->redirectToRoute('error_answer', ['id' => $question->getId(), 'in_progress' => (int)$form->get('saveAndCreateNew')->isClicked()]);
            }
            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('survey', ['id' => $survey->getId()]);
            }

            return $this->redirectToRoute('survey_ended', ['id' => $surveyAnswers->getId()]);
        }

        return $this->render('survey/index.html.twig', [
            'survey' => $survey,
            'surveyAnswers' => $surveyAnswers,
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ended/{id}", methods="GET", name="survey_ended")
     */
    public function endend(SurveyAnswers $surveyAnswers): Response
    {
        return $this->render('survey/ended.html.twig', [
            'surveyAnswers' => $surveyAnswers,
        ]);
    }

    /**
     * @Route("/error_answer/{id}/in_progress/{in_progress}", methods="GET", name="error_answer")
     */
    public function errorAnswer(Request $request, Question $question): Response
    {
        return $this->render('survey/error_answer.html.twig', [
            'question' => $question,
            'inProgress' => $request->get('in_progress'),
        ]);
    }

}
