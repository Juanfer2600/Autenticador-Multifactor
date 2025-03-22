-- Table: public.Autenticación

-- DROP TABLE IF EXISTS public."Autenticación";

CREATE TABLE IF NOT EXISTS public."Autenticación"
(
    -- Inherited from table public."Usuario": id_usuario bigint NOT NULL,
    -- Inherited from table public."Usuario": nombre_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL,
    -- Inherited from table public."Usuario": correo_usuario character varying(50) COLLATE pg_catalog."default" NOT NULL,
    -- Inherited from table public."Usuario": "contraseña_hash" character varying(50) COLLATE pg_catalog."default" NOT NULL,
    -- Inherited from table public."Usuario": metodos_mfa character varying(50) COLLATE pg_catalog."default" NOT NULL,
    -- Inherited from table public."Usuario": id_metodo bigint NOT NULL,
    -- Inherited from table public."Metodos_MFA": tipo_metodo character varying(50) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT "Autenticación_pkey" PRIMARY KEY (id_usuario, id_metodo),
    CONSTRAINT "Metodo_Relacion" FOREIGN KEY (id_metodo)
        REFERENCES public."Metodos_MFA" (id_metodo) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
    CONSTRAINT "Usuario_Relacion" FOREIGN KEY (id_usuario)
        REFERENCES public."Usuario" (id_usuario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
    INHERITS (public."Usuario", public."Metodos_MFA")

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Autenticación"
    OWNER to postgres;