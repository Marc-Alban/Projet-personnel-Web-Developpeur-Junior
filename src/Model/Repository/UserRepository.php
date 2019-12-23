<?php
declare (strict_types = 1);
namespace App\Model\Repository;

use App\Model\Entity\User;
use App\Tools\Database;

class UserRepository
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

/**
 * Récupère tous les objets user
 *
 * false si l'objet n'a pu être inséré, objet user si une
 * correspondance est trouvé, NULL s'il n'y a aucune correspondance
 *
 * @return Object
 */
    public function readUser(): Object
    {
        $this->pdoStatement = $this->pdo->query("SELECT * FROM user");
        $user = $this->pdoStatement->fetchObject(User::class);
        return $user;
    }

}