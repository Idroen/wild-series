<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Form\CategoryType;
use App\Form\ProgramSearchType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


Class WildController extends AbstractController
{

    /**
     * @Route("/wild", name="wild_index")
     * @return Response A response instance
     */

    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }
        $form = $this->createForm(
            ProgramSearchType::class,
            null,
            ['method' => Request::METHOD_GET]
        );

        return $this->render(
            'wild/index.html.twig', [
                'programs' => $programs,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param string $slug The slugger
     * @Route("wild/show/{slug}", defaults={"slug" = null}, name="wild_show")
     * @return Response
     */
    public function showByProgram(string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with ' . $slug . ' title, found in program\'s table.'
            );
        }
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program]);

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug' => $slug,
            'seasons' => $seasons,
        ]);
    }

    /**
     * @param string $categoryName The category
     * @Route("wild/category/{categoryName}", defaults={"category" = null}, name="wild_category")
     * @return Response
     */
    public function showByCategory(string $categoryName): Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No category has been sent to find a category in category\'s table.');
        }
        $categoryName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($categoryName)), "-")
        );
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => mb_strtolower($categoryName)]);
        if (!$category) {
            throw $this->createNotFoundException('No category with ' . $categoryName . 'found in category\'s table.');
        }
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category], ['id' => 'desc'], 3);
        if (!$programs) {
            throw $this->createNotFoundException('No program found in program\'s table.');
        }
        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
            'category' => $category,
        ]);
    }

    /**
     * @param int $id
     * @Route("/wild/season/{id<^[0-9-]+$>}", defaults={"id" = null}, name="season")
     * @return Response
     */
    public function showBySeason(int $id): Response
    {
        if (!$id) {
            throw $this
                ->createNotFoundException('No season has been sent to find.');
                }
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findAll();


        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->find($id);

        $program = $season->getProgram();
        $episodes = $season->getEpisodes();

        return $this->render('wild/season.html.twig', [
            'program'  => $program,
            'season'   => $season,
            'episodes' => $episodes,
            'seasons'  => $seasons
        ]);
    }

    /**
     * @param Episode $episode
     * @Route("wild/episode/{id}", name="episode")
     * @return Response
     */
    public function showEpisode(Episode $episode): Response
    {
        $season = $episode->getSeason();
        $program = $season->getProgram();

        return $this->render('wild/episode.html.twig', [
            'episode' => $episode,
            'season' => $season,
            'program' => $program,
        ]);
    }

    /**
     * @param Category $categories
     * @return Response
     * @Route("wild/categories", name="categories")
     */

    public function showCategories(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/show.html.twig',[
            'categories' => $categories,]);
    }

    /**
     * @Route("/wild/add", name="wild_add", methods={"GET", "POST"})
     * @Route("/wild/edit/{id}", name="wild_edit", methods={"GET", "POST"} )
     */
    public function new(EntityManagerInterface $em, Request $request ,Program $program = null)
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $data = $form->getData();
        }
        return $this->render('wild/category.html.twig', [
            'form' => $form->createView(),
            'program' => $program]);
        /*$program = new Program;

        $form = $this->createForm(ProgramSearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $program = $form->getData();
            $em->persist($program);
            $em->flush();
            return $this->redirect('wild_index');
            }

        return $this->render('wild/category.html.twig', [
            'form' => $form->createView(),
            'program' => $program
    ]);*/
    }

}
