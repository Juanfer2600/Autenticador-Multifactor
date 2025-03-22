-- Table: public.Reconocimiento_Facial

-- DROP TABLE IF EXISTS public."Reconocimiento_Facial";

CREATE TABLE IF NOT EXISTS public."Reconocimiento_Facial"
(
    imagen_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Reconocimiento_Facial"
    OWNER to postgres;