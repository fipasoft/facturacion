SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `facturacion` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci ;
USE `facturacion` ;

-- -----------------------------------------------------
-- Table `facturacion`.`ejercicio`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`ejercicio` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `annio` INT(10) UNSIGNED NOT NULL ,
  `saved_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`pais`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`pais` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(100) CHARACTER SET 'latin1' NOT NULL ,
  `clave` VARCHAR(5) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`edo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`edo` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `pais_id` INT(10) UNSIGNED NOT NULL ,
  `nombre` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `edo_FKIndex1` (`pais_id` ASC) ,
  CONSTRAINT `edo_ibfk_1`
    FOREIGN KEY (`pais_id` )
    REFERENCES `facturacion`.`pais` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 33
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`municipio`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`municipio` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `edo_id` INT(10) UNSIGNED NOT NULL ,
  `nombre` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `municipio_FKIndex1` (`edo_id` ASC) ,
  CONSTRAINT `municipio_ibfk_1`
    FOREIGN KEY (`edo_id` )
    REFERENCES `facturacion`.`edo` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 2594
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`fiscal`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`fiscal` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `rfc` VARCHAR(20) NOT NULL ,
  `razon` VARCHAR(255) NOT NULL ,
  `domicilio` VARCHAR(255) NOT NULL ,
  `colonia` VARCHAR(255) NOT NULL ,
  `cp` VARCHAR(20) NOT NULL ,
  `municipio_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fiscal_FKIndex1` (`municipio_id` ASC) ,
  CONSTRAINT `fiscal_ibfk_1`
    FOREIGN KEY (`municipio_id` )
    REFERENCES `facturacion`.`municipio` (`id` )
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 20
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`dependencia`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`dependencia` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `fiscal_id` INT(10) UNSIGNED NOT NULL ,
  `ejercicio_id` INT(10) UNSIGNED NOT NULL ,
  `clave` VARCHAR(8) NOT NULL ,
  `nombre` VARCHAR(256) NOT NULL ,
  `saved_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified_in` DATETIME NULL DEFAULT '0000-00-00 00:00:00' ,
  `externo` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `dependencia_FKIndex1` (`ejercicio_id` ASC) ,
  INDEX `dependencia_FKIndex2` (`fiscal_id` ASC) ,
  CONSTRAINT `dependencia_ibfk_1`
    FOREIGN KEY (`ejercicio_id` )
    REFERENCES `facturacion`.`ejercicio` (`id` ),
  CONSTRAINT `dependencia_ibfk_2`
    FOREIGN KEY (`fiscal_id` )
    REFERENCES `facturacion`.`fiscal` (`id` )
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 16
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`festados`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`festados` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(50) NULL DEFAULT NULL ,
  `singular` VARCHAR(50) NULL DEFAULT NULL ,
  `plural` VARCHAR(50) NULL DEFAULT NULL ,
  `clave` VARCHAR(5) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 100
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`metodopago`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`metodopago` (
  `id` INT(10) NOT NULL ,
  `clave` VARCHAR(8) NOT NULL COMMENT '	' ,
  `nombre` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `facturacion`.`factura`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`factura` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ejercicio_id` INT(10) UNSIGNED NOT NULL ,
  `festados_id` INT(10) UNSIGNED NOT NULL ,
  `dependencia_id` INT(10) UNSIGNED NOT NULL ,
  `metodopago_id` INT(10) NOT NULL DEFAULT 1 ,
  `folio` VARCHAR(32) NOT NULL ,
  `fecha` DATE NOT NULL DEFAULT '0000-00-00' ,
  `razon` VARCHAR(256) NOT NULL ,
  `rfc` VARCHAR(16) NOT NULL ,
  `domicilio` VARCHAR(256) NOT NULL ,
  `colonia` VARCHAR(256) NOT NULL ,
  `cpostal` VARCHAR(10) NOT NULL ,
  `subtotal` DOUBLE(11,2) NOT NULL DEFAULT '0.00' ,
  `iva` DOUBLE(11,2) NOT NULL DEFAULT '0.00' ,
  `total` DOUBLE(11,2) NOT NULL DEFAULT '0.00' ,
  `ctapago` VARCHAR(18) NULL ,
  `observaciones` VARCHAR(254) NULL DEFAULT NULL ,
  `enviada` DATE NOT NULL DEFAULT '0000-00-00' ,
  `recibida` DATE NOT NULL DEFAULT '0000-00-00' ,
  `saved_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `factura_FKIndex1` (`dependencia_id` ASC) ,
  INDEX `factura_FKIndex2` (`festados_id` ASC) ,
  INDEX `factura_FKIndex3` (`ejercicio_id` ASC) ,
  INDEX `fk_factura_metodopago1` (`metodopago_id` ASC) ,
  CONSTRAINT `factura_ibfk_1`
    FOREIGN KEY (`dependencia_id` )
    REFERENCES `facturacion`.`dependencia` (`id` )
    ON UPDATE CASCADE,
  CONSTRAINT `factura_ibfk_2`
    FOREIGN KEY (`festados_id` )
    REFERENCES `facturacion`.`festados` (`id` )
    ON UPDATE CASCADE,
  CONSTRAINT `factura_ibfk_3`
    FOREIGN KEY (`ejercicio_id` )
    REFERENCES `facturacion`.`ejercicio` (`id` )
    ON UPDATE CASCADE,
  CONSTRAINT `fk_factura_metodopago1`
    FOREIGN KEY (`metodopago_id` )
    REFERENCES `facturacion`.`metodopago` (`id` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
AUTO_INCREMENT = 36
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`concepto`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`concepto` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `factura_id` INT(10) UNSIGNED NOT NULL ,
  `descripcion` VARCHAR(300) NOT NULL ,
  `cantidad` INT(10) UNSIGNED NOT NULL ,
  `unitario` DOUBLE(11,2) NOT NULL ,
  `monto` DOUBLE(11,2) NOT NULL ,
  `clave` VARCHAR(20) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `orden_FKIndex2` (`factura_id` ASC) ,
  CONSTRAINT `concepto_ibfk_1`
    FOREIGN KEY (`factura_id` )
    REFERENCES `facturacion`.`factura` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 126
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`contacto`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`contacto` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `titulo` VARCHAR(5) NULL DEFAULT NULL ,
  `cargo` VARCHAR(30) NULL DEFAULT NULL ,
  `nombre` VARCHAR(30) NOT NULL ,
  `ap` VARCHAR(20) NOT NULL ,
  `am` VARCHAR(20) NOT NULL ,
  `tel` VARCHAR(30) NULL DEFAULT NULL ,
  `cel` VARCHAR(30) NULL DEFAULT NULL ,
  `domicilio` VARCHAR(254) NULL DEFAULT NULL ,
  `trunk` VARCHAR(30) NULL DEFAULT NULL ,
  `mail` VARCHAR(60) NOT NULL ,
  `sexo` VARCHAR(1) NOT NULL ,
  `observaciones` VARCHAR(256) NULL DEFAULT NULL ,
  `saved_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`depcontacto`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`depcontacto` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ejercicio_id` INT(10) UNSIGNED NOT NULL ,
  `contacto_id` INT(10) UNSIGNED NOT NULL ,
  `dependencia_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `depcontacto_FKIndex1` (`dependencia_id` ASC) ,
  INDEX `depcontacto_FKIndex2` (`contacto_id` ASC) ,
  INDEX `depcontacto_FKIndex3` (`ejercicio_id` ASC) ,
  CONSTRAINT `depcontacto_ibfk_1`
    FOREIGN KEY (`dependencia_id` )
    REFERENCES `facturacion`.`dependencia` (`id` ),
  CONSTRAINT `depcontacto_ibfk_2`
    FOREIGN KEY (`contacto_id` )
    REFERENCES `facturacion`.`contacto` (`id` )
    ON DELETE CASCADE,
  CONSTRAINT `depcontacto_ibfk_3`
    FOREIGN KEY (`ejercicio_id` )
    REFERENCES `facturacion`.`ejercicio` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`festado`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`festado` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `factura_id` INT(10) UNSIGNED NOT NULL ,
  `festados_id` INT(10) UNSIGNED NOT NULL ,
  `saved_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `festado_FKIndex1` (`festados_id` ASC) ,
  INDEX `festado_FKIndex2` (`factura_id` ASC) ,
  CONSTRAINT `festado_ibfk_1`
    FOREIGN KEY (`festados_id` )
    REFERENCES `facturacion`.`festados` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `festado_ibfk_2`
    FOREIGN KEY (`factura_id` )
    REFERENCES `facturacion`.`factura` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 110
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`historial`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`historial` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ejercicio_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `usuario` VARCHAR(32) NOT NULL ,
  `descripcion` VARCHAR(768) NOT NULL ,
  `controlador` VARCHAR(32) NOT NULL ,
  `accion` VARCHAR(32) NOT NULL ,
  `saved_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `historial_FKIndex1` (`ejercicio_id` ASC) ,
  CONSTRAINT `historial_ibfk_1`
    FOREIGN KEY (`ejercicio_id` )
    REFERENCES `facturacion`.`ejercicio` (`id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 180
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;


-- -----------------------------------------------------
-- Table `facturacion`.`usuario`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `facturacion`.`usuario` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `login` VARCHAR(16) NOT NULL ,
  `pass` VARCHAR(50) NOT NULL ,
  `nombre` VARCHAR(30) NOT NULL ,
  `ap` VARCHAR(20) NOT NULL ,
  `am` VARCHAR(20) NOT NULL ,
  `mail` VARCHAR(80) NULL DEFAULT NULL ,
  `saved_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
