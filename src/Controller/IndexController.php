<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Survey;
use App\Entity\SurveyUser;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\SurveyRepository;

use App\Repository\SurveyUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @IsGranted("ROLE_USER")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(CategoryRepository $categoryRepository, SurveyUserRepository $surveyUserRepository): Response
    {
        $categories = $categoryRepository->findAll();

        $surveyUsers = $surveyUserRepository->findBy(['user' => $this->getUser()]);

        /**
         * On ajoute le nombre de questionnaire réussi par l'utilisateur à la liste des catégories
         * @var Category $category
         */
        foreach ($categories as $category) {
            $category->initializeIncrement();
            /** @var SubCategory $subCategory */
            foreach ($category->getSubCategories() as $subCategory) {
                /** @var Survey $survey */
                foreach ($subCategory->getSurveysActive() as $survey) {
                    /** @var SurveyUser $surveyUser */
                    $surveyUser = array_filter($surveyUsers, function (SurveyUser $surveyUser) use ($survey) {
                        return $surveyUser->getSurvey() === $survey;
                    });
                    if($surveyUser){
                        $first =array_key_first($surveyUser);
                        $category->incrementNbSuccess($surveyUser[$first]->isSuccess());
                        $category->incrementNbSurveySuccess($surveyUser[$first]->getNbCorrectSurvey());
                    }
                }
            }
        }

        return $this->render('index/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/{id}", methods="GET", name="category")
     */
    public function category(Category $category, SubCategoryRepository $subCategoryRepository, SurveyUserRepository $surveyUserRepository): Response
    {
        $subCategories = $subCategoryRepository->findBy(
            ['category' => $category]
        );

        $surveyUsers = $surveyUserRepository->findByCategory($category, $this->getUser());
        /**
         * On ajoute le nombre de questionnaire réussi par l'utilisateur à la liste des sous catégorie
         * @var SubCategory $subCategory
         */
        foreach ($subCategories as $subCategory) {
            $subCategory->initializeIncrement();

            /** @var Survey $survey */
            foreach ($subCategory->getSurveysActive() as $survey) {
                /** @var SurveyUser $surveyUser */
                $surveyUser = array_filter($surveyUsers, function (SurveyUser $surveyUser) use ($survey) {
                    return $surveyUser->getSurvey() === $survey;
                });
                if($surveyUser){
                    $first =array_key_first($surveyUser);
                    $subCategory->incrementNbSuccess($surveyUser[$first]->isSuccess());
                    $subCategory->incrementNbSurveySuccess($surveyUser[$first]->getNbCorrectSurvey());
                }
            }
        }

        return $this->render('index/category.html.twig', [
            'category' => $category,
            'subCategories' => $subCategories,
        ]);
    }

    /**
     * @Route("/category/{id}/sub_category/{sub_id}", methods="GET", name="sub_category")
     * @ParamConverter("subCategory", options={"id"="sub_id"})
     */
    public function subCategory(Category $category, SubCategory $subCategory, SurveyRepository $surveyRepository, SurveyUserRepository $surveyUserRepository): Response
    {
        $surveys = $surveyRepository->findBy(
            ['subCategory' => $subCategory, 'status' => true]
        );
        $surveyUsers = $surveyUserRepository->findBySubCategory($subCategory, $this->getUser());

        /**
         * On ajoute le nombre de questionnaire réussi par l'utilisateur à la liste des questionnaires
         * @var Survey $survey
         */
        foreach ($surveys as $survey) {
            $survey->setNbSurveySuccess(0);
            /** @var SurveyUser $surveyUser */
            $surveyUser = array_filter($surveyUsers, function (SurveyUser $surveyUser) use ($survey) {
                return $surveyUser->getSurvey() === $survey;
            });
            if($surveyUser){
                $survey->setNbSurveySuccess($surveyUser[array_key_first($surveyUser)]->getNbCorrectSurvey());
            }

        }
        return $this->render('index/sub_category.html.twig', [
            'category' => $category,
            'subCategory' => $subCategory,
            'surveys' => $surveys,
        ]);
    }
}
