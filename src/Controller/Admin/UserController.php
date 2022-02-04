<?php

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Entity\User;
use App\Form\SurveyEditType;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="admin_user")
     */
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/user/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/{id<\d+>}/change_status", methods="GET|POST", name="admin_user_status")
     */
    public function changeStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            $user->setIsVerified(!$user->isVerified());
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user');
    }
}