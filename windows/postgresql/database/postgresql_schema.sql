--
-- PostgreSQL database dump
--


-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: user_rol; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_rol AS ENUM (
    'usuario',
    'admin',
    'superadmin'
);


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actividades_universitarias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actividades_universitarias (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    tipo character varying(150),
    lugar character varying(150),
    fecha_desde date,
    fecha_hasta date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: actividades_universitarias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actividades_universitarias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actividades_universitarias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actividades_universitarias_id_seq OWNED BY public.actividades_universitarias.id;


--
-- Name: activities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activities (
    id bigint NOT NULL,
    user_id bigint,
    accion character varying(255) CONSTRAINT activities_action_not_null NOT NULL,
    tipo_entidad character varying(255) CONSTRAINT activities_subject_type_not_null NOT NULL,
    entidad_id bigint,
    descripcion character varying(255) CONSTRAINT activities_description_not_null NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    direccion_ip character varying(45),
    navegador text,
    valores_anteriores text,
    valores_nuevos text,
    datos_peticion text,
    metodo character varying(10),
    ruta character varying(255)
);


--
-- Name: activities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activities_id_seq OWNED BY public.activities.id;


--
-- Name: antecedente_documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.antecedente_documentos (
    id bigint NOT NULL,
    antecedente_id bigint NOT NULL,
    tipo_documento character varying(100) NOT NULL,
    numero_providencia character varying(50),
    fecha_emision date,
    institucion_emisora character varying(150),
    folio_numero character varying(50),
    folio_hojas smallint,
    observaciones text,
    archivo character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: antecedente_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.antecedente_documentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: antecedente_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.antecedente_documentos_id_seq OWNED BY public.antecedente_documentos.id;


--
-- Name: antecedentes_servicio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.antecedentes_servicio (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    institucion character varying(150) NOT NULL,
    es_institucion_actual boolean DEFAULT false NOT NULL,
    cargo character varying(150),
    condicion_laboral character varying(100),
    dedicacion character varying(100),
    fecha_inicio date NOT NULL,
    fecha_fin date,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: antecedentes_servicio_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.antecedentes_servicio_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: antecedentes_servicio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.antecedentes_servicio_id_seq OWNED BY public.antecedentes_servicio.id;


--
-- Name: areas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.areas (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: areas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.areas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: areas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.areas_id_seq OWNED BY public.areas.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cargos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cargos (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cargos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cargos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cargos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cargos_id_seq OWNED BY public.cargos.id;


--
-- Name: changelogs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.changelogs (
    id bigint NOT NULL,
    nombre_autor character varying(255) NOT NULL,
    correo_autor character varying(255) NOT NULL,
    hash_commit character varying(40) NOT NULL,
    mensaje_commit text NOT NULL,
    descripcion text,
    tipo character varying(255) DEFAULT 'change'::character varying NOT NULL,
    seccion character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: changelogs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.changelogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: changelogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.changelogs_id_seq OWNED BY public.changelogs.id;


--
-- Name: dedicaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dedicaciones (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: dedicaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dedicaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dedicaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dedicaciones_id_seq OWNED BY public.dedicaciones.id;


--
-- Name: documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.documentos (
    id bigint NOT NULL,
    expediente_id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    archivo character varying(255) NOT NULL,
    estado character varying(255) DEFAULT 'en_revision'::character varying NOT NULL,
    nota_rechazo text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT documentos_estado_check CHECK (((estado)::text = ANY ((ARRAY['en_revision'::character varying, 'aprobado'::character varying, 'rechazado'::character varying])::text[])))
);


--
-- Name: documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.documentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.documentos_id_seq OWNED BY public.documentos.id;


--
-- Name: estudios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.estudios (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    nivel_instruccion_id bigint,
    especialidad character varying(150),
    casa_estudio character varying(150),
    casa_egreso character varying(150),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: estudios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.estudios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: estudios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.estudios_id_seq OWNED BY public.estudios.id;


--
-- Name: expedientes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.expedientes (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    foto_carnet character varying(255),
    estado_global integer DEFAULT 0 NOT NULL,
    notas_admin text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    carta_aprobacion character varying(255)
);


--
-- Name: expedientes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.expedientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: expedientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.expedientes_id_seq OWNED BY public.expedientes.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: formulas_prestaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.formulas_prestaciones (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    codigo character varying(50) NOT NULL,
    descripcion text,
    conceptos json,
    variables json,
    formula_matematica text,
    explicacion_variables json,
    ejemplo_calculo text,
    observaciones text,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: formulas_prestaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.formulas_prestaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: formulas_prestaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.formulas_prestaciones_id_seq OWNED BY public.formulas_prestaciones.id;


--
-- Name: grados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.grados (
    id bigint NOT NULL,
    nombre character varying(50) NOT NULL,
    codigo character varying(20) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: grados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.grados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: grados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.grados_id_seq OWNED BY public.grados.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: niveles_instruccion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.niveles_instruccion (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    codigo character varying(20) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: niveles_instruccion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.niveles_instruccion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: niveles_instruccion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.niveles_instruccion_id_seq OWNED BY public.niveles_instruccion.id;


--
-- Name: nomina_trabajador; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.nomina_trabajador (
    id bigint NOT NULL,
    nomina_id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    sueldo_base numeric(12,2),
    prima_familiar numeric(12,2),
    prima_hijo numeric(12,2),
    prima_hijos_discapacidad numeric(12,2),
    prima_actividad_universitaria numeric(12,2),
    prima_profesionalizacion numeric(12,2),
    prima_responsabilidad numeric(12,2),
    complemento_prima_responsabilidad numeric(12,2),
    prima_antiguedad numeric(12,2),
    cesta_ticket numeric(12,2),
    total_asignacion numeric(12,2),
    sso numeric(12,2),
    lpf numeric(12,2),
    faov numeric(12,2),
    aporte_ipasme numeric(12,2),
    aporte_caja_ahorro numeric(12,2),
    prestamo_caja_ahorro numeric(12,2),
    isr numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    horas_extras numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total_deduccion numeric(12,2),
    neto_a_cobrar numeric(12,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: nomina_trabajador_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.nomina_trabajador_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: nomina_trabajador_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.nomina_trabajador_id_seq OWNED BY public.nomina_trabajador.id;


--
-- Name: nominas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.nominas (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    periodo character varying(10) NOT NULL,
    estado character varying(20) DEFAULT 'borrador'::character varying NOT NULL,
    total_general numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: nominas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.nominas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: nominas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.nominas_id_seq OWNED BY public.nominas.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    from_user_id bigint,
    titulo character varying(255) CONSTRAINT notifications_title_not_null NOT NULL,
    mensaje text CONSTRAINT notifications_message_not_null NOT NULL,
    tipo character varying(255) DEFAULT 'info'::character varying CONSTRAINT notifications_type_not_null NOT NULL,
    leida boolean DEFAULT false CONSTRAINT notifications_is_read_not_null NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: prestaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.prestaciones (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    anios_servicio integer NOT NULL,
    monto numeric(12,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    detalles json,
    sueldo_integral numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total_primas numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    porcentaje_jubilacion numeric(5,2) DEFAULT '100'::numeric NOT NULL,
    tasa_cambio_id bigint,
    tasa_utilizada numeric(12,4),
    moneda_tasa character varying(10),
    fecha_tasa_utilizada timestamp(0) without time zone,
    fuente_tasa character varying(100),
    calculado_por_user_id bigint,
    fideicomiso_monto numeric(12,2),
    fideicomiso_observaciones text,
    fideicomiso_documento character varying(255)
);


--
-- Name: prestaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.prestaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: prestaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.prestaciones_id_seq OWNED BY public.prestaciones.id;


--
-- Name: primas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.primas (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(150) NOT NULL,
    monto numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    valor numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    fecha_vigencia date
);


--
-- Name: primas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.primas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: primas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.primas_id_seq OWNED BY public.primas.id;


--
-- Name: recibos_pago; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recibos_pago (
    id bigint NOT NULL,
    expediente_id bigint NOT NULL,
    periodo character varying(7),
    concepto character varying(150),
    monto_usd numeric(12,2),
    archivo character varying(255) NOT NULL,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: recibos_pago_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recibos_pago_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recibos_pago_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recibos_pago_id_seq OWNED BY public.recibos_pago.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: solicitudes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.solicitudes (
    id bigint NOT NULL,
    trabajador_id bigint NOT NULL,
    fecha_solicitud date NOT NULL,
    estado character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    periodo character varying(20),
    tipo_jubilacion character varying(100),
    observaciones text,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT solicitudes_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'revision'::character varying, 'aprobado'::character varying, 'rechazado'::character varying])::text[])))
);


--
-- Name: solicitudes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.solicitudes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: solicitudes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.solicitudes_id_seq OWNED BY public.solicitudes.id;


--
-- Name: sueldos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sueldos (
    id bigint NOT NULL,
    grado_id bigint NOT NULL,
    nivel_instruccion_id bigint NOT NULL,
    sueldo_base numeric(12,2) NOT NULL,
    complemento_prima_cargo numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    porcentaje_prima_cargo numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sueldos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sueldos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sueldos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sueldos_id_seq OWNED BY public.sueldos.id;


--
-- Name: tasas_cambio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tasas_cambio (
    id bigint NOT NULL,
    tasa numeric(12,4) NOT NULL,
    moneda_origen character varying(10) DEFAULT 'USD'::character varying NOT NULL,
    moneda_destino character varying(10) DEFAULT 'VES'::character varying NOT NULL,
    fuente character varying(100),
    tipo character varying(255) DEFAULT 'manual'::character varying NOT NULL,
    observacion text,
    usuario_id bigint,
    activa boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    fecha_consulta timestamp(0) without time zone,
    CONSTRAINT tasas_cambio_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['automatica'::character varying, 'manual'::character varying])::text[])))
);


--
-- Name: tasas_cambio_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tasas_cambio_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tasas_cambio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tasas_cambio_id_seq OWNED BY public.tasas_cambio.id;


--
-- Name: tipos_contrato; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tipos_contrato (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: tipos_contrato_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tipos_contrato_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipos_contrato_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tipos_contrato_id_seq OWNED BY public.tipos_contrato.id;


--
-- Name: tipos_jubilacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tipos_jubilacion (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: tipos_jubilacion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tipos_jubilacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipos_jubilacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tipos_jubilacion_id_seq OWNED BY public.tipos_jubilacion.id;


--
-- Name: trabajadores; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.trabajadores (
    id bigint NOT NULL,
    cedula character varying(255) NOT NULL,
    nombres character varying(255) NOT NULL,
    apellidos character varying(255) NOT NULL,
    cuenta_bancaria character varying(255),
    genero character varying(255) NOT NULL,
    grado_nivel character varying(255) NOT NULL,
    cargo character varying(255) NOT NULL,
    unidad_departamento character varying(255) NOT NULL,
    fecha_nacimiento date NOT NULL,
    edad integer NOT NULL,
    fecha_ingreso date NOT NULL,
    anos_servicio_inst integer NOT NULL,
    anos_servicio_externo integer NOT NULL,
    total_anos_servicio integer NOT NULL,
    nivel_instruccion integer NOT NULL,
    numero_hijos integer NOT NULL,
    hijos_discapacidad integer DEFAULT 0 NOT NULL,
    porcentaje_antiguedad numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    porcentaje_caja_ahorro numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    codigo_empleado character varying(20),
    sueldo_base numeric(12,2),
    denominacion_salario character varying(255),
    tabulador character varying(50),
    porcentaje_prima_cargo numeric(5,2),
    complemento_prima_cargo numeric(12,2),
    es_jefe_coordinador boolean DEFAULT false NOT NULL,
    cesta_ticket numeric(12,2),
    prima_profesionalizacion numeric(12,2),
    sugau numeric(12,2),
    afiliacion_sifaiuty character varying(50),
    nivel_educativo_texto character varying(100),
    cargo_id bigint,
    area_id bigint,
    grado_id bigint,
    nivel_instruccion_id bigint,
    tipo_contrato_id bigint,
    asignacion character varying(255) DEFAULT 'Manual'::character varying NOT NULL,
    actividad_universitaria boolean DEFAULT false NOT NULL,
    dedicacion character varying(100),
    grado_cargo character varying(100),
    tipo_nomina character varying(20),
    unidad_id bigint,
    tipo_jubilacion_id bigint,
    CONSTRAINT trabajadores_asignacion_check CHECK (((asignacion)::text = ANY ((ARRAY['Manual'::character varying, 'Nomina'::character varying])::text[]))),
    CONSTRAINT trabajadores_genero_check CHECK (((genero)::text = ANY ((ARRAY['M'::character varying, 'F'::character varying])::text[])))
);


--
-- Name: trabajadores_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.trabajadores_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: trabajadores_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.trabajadores_id_seq OWNED BY public.trabajadores.id;


--
-- Name: unidades; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unidades (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    codigo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: unidades_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unidades_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unidades_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.unidades_id_seq OWNED BY public.unidades.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    nombre character varying(255) CONSTRAINT users_name_not_null NOT NULL,
    correo character varying(255) CONSTRAINT users_email_not_null NOT NULL,
    correo_verificado_en timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    rol public.user_rol DEFAULT 'usuario'::public.user_rol CONSTRAINT users_role_not_null NOT NULL,
    token_recordar character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar character varying(255),
    tema character varying(255) DEFAULT 'light'::character varying CONSTRAINT users_theme_not_null NOT NULL,
    idioma character varying(255) DEFAULT 'es'::character varying CONSTRAINT users_language_not_null NOT NULL,
    color_acento character varying(255) DEFAULT '#1a365d'::character varying CONSTRAINT users_accent_color_not_null NOT NULL,
    verificacion_dos_pasos boolean DEFAULT false CONSTRAINT users_two_factor_enabled_not_null NOT NULL,
    secreto_2fa character varying(255),
    notificacion_correo character varying(255) DEFAULT 'all'::character varying CONSTRAINT users_notification_email_not_null NOT NULL,
    notificacion_sistema character varying(255) DEFAULT 'all'::character varying CONSTRAINT users_notification_system_not_null NOT NULL,
    perfil_publico boolean DEFAULT true CONSTRAINT users_profile_public_not_null NOT NULL,
    ultimo_acceso timestamp(0) without time zone,
    ultimo_acceso_ip character varying(255),
    apellido character varying(255),
    telefono character varying(255),
    fecha_nacimiento date
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: actividades_universitarias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_universitarias ALTER COLUMN id SET DEFAULT nextval('public.actividades_universitarias_id_seq'::regclass);


--
-- Name: activities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities ALTER COLUMN id SET DEFAULT nextval('public.activities_id_seq'::regclass);


--
-- Name: antecedente_documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedente_documentos ALTER COLUMN id SET DEFAULT nextval('public.antecedente_documentos_id_seq'::regclass);


--
-- Name: antecedentes_servicio id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedentes_servicio ALTER COLUMN id SET DEFAULT nextval('public.antecedentes_servicio_id_seq'::regclass);


--
-- Name: areas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.areas ALTER COLUMN id SET DEFAULT nextval('public.areas_id_seq'::regclass);


--
-- Name: cargos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos ALTER COLUMN id SET DEFAULT nextval('public.cargos_id_seq'::regclass);


--
-- Name: changelogs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.changelogs ALTER COLUMN id SET DEFAULT nextval('public.changelogs_id_seq'::regclass);


--
-- Name: dedicaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dedicaciones ALTER COLUMN id SET DEFAULT nextval('public.dedicaciones_id_seq'::regclass);


--
-- Name: documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documentos ALTER COLUMN id SET DEFAULT nextval('public.documentos_id_seq'::regclass);


--
-- Name: estudios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estudios ALTER COLUMN id SET DEFAULT nextval('public.estudios_id_seq'::regclass);


--
-- Name: expedientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expedientes ALTER COLUMN id SET DEFAULT nextval('public.expedientes_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: formulas_prestaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.formulas_prestaciones ALTER COLUMN id SET DEFAULT nextval('public.formulas_prestaciones_id_seq'::regclass);


--
-- Name: grados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grados ALTER COLUMN id SET DEFAULT nextval('public.grados_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: niveles_instruccion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.niveles_instruccion ALTER COLUMN id SET DEFAULT nextval('public.niveles_instruccion_id_seq'::regclass);


--
-- Name: nomina_trabajador id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nomina_trabajador ALTER COLUMN id SET DEFAULT nextval('public.nomina_trabajador_id_seq'::regclass);


--
-- Name: nominas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nominas ALTER COLUMN id SET DEFAULT nextval('public.nominas_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: prestaciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestaciones ALTER COLUMN id SET DEFAULT nextval('public.prestaciones_id_seq'::regclass);


--
-- Name: primas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.primas ALTER COLUMN id SET DEFAULT nextval('public.primas_id_seq'::regclass);


--
-- Name: recibos_pago id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recibos_pago ALTER COLUMN id SET DEFAULT nextval('public.recibos_pago_id_seq'::regclass);


--
-- Name: solicitudes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes ALTER COLUMN id SET DEFAULT nextval('public.solicitudes_id_seq'::regclass);


--
-- Name: sueldos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sueldos ALTER COLUMN id SET DEFAULT nextval('public.sueldos_id_seq'::regclass);


--
-- Name: tasas_cambio id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasas_cambio ALTER COLUMN id SET DEFAULT nextval('public.tasas_cambio_id_seq'::regclass);


--
-- Name: tipos_contrato id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_contrato ALTER COLUMN id SET DEFAULT nextval('public.tipos_contrato_id_seq'::regclass);


--
-- Name: tipos_jubilacion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_jubilacion ALTER COLUMN id SET DEFAULT nextval('public.tipos_jubilacion_id_seq'::regclass);


--
-- Name: trabajadores id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores ALTER COLUMN id SET DEFAULT nextval('public.trabajadores_id_seq'::regclass);


--
-- Name: unidades id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades ALTER COLUMN id SET DEFAULT nextval('public.unidades_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: actividades_universitarias actividades_universitarias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_universitarias
    ADD CONSTRAINT actividades_universitarias_pkey PRIMARY KEY (id);


--
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (id);


--
-- Name: antecedente_documentos antecedente_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedente_documentos
    ADD CONSTRAINT antecedente_documentos_pkey PRIMARY KEY (id);


--
-- Name: antecedentes_servicio antecedentes_servicio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedentes_servicio
    ADD CONSTRAINT antecedentes_servicio_pkey PRIMARY KEY (id);


--
-- Name: areas areas_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_codigo_unique UNIQUE (codigo);


--
-- Name: areas areas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: cargos cargos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_codigo_unique UNIQUE (codigo);


--
-- Name: cargos cargos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cargos
    ADD CONSTRAINT cargos_pkey PRIMARY KEY (id);


--
-- Name: changelogs changelogs_hash_commit_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.changelogs
    ADD CONSTRAINT changelogs_hash_commit_unique UNIQUE (hash_commit);


--
-- Name: changelogs changelogs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.changelogs
    ADD CONSTRAINT changelogs_pkey PRIMARY KEY (id);


--
-- Name: dedicaciones dedicaciones_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dedicaciones
    ADD CONSTRAINT dedicaciones_codigo_unique UNIQUE (codigo);


--
-- Name: dedicaciones dedicaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dedicaciones
    ADD CONSTRAINT dedicaciones_pkey PRIMARY KEY (id);


--
-- Name: documentos documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documentos
    ADD CONSTRAINT documentos_pkey PRIMARY KEY (id);


--
-- Name: estudios estudios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estudios
    ADD CONSTRAINT estudios_pkey PRIMARY KEY (id);


--
-- Name: expedientes expedientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: formulas_prestaciones formulas_prestaciones_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.formulas_prestaciones
    ADD CONSTRAINT formulas_prestaciones_codigo_unique UNIQUE (codigo);


--
-- Name: formulas_prestaciones formulas_prestaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.formulas_prestaciones
    ADD CONSTRAINT formulas_prestaciones_pkey PRIMARY KEY (id);


--
-- Name: grados grados_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grados
    ADD CONSTRAINT grados_codigo_unique UNIQUE (codigo);


--
-- Name: grados grados_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grados
    ADD CONSTRAINT grados_nombre_unique UNIQUE (nombre);


--
-- Name: grados grados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.grados
    ADD CONSTRAINT grados_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: niveles_instruccion niveles_instruccion_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.niveles_instruccion
    ADD CONSTRAINT niveles_instruccion_codigo_unique UNIQUE (codigo);


--
-- Name: niveles_instruccion niveles_instruccion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.niveles_instruccion
    ADD CONSTRAINT niveles_instruccion_pkey PRIMARY KEY (id);


--
-- Name: nomina_trabajador nomina_trabajador_nomina_id_trabajador_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nomina_trabajador
    ADD CONSTRAINT nomina_trabajador_nomina_id_trabajador_id_unique UNIQUE (nomina_id, trabajador_id);


--
-- Name: nomina_trabajador nomina_trabajador_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nomina_trabajador
    ADD CONSTRAINT nomina_trabajador_pkey PRIMARY KEY (id);


--
-- Name: nominas nominas_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nominas
    ADD CONSTRAINT nominas_codigo_unique UNIQUE (codigo);


--
-- Name: nominas nominas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nominas
    ADD CONSTRAINT nominas_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: prestaciones prestaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestaciones
    ADD CONSTRAINT prestaciones_pkey PRIMARY KEY (id);


--
-- Name: primas primas_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.primas
    ADD CONSTRAINT primas_codigo_unique UNIQUE (codigo);


--
-- Name: primas primas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.primas
    ADD CONSTRAINT primas_pkey PRIMARY KEY (id);


--
-- Name: recibos_pago recibos_pago_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recibos_pago
    ADD CONSTRAINT recibos_pago_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: solicitudes solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_pkey PRIMARY KEY (id);


--
-- Name: sueldos sueldos_grado_id_nivel_instruccion_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sueldos
    ADD CONSTRAINT sueldos_grado_id_nivel_instruccion_id_unique UNIQUE (grado_id, nivel_instruccion_id);


--
-- Name: sueldos sueldos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sueldos
    ADD CONSTRAINT sueldos_pkey PRIMARY KEY (id);


--
-- Name: tasas_cambio tasas_cambio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasas_cambio
    ADD CONSTRAINT tasas_cambio_pkey PRIMARY KEY (id);


--
-- Name: tipos_contrato tipos_contrato_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_contrato
    ADD CONSTRAINT tipos_contrato_codigo_unique UNIQUE (codigo);


--
-- Name: tipos_contrato tipos_contrato_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_contrato
    ADD CONSTRAINT tipos_contrato_pkey PRIMARY KEY (id);


--
-- Name: tipos_jubilacion tipos_jubilacion_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_jubilacion
    ADD CONSTRAINT tipos_jubilacion_codigo_unique UNIQUE (codigo);


--
-- Name: tipos_jubilacion tipos_jubilacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_jubilacion
    ADD CONSTRAINT tipos_jubilacion_pkey PRIMARY KEY (id);


--
-- Name: trabajadores trabajadores_cedula_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_cedula_unique UNIQUE (cedula);


--
-- Name: trabajadores trabajadores_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_pkey PRIMARY KEY (id);


--
-- Name: unidades unidades_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades
    ADD CONSTRAINT unidades_codigo_unique UNIQUE (codigo);


--
-- Name: unidades unidades_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades
    ADD CONSTRAINT unidades_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (correo);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: activities_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_created_at_index ON public.activities USING btree (created_at);


--
-- Name: activities_tipo_entidad_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_tipo_entidad_index ON public.activities USING btree (tipo_entidad);


--
-- Name: activities_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_user_id_index ON public.activities USING btree (user_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: documentos_estado_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documentos_estado_index ON public.documentos USING btree (estado);


--
-- Name: documentos_expediente_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documentos_expediente_id_index ON public.documentos USING btree (expediente_id);


--
-- Name: expedientes_estado_global_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX expedientes_estado_global_index ON public.expedientes USING btree (estado_global);


--
-- Name: expedientes_solicitud_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX expedientes_solicitud_id_index ON public.expedientes USING btree (solicitud_id);


--
-- Name: expedientes_trabajador_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX expedientes_trabajador_id_index ON public.expedientes USING btree (trabajador_id);


--
-- Name: formulas_prestaciones_activo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX formulas_prestaciones_activo_index ON public.formulas_prestaciones USING btree (activo);


--
-- Name: ix_accion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_accion ON public.activities USING btree (accion);


--
-- Name: ix_tipo_entidad; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_tipo_entidad ON public.activities USING btree (tipo_entidad);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: notifications_leida_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notifications_leida_index ON public.notifications USING btree (leida);


--
-- Name: notifications_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notifications_user_id_index ON public.notifications USING btree (user_id);


--
-- Name: prestaciones_trabajador_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX prestaciones_trabajador_id_index ON public.prestaciones USING btree (trabajador_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: solicitudes_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX solicitudes_created_at_index ON public.solicitudes USING btree (created_at);


--
-- Name: solicitudes_estado_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX solicitudes_estado_index ON public.solicitudes USING btree (estado);


--
-- Name: solicitudes_trabajador_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX solicitudes_trabajador_id_index ON public.solicitudes USING btree (trabajador_id);


--
-- Name: tasas_cambio_activa_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasas_cambio_activa_index ON public.tasas_cambio USING btree (activa);


--
-- Name: tasas_cambio_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tasas_cambio_created_at_index ON public.tasas_cambio USING btree (created_at);


--
-- Name: trabajadores_tipo_nomina_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX trabajadores_tipo_nomina_index ON public.trabajadores USING btree (tipo_nomina);


--
-- Name: actividades_universitarias actividades_universitarias_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividades_universitarias
    ADD CONSTRAINT actividades_universitarias_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: activities activities_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: antecedente_documentos antecedente_documentos_antecedente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedente_documentos
    ADD CONSTRAINT antecedente_documentos_antecedente_id_foreign FOREIGN KEY (antecedente_id) REFERENCES public.antecedentes_servicio(id) ON DELETE CASCADE;


--
-- Name: antecedentes_servicio antecedentes_servicio_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.antecedentes_servicio
    ADD CONSTRAINT antecedentes_servicio_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: documentos documentos_expediente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documentos
    ADD CONSTRAINT documentos_expediente_id_foreign FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) ON DELETE CASCADE;


--
-- Name: estudios estudios_nivel_instruccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estudios
    ADD CONSTRAINT estudios_nivel_instruccion_id_foreign FOREIGN KEY (nivel_instruccion_id) REFERENCES public.niveles_instruccion(id) ON DELETE SET NULL;


--
-- Name: estudios estudios_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estudios
    ADD CONSTRAINT estudios_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: expedientes expedientes_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitudes(id) ON DELETE CASCADE;


--
-- Name: expedientes expedientes_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: nomina_trabajador nomina_trabajador_nomina_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nomina_trabajador
    ADD CONSTRAINT nomina_trabajador_nomina_id_foreign FOREIGN KEY (nomina_id) REFERENCES public.nominas(id) ON DELETE CASCADE;


--
-- Name: nomina_trabajador nomina_trabajador_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nomina_trabajador
    ADD CONSTRAINT nomina_trabajador_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_from_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_from_user_id_foreign FOREIGN KEY (from_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: prestaciones prestaciones_calculado_por_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestaciones
    ADD CONSTRAINT prestaciones_calculado_por_user_id_foreign FOREIGN KEY (calculado_por_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: prestaciones prestaciones_tasa_cambio_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestaciones
    ADD CONSTRAINT prestaciones_tasa_cambio_id_foreign FOREIGN KEY (tasa_cambio_id) REFERENCES public.tasas_cambio(id) ON DELETE SET NULL;


--
-- Name: prestaciones prestaciones_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestaciones
    ADD CONSTRAINT prestaciones_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: recibos_pago recibos_pago_expediente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recibos_pago
    ADD CONSTRAINT recibos_pago_expediente_id_foreign FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) ON DELETE CASCADE;


--
-- Name: solicitudes solicitudes_trabajador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_trabajador_id_foreign FOREIGN KEY (trabajador_id) REFERENCES public.trabajadores(id) ON DELETE CASCADE;


--
-- Name: sueldos sueldos_grado_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sueldos
    ADD CONSTRAINT sueldos_grado_id_foreign FOREIGN KEY (grado_id) REFERENCES public.grados(id) ON DELETE CASCADE;


--
-- Name: sueldos sueldos_nivel_instruccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sueldos
    ADD CONSTRAINT sueldos_nivel_instruccion_id_foreign FOREIGN KEY (nivel_instruccion_id) REFERENCES public.niveles_instruccion(id) ON DELETE CASCADE;


--
-- Name: tasas_cambio tasas_cambio_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tasas_cambio
    ADD CONSTRAINT tasas_cambio_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_area_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_area_id_foreign FOREIGN KEY (area_id) REFERENCES public.areas(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_cargo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_cargo_id_foreign FOREIGN KEY (cargo_id) REFERENCES public.cargos(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_grado_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_grado_id_foreign FOREIGN KEY (grado_id) REFERENCES public.grados(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_nivel_instruccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_nivel_instruccion_id_foreign FOREIGN KEY (nivel_instruccion_id) REFERENCES public.niveles_instruccion(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_tipo_contrato_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_tipo_contrato_id_foreign FOREIGN KEY (tipo_contrato_id) REFERENCES public.tipos_contrato(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_tipo_jubilacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_tipo_jubilacion_id_foreign FOREIGN KEY (tipo_jubilacion_id) REFERENCES public.tipos_jubilacion(id) ON DELETE SET NULL;


--
-- Name: trabajadores trabajadores_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.trabajadores
    ADD CONSTRAINT trabajadores_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidades(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--


