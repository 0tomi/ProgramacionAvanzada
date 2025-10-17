-- ====================================================================================
-- Script de creación de tablas - MariaDB (InnoDB, utf8mb4)
-- Modelo:
--   User(PK idUser, (UNIQUE Index) userTag, username, dateCreated)
--   Profile(PKFK idUser, Descripcion)
--   Password((PKFK) idUser, hash)
--   Post((PKFK Nullable) idBelogingPost, (PKFK) idUserOwner, (PK) idPost, content, date)
--   ImagesPost (PKFK idPost, PKFK Name, int order, route)
--   Likes (PKFK idUser, PKFK post, date, time)
-- ====================================================================================

-- Configuración recomendada
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drops en orden inverso de dependencias
DROP TABLE IF EXISTS `Likes`;
DROP TABLE IF EXISTS `ImagesPost`;
DROP TABLE IF EXISTS `Post`;
DROP TABLE IF EXISTS `Password`;
DROP TABLE IF EXISTS `Profile`;
DROP TABLE IF EXISTS `User`;

SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================================
-- Tabla: User
-- ====================================================================================
CREATE TABLE `User` (
  `idUser`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userTag`       VARCHAR(30)     NOT NULL,
  `username`      VARCHAR(100)    NOT NULL,
  `dateCreated`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `uk_user_userTag` (`userTag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================================
-- Tabla: Post
-- Nota: idBelogingPost es NULLABLE y referencia a Post.idPost (autorreferencia).
-- ====================================================================================
CREATE TABLE `Post` (
  `idPost`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idBelogingPost`   BIGINT UNSIGNED NULL,
  `idUserOwner`      BIGINT UNSIGNED NOT NULL,
  `content`          TEXT            NOT NULL,
  `date`             DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPost`),
  KEY `idx_post_belonging` (`idBelogingPost`),
  KEY `idx_post_owner` (`idUserOwner`),
  CONSTRAINT `fk_post_parent`
    FOREIGN KEY (`idBelogingPost`) REFERENCES `Post`(`idPost`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_post_user`
    FOREIGN KEY (`idUserOwner`) REFERENCES `User`(`idUser`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================================
-- Tabla: Profile
-- PK = FK a User.idUser
-- ====================================================================================
CREATE TABLE `Profile` (
  `idUser`      BIGINT UNSIGNED NOT NULL,
  `Descripcion` TEXT            NOT NULL,
  PRIMARY KEY (`idUser`),
  CONSTRAINT `fk_profile_user`
    FOREIGN KEY (`idUser`) REFERENCES `User`(`idUser`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================================
-- Tabla: Password
-- PK = FK a User.idUser
-- (El nombre de la tabla y columnas se mantienen tal cual el modelo)
-- ====================================================================================
CREATE TABLE `Password` (
  `idUser`  BIGINT UNSIGNED NOT NULL,
  `hash`    VARCHAR(255)     NOT NULL,
  PRIMARY KEY (`idUser`),
  CONSTRAINT `fk_password_user`
    FOREIGN KEY (`idUser`) REFERENCES `User`(`idUser`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================================
-- Tabla: ImagesPost
-- PK compuesta (idPost, Name). 'order' es palabra reservada: se usa con backticks.
-- FK: idPost -> Post.idPost. (No hay otra tabla para que 'Name' referencie.)
-- ====================================================================================
CREATE TABLE `ImagesPost` (
  `idPost`  BIGINT UNSIGNED NOT NULL,
  `Name`    VARCHAR(255)    NOT NULL,
  `order`   INT             NOT NULL,
  `route`   VARCHAR(1024)   NOT NULL,
  PRIMARY KEY (`idPost`, `Name`),
  KEY `idx_imagespost_post` (`idPost`),
  CONSTRAINT `fk_imagespost_post`
    FOREIGN KEY (`idPost`) REFERENCES `Post`(`idPost`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================================
-- Tabla: Likes
-- PK compuesta (idUser, post). 'post' referencia a Post.idPost.
-- 'date' y 'time' son nombres válidos, se escapan por claridad.
-- ====================================================================================
CREATE TABLE `Likes` (
  `idUser`  BIGINT UNSIGNED NOT NULL,
  `post`    BIGINT UNSIGNED NOT NULL,
  `date`    DATE            NOT NULL,
  `time`    TIME            NOT NULL,
  PRIMARY KEY (`idUser`, `post`),
  KEY `idx_likes_post` (`post`),
  CONSTRAINT `fk_likes_user`
    FOREIGN KEY (`idUser`) REFERENCES `User`(`idUser`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_post`
    FOREIGN KEY (`post`) REFERENCES `Post`(`idPost`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Me colgue de poner la foto del usuario asi que:
-- (Alternativa sin IF NOT EXISTS)
ALTER TABLE `User`
  ADD COLUMN `profileImageRoute` VARCHAR(1024) NULL
  AFTER `username`;
/* para dps
UPDATE `User`
SET `profileImageRoute` = '/img/defaults/profile.png'
WHERE `profileImageRoute` IS NULL; 
*/