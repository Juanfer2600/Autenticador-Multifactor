-- Table: public.Token_OTP

-- DROP TABLE IF EXISTS public."Token_OTP";

CREATE TABLE IF NOT EXISTS public."Token_OTP"
(
    clave_secreta text COLLATE pg_catalog."default"
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public."Token_OTP"
    OWNER to postgres;