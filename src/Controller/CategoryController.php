<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/**
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     * @Route("/category", name="category_index")
     */
    public function index(EntityManagerInterface $em, Request $request )
    {

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute("categories");
        }
        return $this->render('category/index.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories]);
    }

}
