--
-- PostgreSQL database dump
--

-- Dumped from database version 10.23
-- Dumped by pg_dump version 16.3

-- Started on 2025-02-19 17:19:25 -05

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
-- TOC entry 4024 (class 0 OID 1298733)
-- Dependencies: 518
-- Data for Name: meta_rueda_de_la_vida; Type: TABLE DATA; Schema: public; Owner: redil2024_usuario
--

INSERT INTO public.meta_rueda_de_la_vida (id, metas_id, valor, rueda_de_la_vida_id, created_at, updated_at) VALUES (1, 1, 'ORAR MAS', 1, '2025-02-19 17:08:04', '2025-02-19 17:08:04');
INSERT INTO public.meta_rueda_de_la_vida (id, metas_id, valor, rueda_de_la_vida_id, created_at, updated_at) VALUES (2, 2, 'ORAR MAS 2', 1, '2025-02-19 17:08:04', '2025-02-19 17:08:04');
INSERT INTO public.meta_rueda_de_la_vida (id, metas_id, valor, rueda_de_la_vida_id, created_at, updated_at) VALUES (3, 3, 'ORAR MAS 3', 1, '2025-02-19 17:08:04', '2025-02-19 17:08:04');
INSERT INTO public.meta_rueda_de_la_vida (id, metas_id, valor, rueda_de_la_vida_id, created_at, updated_at) VALUES (4, 4, 'ORAR MAS 4', 1, '2025-02-19 17:08:04', '2025-02-19 17:08:04');


--
-- TOC entry 4030 (class 0 OID 0)
-- Dependencies: 517
-- Name: meta_rueda_de_la_vida_id_seq; Type: SEQUENCE SET; Schema: public; Owner: redil2024_usuario
--

SELECT pg_catalog.setval('public.meta_rueda_de_la_vida_id_seq', 4, true);


-- Completed on 2025-02-19 17:19:31 -05

--
-- PostgreSQL database dump complete
--

