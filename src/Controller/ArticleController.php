<?php

namespace App\Controller;

use App\Model\Manager\ArticleManager;
use App\View\View;

class ArticleController extends View
{
    public function listePostAction()
    {
        $articleManager = new ArticleManager;
        $lastId = $articleManager->lastId();
        $lasteArticle = $articleManager->read($lastId);
        $listeArticles = $articleManager->readAll();
        //var_dump($listeArticles);
        // echo '<br/>';
        // var_dump($lasteArticle, 'toto');
        // echo '<br/>';
        // die();
        $this->renderer('Frontend', 'blog', ['lasteArticle' => $lasteArticle, 'listeArticle' => $listeArticles]);
    }

    public function PostAction()
    {
        $articleManager = new ArticleManager;
        $article[] = $articleManager->read(1);
        $this->renderer('Frontend', 'article', $article);
    }

    // public function createAction()
    // {
    //     $articleEntity = new Article;
    //     $articleEntity->setLastArticle($_POST['lastArticle'])
    //         ->setPosted($_POST['posted'])
    //         ->setDate('Now()')
    //         ->setImage($_FILES);
    // }

}