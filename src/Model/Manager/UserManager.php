<?php
declare (strict_types = 1);
namespace App\Model\Manager;

use App\Model\Repository\UserRepository;
use App\Tools\GestionGlobal;
use App\Tools\Token;

class UserManager
{

    private $userRepository;
    private $name;
    private $lastName;
    private $mail;
    private $mailConf;
    private $error;
    private $token;
    private $maSuperGlobale;

    /**
     * Fonction constructeur, instanciation du userRepository
     * dans la propriété userRepository
     */
    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->maSuperGlobale = new GestionGlobal();
        $this->token = new Token();
    }
    /************************************Get All Form User Bdd************************************************* */
    /**
     * Retourne la table user sauf le password
     *
     * @return string
     */
    public function getAllUserBdd(): Object
    {
        return $this->userRepository->readAllUser();
    }
/************************************End Get Users Bdd************************************************* */
    /************************************Get All Form User************************************************* */
    /**
     * formulaire user sauf le password
     *
     * @return string
     */
    public function getAllUsersForm(array $data): void
    {
        $this->name = htmlentities(trim($data['post']['name'])) ?? null;
        $this->lastName = htmlentities(trim($data['post']['lastName'])) ?? null;
        $this->mail = htmlentities(trim($data['post']['mail'])) ?? null;
        $this->mailConf = htmlentities(trim($data['post']['mailConf'])) ?? null;
    }
/************************************End Get Users************************************************* */
    /************************************Return Datas Form User************************************************* */
    /**
     * Retourne la table user sauf le password
     *
     * @return string
     */
    public function FormUser(array $data): ?array
    {
        $this->getAllUsersForm($data);
        $tabDataFormUser = [
            'name' => $this->name,
            'lastName' => $this->lastName,
            'mail' => $this->mail,
            'mailConf' => $this->mailConf,
        ];
        return $tabDataFormUser;
    }
/************************************End Return Datas Form User************************************************* */
    /************************************Factorisation VerifPassword************************************************* */
    /**
     * Retourne l'entiereté de la table user
     *
     * @return string
     */
    public function verifPass(array $data): ?string
    {
        $newMdp = htmlentities(trim($data['post']['newMdp'])) ?? null;
        $newMdpConf = htmlentities(trim($data['post']['newMdpConf'])) ?? null;

        if ($newMdp === null || empty($newMdp)) {
            $this->error = "Veuillez mettre un mot de passe";
        } else if ($newMdpConf === null || empty($newMdpConf)) {
            $this->error = "Veuillez mettre un mot de passe de confirmation";
        } else if (strlen($newMdp) < 6 || strlen($newMdpConf) < 6) {
            $this->error = "Veuillez mettre un mot de passe de plus de 6 caractères";
        } else if ($newMdp !== $newMdpConf) {
            $this->error = "Mot de passe pas identique";
        } else if (!preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{6,}$#', $newMdp)) {
            $this->error = 'Mot de passe non conforme, doit avoir minuscule-majuscule-chiffres-caractères';
        }
        return $this->error;
    }
/************************************End Factorisation VerifPassword************************************************* */
/************************************Get Users************************************************* */
    /**
     * Retourne le nom de l'utilisateur
     *
     * @return string
     */
    public function getUsers(): string
    {
        return $this->userRepository->readAllUser()->getName();
    }
/************************************End Get Users************************************************* */
/************************************Get Pass************************************************* */
    /**
     * Retourne le mot de passe
     *
     * @return string
     */
    public function getPass(): string
    {
        return $this->userRepository->readAllUser()->getPassword();
    }
/************************************End Get Pass************************************************* */
/************************************Get MailForm Page LostPass************************************************* */
    /**
     * Récupère le mail dans le formulaire sinon renvoie null si rien
     *
     * @param array $data
     * @return string|null
     */
    public function getMailForm(array $data): ?string
    {
        if (isset($data['post']['submit'])) {
            $mail = htmlentities(strip_tags(trim($data['post']['mail']))) ?? null;
            return $mail;
        }
        return null;
    }
/************************************End Get MailForm Page LostPass************************************************* */
/************************************Get Active User************************************************* */

    /**
     * Retourna la valeur active dans le controller
     *
     * @return void
     */
    public function getActiveUser(): array
    {
        return $this->userRepository->getActiveValue();
    }
/************************************End Get Active User************************************************* */
/************************************Get Token Bdd************************************************* */
    /**
     * Récupère le token dans la bdd
     *
     * @param array $data
     * @return string
     */
    public function getTokenBdd(): string
    {
        $token = $this->userRepository->readAllUser()->getToken();
        return $token;
    }
/************************************End Get Token Bdd************************************************* */
/************************************Set Name Bdd************************************************* */
    /**
     * Modifie le nom dans la bdd
     *
     * @param array $data
     * @return string
     */
    public function setNameBdd(array $data): void
    {
        $this->userRepository->updateName($data);
    }
/************************************End Set name Bdd************************************************* */
/************************************Set lastName Bdd************************************************* */
    /**
     * Modifie le prénom dans la bdd
     *
     * @param array $data
     * @return string
     */
    public function setlastNameBdd(array $data): void
    {
        $this->userRepository->updateLastName($data);
    }
/************************************End Set lastname Bdd************************************************* */
/************************************Set Mail Bdd************************************************* */
    /**
     * Modifie le mail dans la bdd
     *
     * @param array $data
     * @return string
     */
    public function setMailBdd(array $data): void
    {
        $this->userRepository->updateMail($data);
    }
/************************************End Set Mail Bdd************************************************* */
/************************************Check BDD Mail************************************************* */

    /**
     * Verifie si le mail existe bien en bdd
     * retourne un boolean si c'est bon ou faux
     *
     * @return void
     */
    private function checkBddMail(string $mailPost): bool
    {
        $mail = $this->userRepository->readAllUser()->getMail();
        if ($mail !== $mailPost) {
            return false;
        }
        return true;
    }
/************************************End Check BDD Mail ************************************************* */
/************************************Mail New Pass************************************************* */

    /**
     * Message du mail que va recevoir l'utilisateur lors
     * de la réinitialisation de son mot de passe.
     *
     * @return void
     */
    private function message(string $token): void
    {
        $headers = "MIME-Version: 1.0\r\n..";
        $headers = 'From: From: BmFinance' . "\n";
        $headers .= 'Content-Type: text/html; charset="UTF-8"' . "\n";
        $headers .= 'Content-Transfer-Encoding: 8bit';

        $message = "
        <html>
            <head>
                <title> Renouvellement du mot de passe </title>
            </head>
            <body>
                <p>Bonjour,</p>

                <p>Vous avez cliqué sur le lien pour renouveler son mot de passe:</p>
                <p>Cliquer sur le lien dessous pour modifier votre mot de passe.</p>
                <p><a href='http://projet5.marcalban/?p=newPassword&token=" . $token . "'>?p=newPassword&token=' . $token . '</a></p>
            </body>
        </html>
        ";

        mail("millet.marcalban@gmail.com", "Page LostPassword - BmFinance.com", $message, $headers);
    }
/************************************End Mail New Pass************************************************* */
/************************************Verif Mail Form************************************************* */

    /**
     * Verifie le champ mail et affiche les erreurs
     *
     * @param array $data
     * @return array|null
     */
    public function verifMail(array $data): ?array
    {
        $mail = $this->getMailForm($data);
        $token = password_hash(random_bytes(5), PASSWORD_DEFAULT);
        $succes = $data["session"]["succes"] ?? null;
        unset($data["session"]["succes"]);
        $error = $data['session']['error'] ?? null;
        unset($data["session"]["error"]);

        if (isset($data['post']['submit'])) {

            if (empty($mail) || !isset($mail)) {
                $error['empty'] = "Veuillez mettre un mail";
            } else if (!preg_match(" /^.+@.+\.[a-zA-Z]{2,}$/ ", $mail)) {
                $error['mailWrong'] = "L'adresse e-mail est invalide";
            } else if ($this->checkBddMail($mail) === false) {
                $error['mailEmpty'] = "L'adresse e-mail est inconnue de nos service";
            }

            if ($this->token->compareTokens($data) !== null) {
                $error['token'] = "Formulaire incorrect";
            }
            if (empty($error)) {
                if ($this->checkBddMail($mail) === true) {
                    $this->message($token);
                    $this->userRepository->insertToken($token);
                    $this->userRepository->changeActive('false');
                    $succes['message'] = 'Vous allez recevoir un mail de réinitialisation de mot de passe';
                }
                return $succes;
            }
            return $error;
        }
        return null;
    }
/************************************End Verif Mail Form************************************************* */
/************************************Verif UserToken************************************************* */
    /**
     * Verification du bon utilisateur pour acceder à la page
     * newPassword, si null renvoie sur la page lostPassword,
     * si ok retourne true
     *
     * @return null|bool
     */
    public function verifUser(array $data): ?bool
    {
        $tokenUrl = trim(htmlentities($data['get']['token'])) ?? null;
        $token = $this->getTokenBdd();
        if ($tokenUrl === null || empty($tokenUrl) || $token !== $tokenUrl) {
            return null;
        }
        return true;
    }
/************************************End Verif UserToken************************************************* */
/************************************FormDataUser************************************************* */
    /**
     * Met à jour les données sur le fomulaire back
     * pour ensuite les mettres à jours en bdd
     *
     */
    public function dataFormBack(array $data): ?array
    {
        if (isset($data['post']['submit'])) {
            $this->getAllUsersForm($data);
            $action = $data['get']['action'] ?? null;
            $name = $this->name;
            $lastName = $this->lastName;
            $mail = $this->mail;
            $mailConf = $this->mailConf;
            $errors = $data["session"]["error"] ?? null;
            unset($data["session"]["error"]);
            $succes = $data["session"]["succes"] ?? null;
            unset($data["session"]["succes"]);
            if ($action === null || empty($action) || !isset($action)) {
                return null;
            }
            if ($name === null || empty($name)) {
                $errors['emptyName'] = "Veuillez mettre un nom";
            } else if ($lastName === null || empty($lastName)) {
                $errors['emptyLastName'] = "Veuillez mettre un prénom";
            } else if ($mail === null || empty($mail)) {
                $errors['emptyMail'] = "Veuillez mettre un mail";
            } else if (!preg_match(" /^.+@.+\.[a-zA-Z]{2,}$/ ", $mail)) {
                $errors['mailWrong'] = "L'adresse e-mail est invalide";
            } else if ($mailConf === null || empty($mailConf)) {
                $errors['emptyMailConf'] = "Veuillez mettre un mail de confirmation";
            } else if (!preg_match(" /^.+@.+\.[a-zA-Z]{2,}$/ ", $mailConf)) {
                $errors['mailConfWrong'] = "L'adresse e-mail de confirmation est invalide";
            }

            if ($this->token->compareTokens($data) !== null) {
                $errors['token'] = "Formulaire incorrect";
            }

            if (empty($errors)) {
                $this->setNameBdd($data);
                $this->setLastNameBdd($data);
                $this->setMailBdd($data);
                $succes["update"] = "Utilisateur mis à jour";
                return $succes;
            }
            return $errors;
        }
        return null;
    }
/************************************End FormDataUser************************************************* */
/************************************Change Password************************************************* */
    /**
     * Methode pour changer le mot de passe
     *
     * @param array $data
     * @return array|null
     */
    public function changeMdp(array $data): ?array
    {
        $submit = $data['post']['submit'] ?? null;
        $arrayValue = $this->userRepository->getActiveValue();
        $activeValue = $arrayValue[0];
        $errors = $data['session']['error'] ?? null;
        unset($data["session"]["error"]);

        if ($activeValue === '0' || $activeValue === 0) {
            if (isset($submit)) {
                if ($this->verifPass($data)) {
                    $errors['errorMdp'] = $errors . $this->error;
                }
                if ($this->token->compareTokens($data) !== null) {
                    $errors['token'] = "Formulaire incorrect";
                }
                if (empty($errors)) {
                    $this->userRepository->changePass($data);
                    $this->userRepository->changeActive('true');
                    $succes['message'] = "Mot de passe changé";
                    return $succes;
                }
                return $errors;
            }
            return null;
        } else if ($activeValue === '1' || $activeValue === 1) {
            if (isset($submit)) {
                if ($this->verifPass($data)) {
                    $errors['errorMdp'] = $errors . $this->error;
                }
                if ($this->token->compareTokens($data) !== null) {
                    $errors['token'] = "Formulaire incorrect";
                }
                if (empty($errors)) {
                    $this->userRepository->changePass($data);
                    $succes['message'] = "Mot de passe changé";
                    return $succes;
                }
                return $errors;
            }
            return null;
        }
        return null;
    }
/************************************End Change Password************************************************* */

}