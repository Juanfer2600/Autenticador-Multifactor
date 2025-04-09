-- Table: public.Reconocimiento_Facial

-- DROP TABLE IF EXISTS public."Reconocimiento_Facial";

CREATE TABLE IF NOT EXISTS public."Reconocimiento_Facial"
(
    imagen_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Reconocimiento_Facial"
    OWNER to postgres;

--------------------RECONOCIMIENTO FACIAL MYSQL------------------------------------

DROP TABLE IF EXISTS `Reconocimiento_Facial`;
CREATE TABLE `Reconocimiento_Facial` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `imagen_usuario` VARCHAR(50) NOT NULL
);