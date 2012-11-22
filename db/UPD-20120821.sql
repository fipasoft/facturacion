# Ajustes de codificacion
ALTER SCHEMA facturacion  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_spanish_ci ;

# Crea la tabla metodopago
CREATE  TABLE IF NOT EXISTS metodopago (
  id INT(10) NOT NULL ,
  clave VARCHAR(8) NOT NULL ,
  nombre VARCHAR(45) NOT NULL ,
  PRIMARY KEY (id) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish_ci;

# Registros de la tabla metodopago
INSERT INTO metodopago (id, clave, nombre) VALUES (1, 'NID', 'No identificado');
INSERT INTO metodopago (id, clave, nombre) VALUES (2, 'EFE', 'Efectivo');
INSERT INTO metodopago (id, clave, nombre) VALUES (3, 'CHQ', 'Cheque');
INSERT INTO metodopago (id, clave, nombre) VALUES (4, 'TRN', 'Transferencia electrónica');
INSERT INTO metodopago (id, clave, nombre) VALUES (5, 'TCR', 'Tarjeta de crédito');
INSERT INTO metodopago (id, clave, nombre) VALUES (6, 'TDB', 'Tarjeta de débito');
INSERT INTO metodopago (id, clave, nombre) VALUES (7, 'TSV', 'Tarjeta de servicio');
INSERT INTO metodopago (id, clave, nombre) VALUES (8, 'MON', 'Monedero electrónico');

# Crea el indice a la tabla metodo y agrega el campo ctapago
ALTER TABLE factura 
  ADD COLUMN metodopago_id INT(10) NOT NULL DEFAULT 1 AFTER dependencia_id , 
  ADD COLUMN ctapago VARCHAR(18) NULL DEFAULT NULL  AFTER total , 
  ADD CONSTRAINT fk_factura_metodopago1
  FOREIGN KEY (metodopago_id )
  REFERENCES metodopago (id )
  ON DELETE RESTRICT
  ON UPDATE RESTRICT
, ADD INDEX fk_factura_metodopago1 (metodopago_id ASC) ;
