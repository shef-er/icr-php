--
-- PostgreSQL database dump
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: svarta; Type: SCHEMA; Schema: -; Owner: svarta
--

CREATE SCHEMA svarta;


ALTER SCHEMA svarta OWNER TO svarta;

CREATE TABLE svarta.api_tokens (
  "token_id" SERIAL PRIMARY KEY,
  "user_id" uuid,
  "expiry" timestamp
);


--
-- Name: worker_status; Type: TYPE; Schema: svarta; Owner: svarta
--

CREATE TYPE svarta.worker_status AS ENUM (
    'Я',
    'Вых',
    'ОС',
    'Отг',
    'УО',
    'П',
    'Б',
    'О'
);


ALTER TYPE svarta.worker_status OWNER TO svarta;

--
-- Name: foremen(); Type: FUNCTION; Schema: svarta; Owner: svarta
--

CREATE FUNCTION svarta.foremen() RETURNS TABLE(id integer, name text, func text)
    LANGUAGE plpgsql
    AS $$
    begin
      return query with subordinates as (
        select subordinate_to as s 
          from svarta.personnel
      ) select personnel.id, personnel.name, personnel.function as func
          from svarta.personnel, subordinates
            where personnel.id = s 
              group by personnel.id
                order by personnel.name;
    end; $$;


ALTER FUNCTION svarta.foremen() OWNER TO svarta;

--
-- Name: workdays(integer, date, date); Type: FUNCTION; Schema: svarta; Owner: svarta
--

CREATE FUNCTION svarta.workdays(worker_id integer, s date, e date) RETURNS TABLE(result json)
    LANGUAGE plpgsql
    AS $$
begin
  return query with workdays_no_date as (
    select worker_report.id, hours, status, 
           name as project, project as project_id 
      from svarta.worker_report 
           left join svarta.projects 
             on projects.id = project 
               where worker_report.worker = worker_id
                 and date >= s
                 and date <= e
   ), workdays_with_date as (
     select date, json_agg(workdays_no_date) as workday_info
       from svarta.worker_report, workdays_no_date
         where workdays_no_date.id = worker_report.id 
           group by date
   ) select json_object_agg(date, workday_info) from workdays_with_date;
end; $$;


ALTER FUNCTION svarta.workdays(worker_id integer, s date, e date) OWNER TO svarta;

--
-- Name: workdays_sum(integer, date, date, integer); Type: FUNCTION; Schema: svarta; Owner: svarta
--

CREATE FUNCTION svarta.workdays_sum(worker_id integer, s date, e date, project_id integer) RETURNS TABLE(result json)
    LANGUAGE plpgsql
    AS $$
begin
  return query with sum_hours_and_days as (
    select count(*), sum(hours) from svarta.worker_report
      where 
        (hours > 0 or status = 'Я')
        and
        worker = worker_id
        and (s is null or date >= s)
        and (e is null or date <= e)
        and (project_id is null or project = project_id)
  )
  select json_build_object('days', count, 'hours', sum) 
    from sum_hours_and_days;
end; $$;


ALTER FUNCTION svarta.workdays_sum(worker_id integer, s date, e date, project_id integer) OWNER TO svarta;

--
-- Name: worker_report_data(date, date, integer, date, date, integer); Type: FUNCTION; Schema: svarta; Owner: svarta
--

CREATE FUNCTION svarta.worker_report_data(s date, e date, curator integer, ss date DEFAULT NULL::date, se date DEFAULT NULL::date, sp_id integer DEFAULT NULL::integer) RETURNS TABLE(worker json, workdays json, sum json)
    LANGUAGE plpgsql
    AS $$
begin
  return query with workers as (
    select id, name, function as func
      from svarta.personnel
        where subordinate_to = curator
  ) select json_agg(workers) as worker, svarta.workdays(id, s, e), svarta.workdays_sum(id, ss, se, sp_id)
      from workers
        group by id, name    
          order by name;
end; $$;


ALTER FUNCTION svarta.worker_report_data(s date, e date, curator integer, ss date, se date, sp_id integer) OWNER TO svarta;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: actors; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.actors (
    id uuid NOT NULL,
    full_name character varying,
    role character varying
);


ALTER TABLE svarta.actors OWNER TO svarta;

--
-- Name: jobs; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.jobs (
    id integer NOT NULL,
    name text NOT NULL,
    project integer NOT NULL,
    measure text NOT NULL,
    planned_measure numeric NOT NULL,
    planned_hours numeric NOT NULL
);


ALTER TABLE svarta.jobs OWNER TO svarta;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: svarta; Owner: svarta
--

CREATE SEQUENCE svarta.jobs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE svarta.jobs_id_seq OWNER TO svarta;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: svarta; Owner: svarta
--

ALTER SEQUENCE svarta.jobs_id_seq OWNED BY svarta.jobs.id;


--
-- Name: jobs_report; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.jobs_report (
    id integer NOT NULL,
    date date DEFAULT CURRENT_DATE,
    measure numeric NOT NULL,
    hours numeric NOT NULL
);


ALTER TABLE svarta.jobs_report OWNER TO svarta;

--
-- Name: jobs_report_id_seq; Type: SEQUENCE; Schema: svarta; Owner: svarta
--

CREATE SEQUENCE svarta.jobs_report_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE svarta.jobs_report_id_seq OWNER TO svarta;

--
-- Name: jobs_report_id_seq; Type: SEQUENCE OWNED BY; Schema: svarta; Owner: svarta
--

ALTER SEQUENCE svarta.jobs_report_id_seq OWNED BY svarta.jobs_report.id;


--
-- Name: personnel; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.personnel (
    id integer NOT NULL,
    name text NOT NULL,
    function text NOT NULL,
    subordinate_to integer
);


ALTER TABLE svarta.personnel OWNER TO svarta;

--
-- Name: personnel_id_seq; Type: SEQUENCE; Schema: svarta; Owner: svarta
--

CREATE SEQUENCE svarta.personnel_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE svarta.personnel_id_seq OWNER TO svarta;

--
-- Name: personnel_id_seq; Type: SEQUENCE OWNED BY; Schema: svarta; Owner: svarta
--

ALTER SEQUENCE svarta.personnel_id_seq OWNED BY svarta.personnel.id;


--
-- Name: projects; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.projects (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE svarta.projects OWNER TO svarta;

--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: svarta; Owner: svarta
--

CREATE SEQUENCE svarta.projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE svarta.projects_id_seq OWNER TO svarta;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: svarta; Owner: svarta
--

ALTER SEQUENCE svarta.projects_id_seq OWNED BY svarta.projects.id;


--
-- Name: users; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.users (
    id uuid NOT NULL,
    registration_date date,
    credentials jsonb
);


ALTER TABLE svarta.users OWNER TO svarta;

--
-- Name: worker_report; Type: TABLE; Schema: svarta; Owner: svarta
--

CREATE TABLE svarta.worker_report (
    id integer NOT NULL,
    date date DEFAULT CURRENT_DATE,
    worker integer,
    hours numeric,
    status svarta.worker_status,
    project integer,
    CONSTRAINT worker_report_check CHECK (((hours IS NULL) OR (status IS NULL)))
);


ALTER TABLE svarta.worker_report OWNER TO svarta;

--
-- Name: worker_report_id_seq; Type: SEQUENCE; Schema: svarta; Owner: svarta
--

CREATE SEQUENCE svarta.worker_report_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE svarta.worker_report_id_seq OWNER TO svarta;

--
-- Name: worker_report_id_seq; Type: SEQUENCE OWNED BY; Schema: svarta; Owner: svarta
--

ALTER SEQUENCE svarta.worker_report_id_seq OWNED BY svarta.worker_report.id;


--
-- Name: jobs id; Type: DEFAULT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.jobs ALTER COLUMN id SET DEFAULT nextval('svarta.jobs_id_seq'::regclass);


--
-- Name: jobs_report id; Type: DEFAULT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.jobs_report ALTER COLUMN id SET DEFAULT nextval('svarta.jobs_report_id_seq'::regclass);


--
-- Name: personnel id; Type: DEFAULT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.personnel ALTER COLUMN id SET DEFAULT nextval('svarta.personnel_id_seq'::regclass);


--
-- Name: projects id; Type: DEFAULT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.projects ALTER COLUMN id SET DEFAULT nextval('svarta.projects_id_seq'::regclass);


--
-- Name: worker_report id; Type: DEFAULT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.worker_report ALTER COLUMN id SET DEFAULT nextval('svarta.worker_report_id_seq'::regclass);


--
-- Data for Name: actors; Type: TABLE DATA; Schema: svarta; Owner: svarta
--



--
-- Data for Name: jobs; Type: TABLE DATA; Schema: svarta; Owner: svarta
--



--
-- Data for Name: jobs_report; Type: TABLE DATA; Schema: svarta; Owner: svarta
--



--
-- Data for Name: personnel; Type: TABLE DATA; Schema: svarta; Owner: svarta
--

INSERT INTO svarta.personnel VALUES (1, 'Туточкин У.Ч.', 'КУРАТОР', NULL);
INSERT INTO svarta.personnel VALUES (2, 'Иванов П.Н.', 'МАСТ', 3);
INSERT INTO svarta.personnel VALUES (3, 'Петров А.Я.', 'ИНЖ', 3);
INSERT INTO svarta.personnel VALUES (4, 'Сапог А.М.', 'ИЗОЛ', 5);
INSERT INTO svarta.personnel VALUES (5, 'Дубин Ц.Ф.', 'МАСТ', 5);
INSERT INTO svarta.personnel VALUES (6, 'Выворотень В.О.', 'ИЗОЛ', 5);
INSERT INTO svarta.personnel VALUES (7, 'Колымин Л.А.', 'МАСТ', 5);
INSERT INTO svarta.personnel VALUES (8, 'Тугаров С.У.', 'СВАР', 5);
INSERT INTO svarta.personnel VALUES (9, 'Шебуренов К.С.', 'МОНТ', 5);
INSERT INTO svarta.personnel VALUES (10, 'Щукин Е.Д.', 'МАСТ', 10);
INSERT INTO svarta.personnel VALUES (11, 'Шепелкин T.А.', 'МОНТ', 10);
INSERT INTO svarta.personnel VALUES (12, 'Колымкин М.А.', 'МОНТ', 10);
INSERT INTO svarta.personnel VALUES (13, 'Топоров В.Е.', 'РУК-ПР', 10);
INSERT INTO svarta.personnel VALUES (14, 'Разов С.В.', 'СВАР', 10);
INSERT INTO svarta.personnel VALUES (15, 'Гамерсон А.С.', 'МОНТ', 1);
INSERT INTO svarta.personnel VALUES (16, 'Сигматулин А.С.', 'МОНТ', 1);
INSERT INTO svarta.personnel VALUES (17, 'Семёнов Е.Н.', 'ИЗОЛ', 1);
INSERT INTO svarta.personnel VALUES (18, 'Окуньков М.А.', 'СВАР', 1);
INSERT INTO svarta.personnel VALUES (19, 'Теплов А.К.', 'СВАР', 1);
INSERT INTO svarta.personnel VALUES (20, 'Вахрашин В.Ю.', 'МОНТ', 1);
INSERT INTO svarta.personnel VALUES (21, 'Пушечкин В.В.', 'МАСТ', 1);


--
-- Data for Name: projects; Type: TABLE DATA; Schema: svarta; Owner: svarta
--

INSERT INTO svarta.projects VALUES (1, 'Ку');
INSERT INTO svarta.projects VALUES (2, 'Се');
INSERT INTO svarta.projects VALUES (3, 'Ше');
INSERT INTO svarta.projects VALUES (4, 'Ил');
INSERT INTO svarta.projects VALUES (5, 'Эн');


--
-- Data for Name: users; Type: TABLE DATA; Schema: svarta; Owner: svarta
--



--
-- Data for Name: worker_report; Type: TABLE DATA; Schema: svarta; Owner: svarta
--



--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: svarta; Owner: svarta
--

SELECT pg_catalog.setval('svarta.jobs_id_seq', 1, false);


--
-- Name: jobs_report_id_seq; Type: SEQUENCE SET; Schema: svarta; Owner: svarta
--

SELECT pg_catalog.setval('svarta.jobs_report_id_seq', 1, false);


--
-- Name: personnel_id_seq; Type: SEQUENCE SET; Schema: svarta; Owner: svarta
--

SELECT pg_catalog.setval('svarta.personnel_id_seq', 21, true);


--
-- Name: projects_id_seq; Type: SEQUENCE SET; Schema: svarta; Owner: svarta
--

SELECT pg_catalog.setval('svarta.projects_id_seq', 5, true);


--
-- Name: worker_report_id_seq; Type: SEQUENCE SET; Schema: svarta; Owner: svarta
--

SELECT pg_catalog.setval('svarta.worker_report_id_seq', 1, false);


--
-- Name: actors actors_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.actors
    ADD CONSTRAINT actors_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: jobs_report jobs_report_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.jobs_report
    ADD CONSTRAINT jobs_report_pkey PRIMARY KEY (id);


--
-- Name: personnel personnel_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.personnel
    ADD CONSTRAINT personnel_pkey PRIMARY KEY (id);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: worker_report worker_report_pkey; Type: CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.worker_report
    ADD CONSTRAINT worker_report_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_project_fkey; Type: FK CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.jobs
    ADD CONSTRAINT jobs_project_fkey FOREIGN KEY (project) REFERENCES svarta.projects(id);


--
-- Name: personnel personnel_subordinate_to_fkey; Type: FK CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.personnel
    ADD CONSTRAINT personnel_subordinate_to_fkey FOREIGN KEY (subordinate_to) REFERENCES svarta.personnel(id);


--
-- Name: worker_report worker_report_project_fkey; Type: FK CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.worker_report
    ADD CONSTRAINT worker_report_project_fkey FOREIGN KEY (project) REFERENCES svarta.projects(id);


--
-- Name: worker_report worker_report_worker_fkey; Type: FK CONSTRAINT; Schema: svarta; Owner: svarta
--

ALTER TABLE ONLY svarta.worker_report
    ADD CONSTRAINT worker_report_worker_fkey FOREIGN KEY (worker) REFERENCES svarta.personnel(id);


--
-- PostgreSQL database dump complete
--

