-- Table: public.Sesion

-- DROP TABLE IF EXISTS public."Sesion";

CREATE TABLE IF NOT EXISTS public."Sesion"
(
    id_sesion bigint NOT NULL,
    id_usuario bigint NOT NULL,
    token_sesion text COLLATE pg_catalog."default" NOT NULL,
    tiempo_expiracion date NOT NULL,
    CONSTRAINT "Sesion_pkey" PRIMARY KEY (id_sesion)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Sesion"
    OWNER to postgres;