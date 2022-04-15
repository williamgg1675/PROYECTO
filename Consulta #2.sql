--  lenguaje en ingles de mysql
SET GLOBAL time_zone = '-3:00'; 
-- creacion de la BD para sistema de Ventas Malca hno
-- Sentencia que soluciona el mensaje de error al 
-- crear conecion odbc (caching_sha2_password no se pudo encontrar el módulo especificado)
 ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'yourpassword';

CREATE DATABASE BD_VENTASM;
use BD_VENTASM;bd_ventasm
-- DROP DATABASE MOTOREPUESTOS;

/*PARA DESHABILITAR EL MODO SEGURO SE DEBE CORRE LA SGTE SENTENCIA*/
SET SQL_SAFE_UPDATES = 0;
USE facturas

usuariosusuariosusuariosusuariosusuariosusuarios2

insert into usuarios(COD_USER,UO,NOMBRES,APELLIDOS,USUARIO,PASS,TIPO) values ('001','01','WILLIAM','GARNIQUE','WGARNIQUE','1675WE.','A');
-- select * from usuarios;
CREATE TABLE ORIGEN
(
-- tabla creada el 02042019
COD_UO CHAR(2) NOT NULL PRIMARY KEY,
ORIGEN VARCHAR(20) not null ,
DIRECCION varchar(45),
IND CHAR(1) DEFAULT'S'
);
select * from origen;
INSERT INTO ORIGEN(COD_UO,ORIGEN,DIRECCION) VALUES('01','BAGUA','Alto Amazonas N° 125');
-- drop table proveedor;
CREATE TABLE PROVEEDOR
( -- creada el 04.04.2019
RUC char(11) primary key,
RazonSocial varchar(80),
Referencia varchar(50),
Direccion varchar(80),
Telefonos varchar(50),
IND CHAR(1) DEFAULT'S'
);
select * from proveedor;
CREATE TABLE DOCUMENTO(
	Cod_Doc char(2) NOT NULL primary key,
	Documento varchar(30) NOT NULL,
	IND char(1) default 'S'
) ENGINE=InnoDB;
-- select * from documento;
-- drop table documento;
insert into Documento(Cod_doc,Documento) values('01','Boleta Venta');
insert into Documento(Cod_doc,Documento) values('02','Factura');
-- creacion de la tabla Alm_ingreso
-- drop table alm_ingreso;
CREATE TABLE ALM_INGRESO(
	Numero_doc char(11) NOT NULL,
	UO char(2) NOT NULL,
	RUC_DNI char(11) NOT NULL,
	Monto numeric(18, 2) NOT NULL check (Monto>0),
	Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,-- datetime NOT NULL automatico
	Anno char(4) NOT NULL,
	Mes int NOT NULL,
    Cod_tipodoc char(2) not null,-- nuevo campo
    NumDoc varchar(15) not null,-- nuevo campo
    Fechadoc datetime NOT NULL, -- nuevo campo
	Usuario varchar(25) NOT NULL,
	Observacion varchar(120) NULL,
	Estado char(1) NOT NULL check (Estado='P' or Estado='C'),-- P=Pagado o C=credito
	IND char(1) default 'S',
    primary key (Numero_doc,uo),
    foreign key (RUC_DNI) references PROVEEDOR(RUC),
    foreign key (Cod_tipodoc) references DOCUMENTO(Cod_Doc),
    foreign key (UO) references ORIGEN(cod_uo)
)ENGINE=InnoDB;
 select * from alm_ingreso;
select * from alm_ingreso_detalle;
-- drop table alm_ingreso;
-- delete from alm_ingreso where numero_doc='20190100002';
-- creacion de la tabla Producto 
delete from catalogo;
CREATE TABLE CATALOGO(
-- creado el 10.04.2019 tabla Producto
	Cod_Catalogo char(10) not null primary key,-- cod_prod
	Producto varchar(80) NOT NULL,
	IND char(1) NOT NULL default 'S'
);
-- select * from catalogo;
-- creacion de la tabla unidadMedida
CREATE TABLE UNIDADMEDIDA(
-- creado el 10.04.2019 tabla Unidad Medida
	Id_med char(2) NOT NULL primary key,
	UnidadMedida varchar(40) NOT NULL,
	IND char(1) NOT NULL default 'S'
);   
-- select * from unidadmedida;

CREATE TABLE MARCA(
--  tabla creado el 10.04.2019
	Id_Marca char(4) NOT NULL primary key,
	Marca varchar(55) NOT NULL,
	IND char(1) NOT NULL default'S'
);
-- select * from marca;
CREATE TABLE MODELO(
--  tabla creado el 10.04.2019
	Cod_Mod char(4) NOT NULL primary key,
	Modelo varchar(45) NOT NULL,
	IND char(1) NOT NULL default'S'
);
-- select * from modelo;

CREATE TABLE CAPACIDAD(
-- tabla creado el 10.04.2019
	Codigo char(4) NOT NULL primary key,
	Capacidad varchar(45) NOT NULL,
	IND char(1) NOT NULL default'S'
    );
    -- select * from capacidad;
    -- drop table capacidad;
-- creacion de la tabla alm_ingreso_detall
CREATE TABLE ALM_INGRESO_DETALLE( 
--  tabla creado el 10.04.2019
	Num_doc char(11) NOT NULL,
	UO char(2) NOT NULL,
	Sec char(2) NOT NULL,
	Cod_tipo char(1) not NULL check (Cod_tipo='1' or Cod_tipo='2'),-- si es laptop(1) si es accesorio (2)
	Cod_Prod char(10) NOT NULL,
	Cod_medida char(2) NOT NULL,
	Cod_marca char(4) NOT NULL,
	Cod_mod char(4) NOT NULL,-- modelo cambio a 4
	Cod_capac char(4) NOT NULL,
	Cod_bincard char(26) NOT NULL,
	Cod_kardex char(6) NOT NULL,-- ddmmsec 
	Serie varchar(35) NULL,
	Cant decimal(18, 2) NOT NULL check (Cant>0),
	PU decimal(18, 4) NOT NULL check (PU>0),
	SubTotal decimal(18, 2) NOT NULL,
	Mes smallint NOT NULL,
	Anno char(4) NOT NULL,
	Estado char(1) NOT NULL check (Estado='P' or Estado='C'),-- C=credito, P=Pagado
	IND char(1) NOT NULL default 'S',
    primary key (Num_doc,UO,Sec),
    foreign key (Cod_Prod) references CATALOGO(Cod_Catalogo),
    foreign key (Cod_medida) references UNIDADMEDIDA(Id_med),
    foreign key (Cod_marca) references MARCA(Id_Marca),
    foreign key (Cod_mod) references MODELO(Cod_Mod),
    foreign key (Cod_capac) references CAPACIDAD(Codigo)
)ENGINE=InnoDB;

-- select * from alm_ingreso;
select * from CTRL_CORR;
select * from alm_ingreso_detalle;
select * from alm_ingreso;
select * from alm_stock;
select * from alm_movimientos;
/*Elimna Registro*/
delete  from alm_ingreso_detalle;
delete from alm_ingreso;
delete from alm_stock;
delete from alm_movimientos;
-- drop table ALM_INGRESO_DETALLE;
CREATE TABLE ALM_STOCK(
	Cod_Bincard char(26) NOT NULL,
	UO char(2) NOT NULL,
	Mes int NOT NULL,
	Anno char(4) NOT NULL,
    Kardex char(6) NOT NULL,
	Cod_Catalogo char(10) NOT NULL,
	Stock_Ini decimal(18, 2) NOT NULL,
	Stock_Fin decimal(18, 2) NOT NULL,
	Precio_Ini decimal(18, 2) NOT NULL,
	Precio_Fin decimal(18, 2) NOT NULL,
    Estado char(1) not null check (Estado='O' or Estado='C'), -- O=Open C=closed nuevo campo
	IND char(1) NOT NULL default'S',
	PRIMARY KEY (Cod_Bincard,UO,Mes,Anno,Kardex)
)ENGINE=InnoDB;
-- select * from alm_stock;
-- drop table alm_stock;
CREATE TABLE ALM_MOVIMIENTOS(
	Cod_Bincard char(26) NOT NULL,
	Cod_Kardex char(6) NOT NULL,
	UO char(2) NOT NULL,
	Mes int NOT NULL,
	Anno char(4) NOT NULL,
	Cod_Catalogo char(10) NOT NULL,
	Serie varchar(35) NULL,
	Stock_Ini decimal(18, 2) NOT NULL,
	Stock_Final decimal(18, 2) NOT NULL,
	Precio_ini decimal(18, 2) NOT NULL,
	Precio_fin decimal(18, 2) NULL,
	Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,-- fecha del sistema
	NUM_ING_SAL char(11) NOT NULL,
    Tipo char(1) check (Tipo='I' or Tipo='S'),-- ingreso o Salida Nuevo campo
	IND char(1) NOT NULl default 'S',
	PRIMARY KEY (Cod_Bincard,Cod_Kardex,UO,Fecha)
)ENGINE=InnoDB;
-- select * from alm_movimientos;
-- drop table alm_movimientos;
CREATE TABLE CTRL_CORR(
	Tipo int NULL,
	NSal int NULL,
	NIngreso int NULL,
	NFact int NULL,
	NBol int NULL,
	SerieF char(3) NULL,
	SerieB char(3) NULL,
	Anno char(4) not NULL ,
    IND char(1) default 'S',
    primary key (Anno,IND)
)ENGINE=InnoDB;
-- select * from Ctrl_corr;
insert Ctrl_corr(Tipo,NSal,NIngreso,NFact,NBol,SerieF,SerieB,Anno)
values(1,1,2,1,1,'001','001','2019');
update Ctrl_corr set NIngreso=2;
SELECT * FROM ALM_INGRESO;
SELECT * FROM PROVEEDOR;
select * from documento;
-- ------------
-- LISTADO DE COMPRAS INGRESADAS---
DROP PROCEDURE IF EXISTS SP_LISTACOMPRAS;
DELIMITER $$
create PROCEDURE SP_LISTACOMPRAS(
IN FechaI datetime,
IN FechaF datetime,
IN UO CHAR(2)
) 
BEGIN
SELECT I.Numero_doc,Fecha as FechaRegistro,I.RUC_DNI,P.RAZONSOCIAL,CONCAT(D.Documento," N° ",I.NUMDOC) AS DOCUMENTO,I.FECHADOC,MONTO,CASE I.IND WHEN 'S' THEN 'ACTIVO' ELSE 'ANULADO' END AS ESTADO
FROM ALM_INGRESO I
INNER JOIN PROVEEDOR P ON P.RUC=I.RUC_DNI
INNER JOIN DOCUMENTO D ON D.Cod_Doc=I.cod_tipodoc
WHERE date(I.FECHA) BETWEEN FechaI AND FechaF AND I.UO=UO
order by I.Numero_doc ASC;
END$$
delimiter ;

CALL SP_LISTACOMPRAS ('2019/05/01','2019/05/01','01');

/* CONSULTAS EMITIDAS EL DIA 26122021*/
USE facturacion17;
SELECT * FROM emisor;
UPDATE emisor SET RAZON_SOCIAL="WILLIAM EDWIN GARNIQUE GONZALES", NOMBRE_COMERCIAL="EMPRESA DE SERVICIOS HARDSOFT",
DIRECCION='TARAPACA Nº 520 - MONSEFU' WHERE ID='1'; 
SELECT * FROM TIPO_COMPROBANTE;
SELECT * FROM TIPO_DOCUMENTO;
DELETE FROM TIPO_DOCUMENTO WHERE CODIGO IN ('B','F','G','H')
