<?php 
/*
    Clase responsable de crear usuarios, la cree asi porque divide la logica de la creacion y recuperacion de datos de un usuario
    del propio objeto que despues unicamente se utiliza para recuperar informacion. 

    Constructor: (username = null, password = null)
    Al instanciarse, puede instanciarse con o sin parametros. La diferencia esta en que,
    en el caso de instanciarse usando parametros, la fabrica intentara recuperar la informacion dedicho usuario
    y crear el usuario.
    Se puede obtener dicho usuario mediante el metodo getUser, que puede devolver un nulo o el objeto usuario. Si devuelve 
    un nulo, eso quiere decir que algo fallo, para conocer el error hay que consultar el atributo error del propio objeto, 
    ya que guardara el error mas reciente.

    createUser(username, password):
    Intenta crear el objeto usuario con las credenciales pasadas por parametro, 
    si no lo logra, devolvera NULL. Como antes, se puede consultar el error con el 
    atributo publico error.

    registerUser(username, password)
    Intenta registrar un usuario, si no lo logra, retorna FALSE.
    Para consultar el error, acceder al atributo publico error.
*/

require_once 'Usuario.php';
require_once 'lectorEnv.php';
class UserFactory {
    private $dataBase;
    private $user;

    public $error = '';

    public function registerUser(string $username, $password): bool {
        $stmt = $this->dataBase->prepare(
            'SELECT 1 FROM `User` WHERE username = ?'
        );
 
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $this->error = 'Nombre de usuario ya ocupado.';
            return false;
        }
        $stmt->close();

        $this->dataBase->begin_transaction();
        try {
            $stmtUser = $this->dataBase->prepare(
                'INSERT INTO `User` (userTag, username) VALUES (?, ?)'
            );
            if (!$stmtUser) { throw new mysqli_sql_exception($this->dataBase->error); }
            $stmtUser->bind_param('ss', $username, $username);
            $stmtUser->execute();
            $newUserId = $this->dataBase->insert_id;
            $stmtUser->close();

            $stmtPass = $this->dataBase->prepare(
                'INSERT INTO `Password` (idUser, hash) VALUES (?, ?)'
            );
            if (!$stmtPass) { throw new mysqli_sql_exception($this->dataBase->error); }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmtPass->bind_param('is', $newUserId, $passwordHash);
            $stmtPass->execute();
            $stmtPass->close();

            $stmtProfile = $this->dataBase->prepare(
                'INSERT INTO `Profile` (idUser, Descripcion) VALUES (?, ?)'
            );
            if (!$stmtProfile) { throw new mysqli_sql_exception($this->dataBase->error); }
            $emptyDesc = '';
            $stmtProfile->bind_param('is', $newUserId, $emptyDesc);
            $stmtProfile->execute();
            $stmtProfile->close();

            $this->dataBase->commit();
            return true;

        } catch (mysqli_sql_exception $e) {
            $this->dataBase->rollback();
            $this->error = 'No se pudo registrar el usuario.';
            error_log($e->getMessage());
            return false;
        }
    }


    public function createUser (string $username, $password): ?User{
        if (!$this->dataBase) {
        $this->error = 'No hay conexión a la base de datos';
        return null;
        }

        try {
            $stmt = $this->dataBase->prepare(
                "SELECT u.idUser as UserID, p.hash as Hash 
                FROM `User` as u
                INNER JOIN `Password` as p ON u.idUser = p.idUser
                WHERE u.username = ?"
            );
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $this->error = 'Usuario no encontrado';
                return null;
            }

            $rows = $result->fetch_assoc();
            if (!password_verify($password, $rows['Hash'])) {
                $this->error = 'Contraseña incorrecta';
                return null;
            }

            $id = $rows['UserID'];
            $stmt->close();

            $stmt = $this->dataBase->prepare(
                "SELECT u.idUser as UserID, u.username as Username, 
                        u.profileImageRoute as PIR, p.Descripcion as Descr 
                FROM `User` as u
                INNER JOIN Profile as p ON u.idUser = p.idUser
                WHERE u.idUser = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = $result->fetch_assoc();
            $stmt->close();

            $this->user = new User(
                $rows['UserID'],
                $rows['Username'],
                $rows['Descr'],
                $rows['PIR']  
            );

            return $this->user;

        } catch (mysqli_sql_exception $e) {
            $this->error = 'Error al querer iniciar sesion con el usuario. Pruebe denuevo';
            error_log($e->getMessage());
            return null;
        }
    }

    public function getUser(): ?User {
        $userToReturn = $this->user;
        $this->user = null;
        return $userToReturn;
    }

    public function __construct(?string $username = null, $password = null) {
        try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->dataBase = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'], 
            $_ENV['DB_PASS'], 
            $_ENV['DB_NAME'], 
            $_ENV['DB_PORT']
        );
        $this->dataBase->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            $this->error = 'Error de conexión a la base de datos.';
            error_log($e->getMessage());
            return;
        }

        if ($username === null)
            return;
        
        $this->createUser($username, $password);
    }
}
?>