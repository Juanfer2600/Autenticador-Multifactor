-- Table: public.Huella_Dactilar

-- DROP TABLE IF EXISTS public."Huella_Dactilar";

CREATE TABLE IF NOT EXISTS public."Huella_Dactilar"
(
    huella_usuario character varying COLLATE pg_catalog."default" NOT NULL
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Huella_Dactilar"
    OWNER to postgres;


-- --------------------HUELLA DACTILAR MYSQL------------------------------------

DROP TABLE IF EXISTS Huella_Dactilar;

CREATE TABLE Huella_Dactilar (
    huella_usuario VARCHAR(255) NOT NULL
);