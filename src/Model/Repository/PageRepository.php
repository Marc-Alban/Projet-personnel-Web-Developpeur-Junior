<?php
declare (strict_types = 1);
namespace App\Model\Repository;

use App\Model\Entity\Page;
use App\Tools\Database;

class PageRepository
{

    private $pdo;
    private $pdoStatement;

    /**
     * Fonction constructeur, instanciation de la bdd
     * dans la propriété pdo
     */
    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

/************************************Read All Page************************************************* */
    /**
     * Récupère tous les objets Page
     *
     * @return bool|Page|null
     * false si l'objet n'a pu être inséré, objet Page si une
     * correspondance est trouvé, NULL s'il n'y a aucune correspondance
     */
    public function readAll(?string $data): array
    {
        $this->pdoStatement = $this->pdo->query("SELECT * FROM page");
        $pages = [];
        while ($page = $this->pdoStatement->fetchObject(Page::class)) {
            //$stringBlank = str_replace(' ', '', $page->getTitlePageExtrait());
            // $string = str_replace('é', 'e', $stringBlank);
            //$title = strtolower($string);
            $title = str_replace(' ', '', strtolower($page->getTitlePageExtrait()));

            if ($data === $title) {
                $pages[] = $page;
            } else if ($data === null) {
                $pages[] = $page;
            }
        }
        return $pages;
    }
/************************************End Read All Page************************************************* */
/************************************Read All TitlePage************************************************* */
    /**
     * Récupère tous les objets Page
     *
     * @return bool|Page|null
     * false si l'objet n'a pu être inséré, objet Page si une
     * correspondance est trouvé, NULL s'il n'y a aucune correspondance
     */
    public function readAllTitlePage(?string $min, ?string $maj): array
    {
        $this->pdoStatement = $this->pdo->query("SELECT * FROM page");
        $pages = [];
        while ($page = $this->pdoStatement->fetchObject(Page::class)) {
            if ($min === 'min' && $maj === null) {
                //$stringBlank = str_replace(' ', '', $page->getTitlePageExtrait());
                //$string = str_replace('é', 'e', $stringBlank);
                $pages[] = str_replace(' ', '', strtolower($page->getTitlePageExtrait()));
            } else if ($min === null && $maj === 'maj') {
                $pages[] = $page->getTitlePageExtrait();
            }
        }
        return $pages;
    }
/************************************End Read TitlePage************************************************* */
/************************************Read Page ID************************************************* */
    /**
     * Récupère l'objet Page avec son id
     * false si l'objet n'a pu être inséré, objet Page si une
     * correspondance est trouvé, NULL s'il n'y a aucune correspondance
     */
    public function readPageId(array $data): ?object
    {
        $id = (int) htmlentities(trim($data['get']['id']));
        $this->pdoStatement = $this->pdo->query("SELECT * FROM page WHERE id=$id");
        if ($this->pdoStatement) {
            $page = $this->pdoStatement->fetchObject(Page::class);
            if ($page === false) {
                return null;
            }
            return $page;
        } else if (!$this->pdoStatement) {
            return null;
        }
        return null;
    }
/************************************End Read Page ID************************************************* */
/************************************Page Update Bdd************************************************* */
    /**
     * Insert en bdd les données des pages modifié avec id une fois vérifiées
     */
    public function addBddPage(string $titlePage, string $title, string $description, string $tmpName, string $extention, string $id): void
    {
        $idInt = (int) $id;
        $p = [
            ':titlePage' => $titlePage,
            ':title' => $title,
            ':description' => $description,
            ':image' => $idInt . "." . $extention,
        ];
        $sql = "
        UPDATE `page` SET `titlePage`=:titlePage,`title`=:title,`description`=:description,`image`=:image WHERE id = $idInt
        ";
        $query = $this->pdo->prepare($sql);
        $query->execute($p);
        move_uploaded_file($tmpName, "img/page/" . $idInt . '.' . $extention);
    }
/************************************End Page Update Bdd************************************************* */
}