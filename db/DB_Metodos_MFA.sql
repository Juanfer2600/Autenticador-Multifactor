-- Table: public.Metodos_MFA

-- DROP TABLE IF EXISTS public."Metodos_MFA";

CREATE TABLE IF NOT EXISTS public."Metodos_MFA"
(
    tipo_metodo character varying(50) COLLATE pg_catalog."default" NOT NULL,
    id_metodo bigint NOT NULL,
    CONSTRAINT "Metodos_MFA_pkey" PRIMARY KEY (id_metodo)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Metodos_MFA"
    OWNER to postgres;