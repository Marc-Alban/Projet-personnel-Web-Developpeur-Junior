<?php
declare (strict_types = 1);
namespace App\Model\Repository;

use App\Model\Entity\Article;
use App\Tools\Database;
use \PDO;

class ArticleRepository
{

    private $pdo;
    private $pdoStatement;
    private $article;

    /**
     * Fonction constructeur, instanciation de la bdd
     * dans la propriété pdo
     */
    public function __construct()
    {
        $this->pdo = Database::getPdo();
        $this->article = new Article;
    }

    /**
     * Récupère le dernier article
     *
     * @return bool|Article|null
     * false si l'objet n'a pu être inséré, objet Article si une
     * correspondance est trouvé, NULL s'il n'y a aucune correspondance
     */
    public function last(): Object
    {

        $this->pdoStatement = $this->pdo->query('SELECT * FROM article WHERE lastArticle = 1 AND posted = 1 ORDER BY date DESC LIMIT 1');

        //execution de la requête
        $executeIsOk = $this->pdoStatement->execute();

        if ($executeIsOk) {
            //$article = $this->pdoStatement->fetchObject('App\Model\Entity\Article');
            $this->article = $this->pdoStatement->fetchObject(Article::class);
            if ($this->article === false) {
                $articleFake = (object) [
                    'id' => '1',
                    'title' => 'Pas d\'article en bdd',
                    'legende' => 'Pas d\'article en bdd',
                    'description' => 'Pas d\'article en bdd',
                    'image' => 'default.png',
                    'date' => '',
                    'posted' => '1',
                    'lastArticle' => '1',
                ];
                return $articleFake;
            }
            return $this->article;
        }

        if (!$executeIsOk) {
            return false;
        }

    }

    /**
     * Met à 0 le dernier article
     *
     * @return void
     */
    public function updateLast(): void
    {

        $this->pdoStatement = $this->pdo->prepare("UPDATE article SET lastArticle = 0 WHERE lastArticle = 1");
        $this->pdoStatement->execute();
    }

    /**
     * Récupère un objet Article à partir de son identifiant
     *
     * @param int $id identifiant d'un article
     * @return bool|Article
     * false si l'objet n'a pu être inséré, objet Article si une
     * correspondance est trouvé, NULL s'il n'y a aucune correspondance
     */
    public function read(int $id): Object
    {

        $this->pdoStatement = $this->pdo->prepare('SELECT * FROM article WHERE  posted = 1 AND id=:id');

        //Liaison paramètres
        $this->pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);

        //execution de la requête
        $executeIsOk = $this->pdoStatement->execute();

        if ($executeIsOk) {
            //$article = $this->pdoStatement->fetchObject('App\Model\Entity\Article');
            $this->article = $this->pdoStatement->fetchObject(Article::class);

            if ($this->article === false) {
                $articleFake = (object) [
                    'id' => '1',
                    'title' => 'Pas d\'article en bdd',
                    'legende' => 'Pas d\'article en bdd',
                    'description' => 'Pas d\'article en bdd',
                    'image' => 'default.png',
                    'date' => '',
                    'posted' => '1',
                    'lastArticle' => '1',
                ];
                return $articleFake;
            }
            return $this->article;

        }

        if (!$executeIsOk) {
            return false;
        }

    }

    /**
     * Récupère tous les objet Article dans la bdd
     *
     * @return array|bool
     * tableau d'objet Article ou un tableau vide s'il n'y pas d'objet en bdd
     * false si une erreur survient
     */
    public function readAll($firstOfPage, $perPage): object
    {

        $this->pdoStatement = $this->pdo->query("SELECT * FROM article WHERE id!=(SELECT max(id) FROM article) AND posted = 1 AND lastArticle = 0 ORDER BY id LIMIT $firstOfPage,$perPage");

        $this->article = [];

        if (empty($this->article) || $this->pdoStatement === false) {
            $articleFake = (object) [
                'id' => '1',
                'title' => 'Pas d\'article en bdd',
                'legende' => 'Pas d\'article en bdd',
                'description' => 'Pas d\'article en bdd',
                'image' => 'default.png',
                'date' => '',
                'posted' => '1',
                'lastArticle' => '1',
            ];
            return $articleFake;
        };

        while ($articles = $this->pdoStatement->fetchObject(Article::class)) {
            $this->article[] = $articles;
        }

        return $this->article;
    }

    /**
     * insert en bdd
     *
     * @param string $title
     * @param string $legende
     * @param string $description
     * @param integer $posted
     * @param integer $lastArticle
     * @param string $tmpName
     * @param string $extention
     * @return void
     */
    public function articleWrite(string $title, string $legende, string $description, string $date, int $posted, int $lastArticle, string $tmpName, string $extention): void
    {
        if (!$tmpName) {
            $id = "post";
            $extention = ".png";
        }

        $this->pdoStatement = $this->pdo->query('SELECT MAX(id) FROM article ORDER BY date = NOW()');
        $response = $this->pdoStatement->fetch();
        $id = $response['MAX(id)'] + 1;

        $p = [
            ':title' => $title,
            ':legende' => $legende,
            ':description' => $description,
            ':image' => $id . "." . $extention,
            ':date' => $date,
            ':posted' => $posted,
            ':lastArticle' => $lastArticle,
        ];

        $sql = "
        INSERT INTO article(title, legende, description, image, date, posted, lastArticle)
        VALUES(:title, :legende, :description, :image, :date, :posted, :lastArticle)
        ";

        $query = $this->pdo->prepare($sql);
        $query->execute($p);

        move_uploaded_file($tmpName, "img/article/" . $id . '.' . $extention);

    }

    /**
     * Supprime un objet Article stocké en bdd
     *
     * @param Article $article objet de type Article
     * @return bool true en cas de succès, false en cas d'erreur
     */
    public function delete(Article $article): bool
    {
        $this->pdoStatement = $this->pdo->prepare('DELETE FROM article WHERE id=:id LIMIT 1');

        //Liaison paramètres
        $this->pdoStatement->bindValue(':id', $article->getId(), PDO::PARAM_INT);

        //execution de la requête
        return $this->pdoStatement->execute();
    }

/**
 * Renvoie le nombre d'article stocké dans la table article et s'il
 * n'y a rien alors renvoie null
 *
 * @return string|null
 */
    public function countArticle(): ?string
    {
        $this->pdoStatement = $this->pdo->query("SELECT count(*) AS total FROM article");
        $req = $this->pdoStatement->fetch();
        if ($req) {
            $total = $req['total'];
            return $total;
        }
        return null;
    }

    /**
     * Retourne les articles en fonctions de la date données dans l'url
     * sinon  si c'est false ou null alors par défaults ce sera 2019
     *
     * @param [type] $years
     * @return array
     */
    public function articleDate($years): array
    {
        $this->pdoStatement = $this->pdo->query("SELECT * FROM article WHERE YEAR( date ) = $years");
        return $this->pdoStatement->fetchAll();
    }

}