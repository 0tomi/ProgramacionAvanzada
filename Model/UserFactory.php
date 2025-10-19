<?php 
require_once 'Usuario.php';
require_once 'lectorEnv.php';
class UserFactory {
    private $dataBase;
    public $error = '';
    private $user;
    public function createUser (string $username, $password): ?User{
        $query = 
        "SELECT u.idUser as UserID, p.hash as Hash from  `User` as u
         inner join `Password` as p on u.idUser = p.idUser
         where u.username = '$username'";
        
        $result = $this->dataBase->query($query);
        if (!$result) {
            error_log('Query error: ' . $this->dataBase->error);
            return null;
        }

        $rows = $result->fetch_assoc();
        if (!$rows) {
            error_log('Usuario no encontrado.');
            $this->error = 'Usuario no encontrado.'; // lo redacto en ingles xq en linux no tengo teclado espaniol
            return null;
        }
        
        if (!password_verify($password, $rows['Hash'])){
            error_log('Contrasenia incorrecta');
            $this->error = 'Incorrect password'; // lo redacto en ingles xq en linux no tengo teclado espaniol
            return null;
        }

        $id = $rows['UserID'];

        $queryLogeado = 
        "SELECT u.idUser as UserID, u.username as Username, u.profileImageRoute as PIR, p.Descripcion as Descr FROM `User` as u
         inner join Profile as p on u.idUser = p.idUser
         where u.idUser = $id";

        $rows = $this->dataBase->query($queryLogeado)->fetch_assoc();

        $this->user = new User(
            $rows['UserID'],
            $rows['Username'],
            $rows['Descr'],
            $rows['PIR']  
        );

        return $this->user;
    }

    public function getUser(): ?User {
        $userToReturn = $this->user;
        $this->user = null;
        return $userToReturn;
    }

    public function __construct(?string $username = null, $password = null) {
        $this->dataBase = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'], 
            $_ENV['DB_PASS'], 
            $_ENV['DB_NAME'], 
            $_ENV['DB_PORT']
        );
        $this->user = null;

        if ($this->dataBase->connect_errno) 
            echo("Error en la bd: $dataBase->connect_error");

        if ($username === null)
            return;
        
        $this->createUser($username, $password);
    }
}
?>