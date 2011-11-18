CREATE TABLE ejercicio (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  annio INTEGER UNSIGNED NOT NULL,
  saved_at DATETIME NOT NULL,
  modified_at DATETIME NOT NULL,
  PRIMARY KEY(id)
)
TYPE=InnoDB;

INSERT INTO ejercicio (id, annio) VALUES(1, 2009);


CREATE TABLE festados (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NULL,
  singular VARCHAR(50) NULL,
  plural VARCHAR(50) NULL,
  clave VARCHAR(5) NULL,
  PRIMARY KEY(id)
)
TYPE=InnoDB;

CREATE TABLE fiscal (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  rfc VARCHAR(20) NOT NULL,
  razon VARCHAR(255) NOT NULL,
  domicilio VARCHAR(255) NOT NULL,
  colonia VARCHAR(255) NOT NULL,
  cp VARCHAR(20) NOT NULL,
  PRIMARY KEY(id)
)
TYPE=InnoDB;

CREATE TABLE contacto (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(5) NULL,
  cargo VARCHAR(30) NULL,
  nombre VARCHAR(30) NOT NULL,
  ap VARCHAR(20) NOT NULL,
  am VARCHAR(20) NOT NULL,
  tel VARCHAR(30) NULL,
  cel VARCHAR(30) NULL,
  domicilio VARCHAR(254) NULL,
  trunk VARCHAR(30) NULL,
  mail VARCHAR(60) NOT NULL,
  sexo VARCHAR(1) NOT NULL,
  observaciones VARCHAR(256) NULL,
  saved_at DATETIME NOT NULL DEFAULT 0000-00-00,
  modified_at DATETIME NOT NULL,
  PRIMARY KEY(id)
)
TYPE=InnoDB;

CREATE TABLE usuario (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  login VARCHAR(16) NOT NULL,
  pass VARCHAR(50) NOT NULL,
  nombre VARCHAR(30) NOT NULL,
  ap VARCHAR(20) NOT NULL,
  am VARCHAR(20) NOT NULL,
  mail VARCHAR(80) NULL,
  saved_at DATETIME NOT NULL,
  modified_at DATETIME NOT NULL,
  PRIMARY KEY(id)
)
TYPE=InnoDB;

INSERT INTO usuario (id, login, pass, nombre, ap, am, mail) VALUES(1, 'root', 'd033e22ae348aeb5660fc2140aec35850c4da997', '_', '_', '_', '_');


CREATE TABLE historial (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  ejercicio_id INTEGER UNSIGNED NULL,
  usuario VARCHAR(32) NOT NULL,
  descripcion VARCHAR(768) NOT NULL,
  controlador VARCHAR(32) NOT NULL,
  accion VARCHAR(32) NOT NULL,
  saved_at DATETIME NOT NULL,
  PRIMARY KEY(id),
  INDEX historial_FKIndex1(ejercicio_id),
  FOREIGN KEY(ejercicio_id)
    REFERENCES ejercicio(id)
      ON DELETE SET NULL
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE dependencia (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  fiscal_id INTEGER UNSIGNED NOT NULL,
  ejercicio_id INTEGER UNSIGNED NOT NULL,
  clave VARCHAR(8) NOT NULL,
  nombre VARCHAR(256) NOT NULL,
  saved_at DATETIME NOT NULL DEFAULT '0000-00-00',
  modified_at DATETIME NOT NULL DEFAULT '0000-00-00',
  externo BOOL NOT NULL,
  PRIMARY KEY(id),
  INDEX dependencia_FKIndex1(ejercicio_id),
  INDEX dependencia_FKIndex2(fiscal_id),
  FOREIGN KEY(ejercicio_id)
    REFERENCES ejercicio(id)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,
  FOREIGN KEY(fiscal_id)
    REFERENCES fiscal(id)
      ON DELETE RESTRICT
      ON UPDATE CASCADE
)
TYPE=InnoDB;

INSERT INTO dependencia (id, ejercicio_id, clave, nombre) VALUES(1, 1, 'DGM', 'Direccion General de Medios');


CREATE TABLE depcontacto (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  ejercicio_id INTEGER UNSIGNED NOT NULL,
  contacto_id INTEGER UNSIGNED NOT NULL,
  dependencia_id INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(id),
  INDEX depcontacto_FKIndex1(dependencia_id),
  INDEX depcontacto_FKIndex2(contacto_id),
  INDEX depcontacto_FKIndex3(ejercicio_id),
  FOREIGN KEY(dependencia_id)
    REFERENCES dependencia(id)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,
  FOREIGN KEY(contacto_id)
    REFERENCES contacto(id)
      ON DELETE CASCADE
      ON UPDATE RESTRICT,
  FOREIGN KEY(ejercicio_id)
    REFERENCES ejercicio(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE factura (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  ejercicio_id INTEGER UNSIGNED NOT NULL,
  festados_id INTEGER UNSIGNED NOT NULL,
  dependencia_id INTEGER UNSIGNED NOT NULL,
  folio VARCHAR(32) NOT NULL,
  fecha DATE NOT NULL DEFAULT '0000-00-00',
  razon VARCHAR(256) NOT NULL,
  rfc VARCHAR(16) NOT NULL,
  domicilio VARCHAR(256) NOT NULL,
  colonia VARCHAR(256) NOT NULL,
  cpostal VARCHAR(10) NOT NULL,
  subtotal DOUBLE(11,2) NOT NULL DEFAULT 0,
  iva DOUBLE(11,2) NOT NULL DEFAULT 0,
  total DOUBLE(11,2) NOT NULL DEFAULT 0,
  observaciones VARCHAR(254) NULL,
  enviada DATE NOT NULL DEFAULT '0000-00-00',
  recibida DATE NOT NULL DEFAULT '0000-00-00',
  saved_at DATETIME NOT NULL,
  modified_at DATETIME NOT NULL,
  PRIMARY KEY(id),
  INDEX factura_FKIndex1(dependencia_id),
  INDEX factura_FKIndex2(festados_id),
  INDEX factura_FKIndex3(ejercicio_id),
  FOREIGN KEY(dependencia_id)
    REFERENCES dependencia(id)
      ON DELETE RESTRICT
      ON UPDATE CASCADE,
  FOREIGN KEY(festados_id)
    REFERENCES festados(id)
      ON DELETE RESTRICT
      ON UPDATE CASCADE,
  FOREIGN KEY(ejercicio_id)
    REFERENCES ejercicio(id)
      ON DELETE RESTRICT
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE concepto (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  factura_id INTEGER UNSIGNED NOT NULL,
  descripcion VARCHAR(300) NOT NULL,
  cantidad INTEGER UNSIGNED NOT NULL,
  unitario DOUBLE(11,2) NOT NULL,
  monto DOUBLE(11,2) NOT NULL,
  PRIMARY KEY(id),
  INDEX orden_FKIndex2(factura_id),
  FOREIGN KEY(factura_id)
    REFERENCES factura(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;

CREATE TABLE festado (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  factura_id INTEGER UNSIGNED NOT NULL,
  festados_id INTEGER UNSIGNED NOT NULL,
  saved_at DATETIME NOT NULL,
  modified_at DATETIME NOT NULL,
  PRIMARY KEY(id),
  INDEX festado_FKIndex1(festados_id),
  INDEX festado_FKIndex2(factura_id),
  FOREIGN KEY(festados_id)
    REFERENCES festados(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(factura_id)
    REFERENCES factura(id)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=InnoDB;


