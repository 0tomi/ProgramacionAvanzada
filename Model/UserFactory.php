<?php 
require_once 'Usuario.php';
require_once 'lectorEnv.php';
class UserFactory {
    private $dataBase;
    private $user;
    public function createUser (string $userTag): ?User {
        $query = 
        "SELECT u.username as Username, u.profileImageRoute as PIR, p.Descripcion as Descr FROM `User` as u
         inner join Profile as p on u.idUser = p.idUser
         where u.userTag = $userTag";
        
        //echo("hasta aca llegamos");
        $result = $bd->query($query);
        if (!$result) {
            error_log('Query error: ' . $bd->error);
            throw new RuntimeException('No pude ejecutar la consulta');
        }
        $rows = $result->fetch_assoc();
        if (!$rows) {
            throw new RuntimeException("No hay fila para id $id");
        }
    }

    public function getUser(): ?User {
        return $this->$user;
    }
    public function __construct(?string $userTag = null) {
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

        if ($userTag === null)
            return;
        
        $this->createUser($userTag);
    }
}
?>