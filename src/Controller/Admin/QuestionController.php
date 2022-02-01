<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Survey;
use App\Form\QuestionType;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/question")
 * @IsGranted("ROLE_ADMIN")
 */
class QuestionController extends AbstractController
{
    /**
     * @Route("/new/{id}/", methods="GET|POST", name="admin_question_new")
     */
    public function new(Request $request, Survey $survey, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $question->setAuthor($this->getUser());
        $question->setSurvey($survey);

        $form = $this->createForm(QuestionType::class, $question)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_question_new', ['id' => $survey->getId()]);
            }

            $this->addFlash('success', 'survey.created_successfully');
            return $this->redirectToRoute('admin_survey_edit', ['id' => $question->getSurvey()->getId()]);
        }

        return $this->render('admin/question/new.html.twig', [
            'question' => $question,
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_question_edit")
     * @IsGranted("edit", subject="question", message="Surveys can only be edited by their authors.")
     */
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_survey_edit', ['id' => $question->getSurvey()->getId()]);
        }

        return $this->render('admin/question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/delete", methods="POST", name="admin_question_delete")
     * @IsGranted("delete", subject="question")
     */
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_survey_index');
        }

        $entityManager->remove($question);
        $entityManager->flush();

        $this->addFlash('success', 'question.deleted_successfully');

        return $this->redirectToRoute('admin_survey_edit', ['id' => $question->getSurvey()->getId()]);
    }
}