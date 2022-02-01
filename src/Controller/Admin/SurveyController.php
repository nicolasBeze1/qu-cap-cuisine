<?php

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/survey")
 * @IsGranted("ROLE_ADMIN")
 */
class SurveyController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="admin_survey_index")
     */
    public function index(SurveyRepository $surveys): Response
    {
        $authorSurveys = $surveys->findBy(['author' => $this->getUser()], ['publishedAt' => 'DESC']);

        return $this->render('admin/survey/index.html.twig', ['surveys' => $authorSurveys]);
    }

    /**
     * @Route("/new/", methods="GET|POST", name="admin_survey_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $survey = new Survey();
        $survey->setAuthor($this->getUser());

        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($survey);
            $entityManager->flush();

            return $this->redirectToRoute('admin_question_new', ['id' => $survey->getId()]);
        }

        return $this->render('admin/survey/new.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_survey_edit")
     * @IsGranted("edit", subject="survey", message="Surveys can only be edited by their authors.")
     */
    public function edit(Request $request, Survey $survey, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_survey_index');
        }

        return $this->render('admin/survey/edit.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/change_status", methods="GET|POST", name="admin_survey_status")
     * @IsGranted("edit", subject="survey", message="Surveys can only be edited by their authors.")
     */
    public function changeStatus(Request $request, Survey $survey, EntityManagerInterface $entityManager): Response
    {
        if($survey->getQuestions()->count() >= $survey->getQuestionsToAsk()){
            $survey->setStatus(!$survey->isStatus());
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_survey_index');
    }

    /**
     * @Route("/{id<\d+>}/delete", methods="POST", name="admin_survey_delete")
     * @IsGranted("delete", subject="survey")
     */
    public function delete(Request $request, Survey $survey, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_survey_index');
        }

        $entityManager->remove($survey);
        $entityManager->flush();

        $this->addFlash('success', 'survey.deleted_successfully');

        return $this->redirectToRoute('admin_survey_index');
    }
}