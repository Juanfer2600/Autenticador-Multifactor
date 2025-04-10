CREATE TABLE IF NOT EXISTS public."Usuario"
(
    id_usuario bigint NOT NULL,
    nombre_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL,
    correo_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL,
    "contraseña_hash" character varying(50) COLLATE pg_catalog."default" NOT NULL,
    metodos_mfa character varying(50) COLLATE pg_catalog."default" NOT NULL,
    id_metodo bigint NOT NULL,
    CONSTRAINT "Usuario_pkey" PRIMARY KEY (id_usuario),
    CONSTRAINT "Usuario_id_metodo_fkey" FOREIGN KEY (id_metodo)
        REFERENCES public."Metodos_MFA" (id_metodo) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
)

--------------------------------USUARIO MYSQL---------------------------------

DROP TABLE IF EXISTS Usuario;
CREATE TABLE IF NOT EXISTS Usuario (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    apellido_usuario VARCHAR(50) NOT NULL,
    correo_usuario VARCHAR(50) NOT NULL,
    password VARCHAR(250) NOT NULL,
    metodos_mfa VARCHAR(250) NULL,
    tipo_usuario varchar(50) NOT NULL
)