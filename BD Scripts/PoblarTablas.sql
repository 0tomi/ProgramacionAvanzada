-- =============================================================================
-- SEED de datos (MariaDB)
-- Inserta solo el usuario solicitado y su perfil/contraseña.
-- No se pueblan rutas de imágenes en ImagesPost.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- Usuario: fcytuader  -- contraseña: programacionavanzada
-- username = userTag = 'fcytuader'
-- profileImageRoute = 'imagenes/profilePictures/uader.png'
-- Descripcion = 'Facultad de Ciencias y Tecnología - Universidad Autónoma de Entre Ríos'
-- ---------------------------------------------------------------------------
INSERT INTO `User` (`userTag`, `username`, `profileImageRoute`)
VALUES ('fcytuader', 'fcytuader', 'imagenes/profilePictures/uader.png');  -- contraseña: programacionavanzada

-- Guardamos el id generado para referenciar en tablas relacionadas
SET @id_fcytuader := LAST_INSERT_ID();

-- Profile
INSERT INTO `Profile` (`idUser`, `Descripcion`)
VALUES
  (@id_fcytuader, 'Facultad de Ciencias y Tecnología - Universidad Autónoma de Entre Ríos');

-- Password (almacenamos hash; aquí usamos SHA2-256)
INSERT INTO `Password` (`idUser`, `hash`)
VALUES
  (@id_fcytuader, SHA2('programacionavanzada', 256));

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;

-- (Opcional) Verificaciones rápidas
-- SELECT * FROM `User` WHERE idUser = @id_fcytuader;
-- SELECT * FROM `Profile` WHERE idUser = @id_fcytuader;
-- SELECT * FROM `Password` WHERE idUser = @id_fcytuader;


-- =============================================================================
-- SEED de prueba (MariaDB) - Poblado inventado
-- Cubre: User, Profile, Password, Post, ImagesPost, Likes
-- Notas:
--   - No se crean rutas reales en ImagesPost: route = '' (cadena vacía).
--   - En User, se comenta la contraseña en cada inserción.
--   - Hash de contraseñas con SHA2-256.
-- =============================================================================

SET NAMES utf8mb4;

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- USERS (con comentarios de contraseña)
-- ---------------------------------------------------------------------------

-- Usuario 1  -- contraseña: test12345
INSERT INTO `User` (`userTag`, `username`)
VALUES ('juanperez', 'eljuampi');  -- contraseña: test12345
SET @id_u1 := LAST_INSERT_ID();

-- Usuario 2  -- contraseña: secreto
INSERT INTO `User` (`userTag`, `username`)
VALUES ('maria.dev', 'mariapower');  -- contraseña: secreto
SET @id_u2 := LAST_INSERT_ID();

-- Usuario 3  -- contraseña: max2025!
INSERT INTO `User` (`userTag`, `username`)
VALUES ('codermax', 'maxi');  -- contraseña: max2025!
SET @id_u3 := LAST_INSERT_ID();

-- Usuario 4  -- contraseña: anapass
INSERT INTO `User` (`userTag`, `username`)
VALUES ('ana_lopez', 'anita');  -- contraseña: anapass
SET @id_u4 := LAST_INSERT_ID();

-- (Opcional) Si también existe previamente el usuario 'fcytuader', no lo tocamos aquí.

-- ---------------------------------------------------------------------------
-- PROFILES
-- ---------------------------------------------------------------------------
INSERT INTO `Profile` (`idUser`, `Descripcion`) VALUES
  (@id_u1, 'Docente de programación y fan de C++'),
  (@id_u2, 'Desarrolladora fullstack, café y TypeScript'),
  (@id_u3, 'Gamer, QA amateur y dev en formación'),
  (@id_u4, 'Estudiante de datos, ama la estadística');

-- ---------------------------------------------------------------------------
-- PASSWORDS (hash = SHA2-256 del comentario de cada user)
-- ---------------------------------------------------------------------------
INSERT INTO `Password` (`idUser`, `hash`) VALUES
  (@id_u1, SHA2('test12345', 256)),
  (@id_u2, SHA2('secreto', 256)),
  (@id_u3, SHA2('max2025!', 256)),
  (@id_u4, SHA2('anapass', 256));

-- ---------------------------------------------------------------------------
-- POSTS
--   - Creamos 5 posts. Algunos responden a otros (idBelogingPost).
-- ---------------------------------------------------------------------------

-- Post 1 (de @id_u1)
INSERT INTO `Post` (`idBelogingPost`, `idUserOwner`, `content`)
VALUES (NULL, @id_u1, '¡Hola, mundo! Probando el timeline de la app.');
SET @p1 := LAST_INSERT_ID();

-- Post 2 (de @id_u2)
INSERT INTO `Post` (`idBelogingPost`, `idUserOwner`, `content`)
VALUES (NULL, @id_u2, 'Hoy deployé una API. Nada se rompió (creo).');
SET @p2 := LAST_INSERT_ID();

-- Post 3 (respuesta a Post 1, de @id_u3)
INSERT INTO `Post` (`idBelogingPost`, `idUserOwner`, `content`)
VALUES (@p1, @id_u3, 'Bienvenido al feed 😄 ¿Qué estás construyendo?');
SET @p3 := LAST_INSERT_ID();

-- Post 4 (de @id_u4)
INSERT INTO `Post` (`idBelogingPost`, `idUserOwner`, `content`)
VALUES (NULL, @id_u4, 'Estudiando pruebas A/B y p-values. Se aceptan tips.');
SET @p4 := LAST_INSERT_ID();

-- Post 5 (respuesta a Post 2, de @id_u1)
INSERT INTO `Post` (`idBelogingPost`, `idUserOwner`, `content`)
VALUES (@p2, @id_u1, '¡Felicitaciones! ¿Usaste CI/CD o deploy manual?');
SET @p5 := LAST_INSERT_ID();

-- ---------------------------------------------------------------------------
-- IMAGESPOST (sin rutas reales: route = '')
--   - PK(compuesta): (idPost, Name)
--   - order = 1..n
-- ---------------------------------------------------------------------------
INSERT INTO `ImagesPost` (`idPost`, `Name`, `order`, `route`) VALUES
  (@p1, 'header.jpg', 1, ''),
  (@p2, 'screen1.png', 1, ''),
  (@p2, 'screen2.png', 2, ''),
  (@p4, 'notes.png', 1, '');

-- ---------------------------------------------------------------------------
-- LIKES
--   - date y time con valores actuales
--   - PK(compuesta): (idUser, post)
-- ---------------------------------------------------------------------------
INSERT INTO `Likes` (`idUser`, `post`, `date`, `time`) VALUES
  (@id_u2, @p1, CURRENT_DATE, CURRENT_TIME),
  (@id_u3, @p1, CURRENT_DATE, CURRENT_TIME),
  (@id_u1, @p2, CURRENT_DATE, CURRENT_TIME),
  (@id_u4, @p2, CURRENT_DATE, CURRENT_TIME),
  (@id_u1, @p4, CURRENT_DATE, CURRENT_TIME),
  (@id_u3, @p4, CURRENT_DATE, CURRENT_TIME),
  (@id_u2, @p5, CURRENT_DATE, CURRENT_TIME);

COMMIT;

-- =============================================================================
-- FIN DEL SEED
-- =============================================================================
