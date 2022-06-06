<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\ORMException;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{

    #[Route('/article', name: 'article', methods: ['GET', 'POST'])]
    public function createArticle(Request $request, ArticleRepository $articleRepository, UserRepository $userRepository): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {

        $article = new Article();
        /*$user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);*/
        $articleform = $this->createForm(ArticleType::class, $article);
        $articleform->handleRequest($request);

        if ($articleform->isSubmitted() && $articleform->isValid()) {
            $dateNow = new \DateTime('now');
            $article
                ->setCreationDate($dateNow)
                ->setUser($this->getUser() );
            $articleRepository->add($article);

            return $this->redirectToRoute('article');
        }
        return $this->render('article/createArticle.html.twig', [
            'article' => $articleform->createView()//penser a faire le twig.html
        ]);
    }

    #[Route('/viewArticles', name: 'viewArticle', methods: ['GET', 'POST'])]
    public function viewArticles(ArticleRepository $articleRepository, Request $request, UserRepository $userRepository)
    {
        // recupère tout les articles
        $article = $articleRepository->findAll();

        return $this->render('article/allArticles.html.twig', [
            'articles' => $article
        ]);
    }

    #[Route('/updateArticle/{id}', name: 'updateArticle')]
    public function updateArticle(ArticleRepository $articleRepository, $id, Request $request)
    {

        $update = $articleRepository->findOneBy(['id' => $id]);

        if ($update->getUser()->getId() === $this->getUser()->getId() || $this->getUser()->getRoles() === ['ROLE_ADMIN']) {

            $form = $this->createForm(ArticleType::class, $update);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $dateNow = new \DateTime('now');
                $articleRepository->add($update);

                return $this->redirectToRoute('articles');
            }
            // abstract renvoi vers un twig
            return $this->render('articleCtrl/updateArticle.html.twig', [
                'article' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/removeArticle/{id}', name: 'removeArticle')]
    public function remove(ArticleRepository $articleRepository, $id)
    {
        //recupere un id qui correpsond à l'id de l'article
        $remove = $articleRepository->findOneBy(['id' => $id]);
        //utilise la function remove qui se trouve dans articlerepository et lui passer en paramètre l'article à supprimer
        $deleteArticle = $articleRepository->remove($remove);
        if ($deleteArticle = true) {
            return $this->redirectToRoute('articles');
        }
    }
}



