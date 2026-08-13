--
-- PostgreSQL database dump
--

\restrict FtjTJkpP3qmua08hLkmLo7Wb6HedX6jWDTzOALk7ehMHhbAqRdXTUyCV5CYMutn

-- Dumped from database version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)

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
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: api_keys; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.api_keys (
    id integer NOT NULL,
    service_name character varying(255) NOT NULL,
    api_key text NOT NULL,
    encrypted boolean DEFAULT false
);


ALTER TABLE public.api_keys OWNER TO postgres;

--
-- Name: api_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.api_keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.api_keys_id_seq OWNER TO postgres;

--
-- Name: api_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.api_keys_id_seq OWNED BY public.api_keys.id;


--
-- Name: app_config; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_config (
    id integer NOT NULL,
    config_json text NOT NULL,
    created_at date NOT NULL,
    updated_at date NOT NULL
);


ALTER TABLE public.app_config OWNER TO postgres;

--
-- Name: app_config_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.app_config_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.app_config_id_seq OWNER TO postgres;

--
-- Name: app_config_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.app_config_id_seq OWNED BY public.app_config.id;


--
-- Name: app_configs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_configs (
    id bigint NOT NULL,
    config_key character varying(200) NOT NULL,
    config_group character varying(50) DEFAULT 'general'::character varying NOT NULL,
    lang character varying(10),
    value text DEFAULT '""'::text NOT NULL,
    label character varying(200),
    description text,
    is_public boolean DEFAULT true NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.app_configs OWNER TO postgres;

--
-- Name: app_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.app_configs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.app_configs_id_seq OWNER TO postgres;

--
-- Name: app_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.app_configs_id_seq OWNED BY public.app_configs.id;


--
-- Name: attributes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attributes (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    slug text NOT NULL,
    type text DEFAULT '""'::text NOT NULL,
    order_by text DEFAULT '""'::text NOT NULL,
    has_archives double precision NOT NULL,
    is_visible double precision NOT NULL,
    _links text NOT NULL,
    updated_at text NOT NULL,
    created_at text NOT NULL
);


ALTER TABLE public.attributes OWNER TO postgres;

--
-- Name: attributes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attributes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attributes_id_seq OWNER TO postgres;

--
-- Name: attributes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attributes_id_seq OWNED BY public.attributes.id;


--
-- Name: blogposts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blogposts (
    id bigint NOT NULL,
    date character varying(255),
    date_gmt character varying(255),
    guid text,
    modified character varying(255),
    modified_gmt character varying(255),
    slug character varying(255),
    status character varying(255),
    type character varying(255),
    link character varying(255),
    title text,
    content text,
    excerpt text,
    author integer,
    featured_media integer,
    comment_status character varying(255),
    ping_status character varying(255),
    sticky boolean,
    template character varying(255),
    format character varying(255),
    meta text,
    categories text,
    tags text,
    class_list text,
    better_featured_image text,
    image_feature character varying(255),
    author_name character varying(255),
    _links text,
    _embedded text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blogposts OWNER TO postgres;

--
-- Name: blogposts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blogposts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blogposts_id_seq OWNER TO postgres;

--
-- Name: blogposts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blogposts_id_seq OWNED BY public.blogposts.id;


--
-- Name: brands; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.brands (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    image character varying(255)
);


ALTER TABLE public.brands OWNER TO postgres;

--
-- Name: brands_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.brands_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.brands_id_seq OWNER TO postgres;

--
-- Name: brands_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.brands_id_seq OWNED BY public.brands.id;


--
-- Name: cart_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cart_items (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    product_id bigint NOT NULL,
    variation_id bigint,
    qty smallint DEFAULT '1'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cart_items OWNER TO postgres;

--
-- Name: cart_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cart_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cart_items_id_seq OWNER TO postgres;

--
-- Name: cart_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cart_items_id_seq OWNED BY public.cart_items.id;


--
-- Name: categories2; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories2 (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255),
    parent integer,
    description character varying(255),
    display character varying(255),
    image text,
    menu_order integer,
    count integer,
    has_children double precision,
    _links text
);


ALTER TABLE public.categories2 OWNER TO postgres;

--
-- Name: categories2_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories2_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories2_id_seq OWNER TO postgres;

--
-- Name: categories2_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories2_id_seq OWNED BY public.categories2.id;


--
-- Name: category_brand_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.category_brand_requests (
    id bigint NOT NULL,
    type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    admin_note text,
    vendor_user_id bigint,
    vendor_name character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    parent_category_id bigint,
    parent_category_name character varying(255),
    CONSTRAINT category_brand_requests_status_check CHECK (((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('approved'::character varying)::text, ('rejected'::character varying)::text]))),
    CONSTRAINT category_brand_requests_type_check CHECK (((type)::text = ANY (ARRAY[('category'::character varying)::text, ('brand'::character varying)::text])))
);


ALTER TABLE public.category_brand_requests OWNER TO postgres;

--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.category_brand_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.category_brand_requests_id_seq OWNER TO postgres;

--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.category_brand_requests_id_seq OWNED BY public.category_brand_requests.id;


--
-- Name: countries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.countries (
    id bigint NOT NULL,
    code character varying(255),
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.countries OWNER TO postgres;

--
-- Name: countries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.countries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.countries_id_seq OWNER TO postgres;

--
-- Name: countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.countries_id_seq OWNED BY public.countries.id;


--
-- Name: coupon_user_limits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.coupon_user_limits (
    id bigint NOT NULL,
    coupon_id bigint NOT NULL,
    user_id bigint NOT NULL,
    use_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.coupon_user_limits OWNER TO postgres;

--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.coupon_user_limits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.coupon_user_limits_id_seq OWNER TO postgres;

--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.coupon_user_limits_id_seq OWNED BY public.coupon_user_limits.id;


--
-- Name: coupons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.coupons (
    id bigint NOT NULL,
    code character varying(50) NOT NULL,
    amount numeric(10,2) NOT NULL,
    status text DEFAULT 'publish'::text NOT NULL,
    discount_type text DEFAULT 'fixed_cart'::text NOT NULL,
    date_created timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    date_created_gmt timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    date_modified timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    date_modified_gmt timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    date_expires timestamp(0) without time zone,
    date_expires_gmt timestamp(0) without time zone,
    usage_count integer DEFAULT 0 NOT NULL,
    individual_use boolean DEFAULT false NOT NULL,
    usage_limit integer,
    usage_limit_per_user integer,
    limit_usage_to_x_items integer,
    product_ids text DEFAULT '[]'::text NOT NULL,
    excluded_product_ids text DEFAULT '[]'::text NOT NULL,
    product_categories text DEFAULT '[]'::text NOT NULL,
    excluded_product_categories text DEFAULT '[]'::text NOT NULL,
    free_shipping boolean DEFAULT false NOT NULL,
    exclude_sale_items boolean DEFAULT false NOT NULL,
    minimum_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    maximum_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    email_restrictions text DEFAULT '[]'::text NOT NULL,
    used_by text DEFAULT '[]'::text NOT NULL,
    description text,
    meta_data text DEFAULT '[]'::text NOT NULL,
    vendor_id bigint
);


ALTER TABLE public.coupons OWNER TO postgres;

--
-- Name: coupons_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.coupons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.coupons_id_seq OWNER TO postgres;

--
-- Name: coupons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.coupons_id_seq OWNED BY public.coupons.id;


--
-- Name: device_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.device_access_tokens (
    id bigint NOT NULL,
    device_id character varying(255) NOT NULL,
    tokenable_id bigint DEFAULT '0'::bigint NOT NULL,
    name character varying(255) DEFAULT ''::character varying NOT NULL,
    token character varying(255) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    key_pass character varying(255) NOT NULL,
    identifier character varying(255) NOT NULL,
    blocked integer DEFAULT 0 NOT NULL,
    about_device text DEFAULT ''::text NOT NULL
);


ALTER TABLE public.device_access_tokens OWNER TO postgres;

--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.device_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.device_access_tokens_id_seq OWNER TO postgres;

--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.device_access_tokens_id_seq OWNED BY public.device_access_tokens.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: getposttest; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.getposttest (
    id integer NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    created_at text NOT NULL,
    updated_at text NOT NULL
);


ALTER TABLE public.getposttest OWNER TO postgres;

--
-- Name: getposttest_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.getposttest_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.getposttest_id_seq OWNER TO postgres;

--
-- Name: getposttest_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.getposttest_id_seq OWNED BY public.getposttest.id;


--
-- Name: idempotency_keys; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.idempotency_keys (
    id bigint NOT NULL,
    key character varying(36) NOT NULL,
    user_id bigint NOT NULL,
    order_id bigint,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.idempotency_keys OWNER TO postgres;

--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.idempotency_keys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.idempotency_keys_id_seq OWNER TO postgres;

--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.idempotency_keys_id_seq OWNED BY public.idempotency_keys.id;


--
-- Name: image_gallery_images; Type: TABLE; Schema: public; Owner: ramo_app
--

CREATE TABLE public.image_gallery_images (
    id bigint NOT NULL,
    path character varying(255) NOT NULL,
    original_name character varying(255) NOT NULL,
    mime_type character varying(100) NOT NULL,
    file_size bigint NOT NULL,
    width integer,
    height integer,
    uploaded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.image_gallery_images OWNER TO ramo_app;

--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE; Schema: public; Owner: ramo_app
--

CREATE SEQUENCE public.image_gallery_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.image_gallery_images_id_seq OWNER TO ramo_app;

--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ramo_app
--

ALTER SEQUENCE public.image_gallery_images_id_seq OWNED BY public.image_gallery_images.id;


--
-- Name: koto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.koto (
    id integer NOT NULL,
    key_in text NOT NULL,
    identfier text NOT NULL
);


ALTER TABLE public.koto OWNER TO postgres;

--
-- Name: koto_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.koto_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.koto_id_seq OWNER TO postgres;

--
-- Name: koto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.koto_id_seq OWNED BY public.koto.id;


--
-- Name: link_access_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.link_access_logs (
    id integer NOT NULL,
    link_name text NOT NULL,
    usage_times integer DEFAULT 0,
    user_call_id text
);


ALTER TABLE public.link_access_logs OWNER TO postgres;

--
-- Name: link_access_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.link_access_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.link_access_logs_id_seq OWNER TO postgres;

--
-- Name: link_access_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.link_access_logs_id_seq OWNED BY public.link_access_logs.id;


--
-- Name: links; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.links (
    id bigint NOT NULL,
    link text NOT NULL,
    data text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    post_data text DEFAULT 'Was_Get_Or_Null'::text NOT NULL
);


ALTER TABLE public.links OWNER TO postgres;

--
-- Name: links_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.links_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.links_id_seq OWNER TO postgres;

--
-- Name: links_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.links_id_seq OWNED BY public.links.id;


--
-- Name: links_json_res; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.links_json_res (
    id bigint NOT NULL,
    link character varying(255) NOT NULL,
    data text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.links_json_res OWNER TO postgres;

--
-- Name: links_json_res_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.links_json_res_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.links_json_res_id_seq OWNER TO postgres;

--
-- Name: links_json_res_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.links_json_res_id_seq OWNED BY public.links_json_res.id;


--
-- Name: links_logs_two; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.links_logs_two (
    id integer NOT NULL,
    link text NOT NULL,
    data text NOT NULL,
    post_data text NOT NULL,
    created_at text NOT NULL,
    updated_at text NOT NULL
);


ALTER TABLE public.links_logs_two OWNER TO postgres;

--
-- Name: links_logs_two_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.links_logs_two_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.links_logs_two_id_seq OWNER TO postgres;

--
-- Name: links_logs_two_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.links_logs_two_id_seq OWNED BY public.links_logs_two.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: order_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.order_messages (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    customer_id bigint NOT NULL,
    vendor_id bigint,
    sender_type character varying(255) NOT NULL,
    message text NOT NULL,
    is_vendor_response boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sub_order_id bigint,
    CONSTRAINT order_messages_sender_type_check CHECK (((sender_type)::text = ANY (ARRAY[('customer'::character varying)::text, ('vendor'::character varying)::text])))
);


ALTER TABLE public.order_messages OWNER TO postgres;

--
-- Name: order_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.order_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_messages_id_seq OWNER TO postgres;

--
-- Name: order_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.order_messages_id_seq OWNED BY public.order_messages.id;


--
-- Name: order_sub_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.order_sub_orders (
    id bigint NOT NULL,
    parent_order_id bigint NOT NULL,
    vendor_id bigint,
    customer_id bigint,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    line_items text,
    subtotal numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    discount_total numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    tracking_number character varying(255),
    tracking_carrier character varying(255),
    timeline text,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    vendor_status character varying(40) DEFAULT 'pending'::character varying NOT NULL
);


ALTER TABLE public.order_sub_orders OWNER TO postgres;

--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.order_sub_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_sub_orders_id_seq OWNER TO postgres;

--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.order_sub_orders_id_seq OWNED BY public.order_sub_orders.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.orders (
    id integer NOT NULL,
    parent_id integer DEFAULT 0,
    parent_vendors_ids text,
    parent_vendors_data text,
    status character varying(200) DEFAULT 'pending'::character varying,
    currency character varying(10) DEFAULT 'USD'::character varying,
    version character varying(10),
    prices_include_tax boolean DEFAULT false,
    date_created timestamp(0) without time zone,
    date_modified timestamp(0) without time zone,
    discount_total numeric(10,2) DEFAULT '0'::numeric,
    discount_tax numeric(10,2) DEFAULT '0'::numeric,
    shipping_total numeric(10,2) DEFAULT '0'::numeric,
    shipping_tax numeric(10,2) DEFAULT '0'::numeric,
    cart_tax numeric(10,2) DEFAULT '0'::numeric,
    coupon_code character varying(50),
    final_total numeric(10,2),
    original_total integer DEFAULT 0 NOT NULL,
    coupon_applied integer DEFAULT 0 NOT NULL,
    total_tax numeric(10,2) DEFAULT '0'::numeric,
    customer_id integer,
    order_key character varying(50),
    billing text,
    shipping text,
    payment_method character varying(50),
    payment_method_title character varying(100),
    transaction_id character varying(100),
    customer_ip_address character varying(45),
    customer_user_agent character varying(255),
    created_via character varying(50),
    customer_note text,
    date_completed timestamp(0) without time zone,
    date_paid timestamp(0) without time zone,
    cart_hash character varying(100),
    meta_data text,
    line_items text,
    tax_lines text,
    shipping_lines text,
    fee_lines text,
    coupon_lines text,
    refunds text,
    payment_url character varying(255) DEFAULT ''::character varying NOT NULL,
    is_editable boolean DEFAULT true NOT NULL,
    needs_payment boolean DEFAULT false NOT NULL,
    needs_processing boolean DEFAULT true NOT NULL,
    bacs_info text,
    currency_symbol character varying(10) DEFAULT 'ج.م'::character varying NOT NULL,
    _links text,
    date_created_gmt text DEFAULT ''::text NOT NULL,
    date_modified_gmt text DEFAULT ''::text NOT NULL,
    date_completed_gmt text DEFAULT ''::text NOT NULL,
    date_paid_gmt text DEFAULT ''::text NOT NULL,
    set_paid boolean DEFAULT false NOT NULL,
    number integer DEFAULT 0 NOT NULL,
    timeline text DEFAULT '[]'::text NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    payment_status character varying(40) DEFAULT 'confirmed'::character varying NOT NULL,
    payment_receipt_path character varying(255),
    payment_receipt_name character varying(255),
    payment_receipt_uploaded_at timestamp(0) without time zone,
    payment_reviewed_at timestamp(0) without time zone,
    payment_reviewed_by bigint,
    payment_rejection_reason text,
    general_order_status character varying(40) DEFAULT 'pending'::character varying NOT NULL,
    general_order_status_override character varying(40),
    general_order_status_override_reason text,
    general_order_status_override_by bigint,
    general_order_status_override_at timestamp(0) without time zone
);


ALTER TABLE public.orders OWNER TO postgres;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.orders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.orders_id_seq OWNER TO postgres;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.otp_verifications (
    id bigint NOT NULL,
    phone character varying(30) NOT NULL,
    otp_code character varying(10) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    attempts smallint DEFAULT '0'::smallint NOT NULL,
    resend_count smallint DEFAULT '0'::smallint NOT NULL,
    resend_window_start timestamp(0) without time zone,
    verified boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.otp_verifications OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.otp_verifications_id_seq OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: payment_receipts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payment_receipts (
    id bigint NOT NULL,
    order_id integer NOT NULL,
    payment_method character varying(50) NOT NULL,
    file_path character varying(255) NOT NULL,
    original_name character varying(255),
    status character varying(30) DEFAULT 'pending'::character varying NOT NULL,
    rejection_reason text,
    uploaded_by bigint,
    reviewed_by bigint,
    uploaded_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    reviewed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payment_receipts OWNER TO postgres;

--
-- Name: payment_receipts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payment_receipts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payment_receipts_id_seq OWNER TO postgres;

--
-- Name: payment_receipts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payment_receipts_id_seq OWNED BY public.payment_receipts.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: product_category; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_category (
    product_id bigint NOT NULL,
    category_id bigint NOT NULL
);


ALTER TABLE public.product_category OWNER TO postgres;

--
-- Name: product_reviews; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_reviews (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    user_id bigint NOT NULL,
    rating smallint NOT NULL,
    title character varying(150),
    body text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    approved boolean DEFAULT true NOT NULL,
    is_verified_purchase boolean DEFAULT false NOT NULL,
    helpful_count integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.product_reviews OWNER TO postgres;

--
-- Name: product_reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.product_reviews_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.product_reviews_id_seq OWNER TO postgres;

--
-- Name: product_reviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.product_reviews_id_seq OWNED BY public.product_reviews.id;


--
-- Name: product_variations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_variations (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    main_variation boolean DEFAULT false NOT NULL,
    attributes text NOT NULL,
    price numeric(10,2) NOT NULL,
    regular_price numeric(10,2) NOT NULL,
    sale_price numeric(10,2),
    stock_quantity integer DEFAULT 0 NOT NULL,
    images text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    stock_status character varying(255) DEFAULT 'instock'::character varying NOT NULL,
    status character varying(255) DEFAULT 'publish'::character varying NOT NULL
);


ALTER TABLE public.product_variations OWNER TO postgres;

--
-- Name: product_variations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.product_variations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.product_variations_id_seq OWNER TO postgres;

--
-- Name: product_variations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.product_variations_id_seq OWNED BY public.product_variations.id;


--
-- Name: products_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products_data (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    search_text text DEFAULT ''::text NOT NULL,
    permalink character varying(255) DEFAULT ''::character varying,
    date_created character varying(255) DEFAULT ''::character varying,
    date_created_gmt character varying(255) DEFAULT ''::character varying,
    date_modified character varying(255) DEFAULT ''::character varying,
    date_modified_gmt character varying(255) DEFAULT ''::character varying,
    type character varying(255) DEFAULT ''::character varying,
    status character varying(255) DEFAULT ''::character varying,
    featured boolean DEFAULT false,
    catalog_visibility character varying(255) DEFAULT ''::character varying,
    description text,
    discount_percentage text DEFAULT ''::text NOT NULL,
    short_description text,
    sku text,
    date_on_sale_from timestamp(0) without time zone,
    date_on_sale_from_gmt timestamp(0) without time zone,
    date_on_sale_to timestamp(0) without time zone,
    date_on_sale_to_gmt timestamp(0) without time zone,
    on_sale boolean DEFAULT false,
    purchasable boolean DEFAULT false,
    total_sales integer DEFAULT 0,
    virtual boolean DEFAULT false,
    downloadable boolean DEFAULT false,
    downloads text DEFAULT '[]'::text,
    download_limit integer DEFAULT 0,
    download_expiry integer DEFAULT 0,
    external_url text,
    button_text character varying(255) DEFAULT ''::character varying,
    manage_stock boolean DEFAULT false,
    stock_quantity integer DEFAULT 0,
    backorders character varying(255) DEFAULT ''::character varying,
    backorders_allowed boolean DEFAULT false,
    backordered boolean DEFAULT false,
    low_stock_amount integer DEFAULT 0,
    sold_individually boolean DEFAULT false,
    dimensions text DEFAULT '[]'::text,
    shipping_required boolean DEFAULT false,
    shipping_taxable boolean DEFAULT false,
    shipping_class character varying(255) DEFAULT ''::character varying,
    shipping_class_id integer DEFAULT 0,
    reviews_allowed boolean DEFAULT false,
    average_rating character varying(255) DEFAULT ''::character varying,
    rating_count integer DEFAULT 0,
    upsell_ids text DEFAULT '[]'::text,
    cross_sell_ids text DEFAULT '[]'::text,
    parent_id integer DEFAULT 0,
    purchase_note character varying(255) DEFAULT ''::character varying,
    categories text DEFAULT '[]'::text,
    tags text DEFAULT '[]'::text,
    images text DEFAULT '[]'::text,
    attributes text DEFAULT '[]'::text,
    default_attributes text DEFAULT '[]'::text,
    variations text DEFAULT '[]'::text,
    grouped_products text DEFAULT '[]'::text,
    menu_order integer DEFAULT 0,
    related_ids text DEFAULT '[]'::text,
    meta_data text DEFAULT '[]'::text,
    stock_status character varying(255) DEFAULT ''::character varying,
    has_options boolean DEFAULT false,
    has_variations boolean DEFAULT false NOT NULL,
    global_unique_id character varying(255) DEFAULT ''::character varying,
    better_featured_image text,
    is_purchased boolean DEFAULT false,
    "attributesData" text DEFAULT '[]'::text,
    is_wallet_product boolean DEFAULT false,
    _links text DEFAULT '[]'::text,
    lang text DEFAULT ''::text NOT NULL,
    min_price character varying(255) DEFAULT '0'::character varying,
    brand_id character varying(255) DEFAULT ''::character varying NOT NULL,
    max_price character varying(255) DEFAULT '0'::character varying,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    minimum_order_qty integer DEFAULT 0 NOT NULL,
    max_orders_per_person integer DEFAULT 0 NOT NULL,
    product_type text DEFAULT 'physical'::text,
    vendor_id bigint,
    translations text DEFAULT ''::text NOT NULL,
    acceptance_status text DEFAULT 'pending'::text NOT NULL,
    unit text DEFAULT ''::text NOT NULL,
    whatsapp text DEFAULT ''::text NOT NULL,
    button_mode character varying(255) DEFAULT 'both'::character varying
);


ALTER TABLE public.products_data OWNER TO postgres;

--
-- Name: COLUMN products_data.button_mode; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.products_data.button_mode IS 'Controls which action buttons show on the product card: both, cart_only, details_only';


--
-- Name: products_data_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_data_id_seq OWNER TO postgres;

--
-- Name: products_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_data_id_seq OWNED BY public.products_data.id;


--
-- Name: products_data_main; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products_data_main (
    id bigint NOT NULL,
    name text NOT NULL,
    slug character varying(255) NOT NULL,
    permalink character varying(255) DEFAULT ''::character varying,
    date_created character varying(255) DEFAULT ''::character varying,
    date_created_gmt character varying(255) DEFAULT ''::character varying,
    date_modified character varying(255) DEFAULT ''::character varying,
    date_modified_gmt character varying(255) DEFAULT ''::character varying,
    type character varying(255) DEFAULT ''::character varying,
    status character varying(255) DEFAULT ''::character varying,
    featured boolean DEFAULT false,
    catalog_visibility character varying(255) DEFAULT ''::character varying,
    description text DEFAULT ''::text,
    discount text DEFAULT ''::text NOT NULL,
    short_description text DEFAULT ''::text,
    sku text DEFAULT ''::text,
    price integer DEFAULT 0,
    regular_price integer DEFAULT 0,
    sale_price integer DEFAULT 0,
    date_on_sale_from timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    date_on_sale_from_gmt timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    date_on_sale_to timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    date_on_sale_to_gmt timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    on_sale boolean DEFAULT false,
    purchasable boolean DEFAULT false,
    total_sales integer DEFAULT 0,
    virtual boolean DEFAULT false,
    downloadable boolean DEFAULT false,
    downloads text DEFAULT '{}'::text,
    download_limit integer DEFAULT 0,
    download_expiry integer DEFAULT 0,
    external_url text DEFAULT ''::text,
    button_text character varying(255) DEFAULT ''::character varying,
    manage_stock boolean DEFAULT false,
    stock_quantity integer DEFAULT 0,
    backorders character varying(255) DEFAULT ''::character varying,
    backorders_allowed boolean DEFAULT false,
    backordered boolean DEFAULT false,
    low_stock_amount integer DEFAULT 0,
    sold_individually boolean DEFAULT false,
    dimensions text DEFAULT '{}'::text,
    shipping_required boolean DEFAULT false,
    shipping_taxable boolean DEFAULT false,
    shipping_class character varying(255) DEFAULT ''::character varying,
    shipping_class_id integer DEFAULT 0,
    reviews_allowed boolean DEFAULT false,
    average_rating character varying(255) DEFAULT ''::character varying,
    rating_count integer DEFAULT 0,
    upsell_ids text DEFAULT '{}'::text,
    cross_sell_ids text DEFAULT '{}'::text,
    parent_id integer DEFAULT 0,
    purchase_note character varying(255) DEFAULT ''::character varying,
    categories text DEFAULT '{}'::text,
    tags text DEFAULT '{}'::text,
    images text DEFAULT '{}'::text,
    attributes text DEFAULT '{}'::text,
    default_attributes text DEFAULT '{}'::text,
    variations text DEFAULT '{}'::text,
    grouped_products text DEFAULT '{}'::text,
    menu_order integer DEFAULT 0,
    price_html text DEFAULT ''::text,
    related_ids text DEFAULT '{}'::text,
    meta_data text DEFAULT '{}'::text,
    stock_status character varying(255) DEFAULT ''::character varying,
    has_options boolean DEFAULT false,
    post_password character varying(255) DEFAULT ''::character varying,
    global_unique_id character varying(255) DEFAULT ''::character varying,
    better_featured_image text DEFAULT ''::text,
    is_purchased boolean DEFAULT false,
    "attributesData" text DEFAULT '{}'::text,
    is_wallet_product boolean DEFAULT false,
    _links text DEFAULT '{}'::text,
    lang text DEFAULT ''::text NOT NULL,
    min_price character varying(255) DEFAULT '0'::character varying,
    brand_id character varying(255) DEFAULT ''::character varying NOT NULL,
    max_price character varying(255) DEFAULT '0'::character varying,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    minimum_order_qty integer,
    max_orders_per_person integer,
    product_type text DEFAULT 'physical'::text,
    vendor_id bigint,
    translations text DEFAULT ''::text NOT NULL,
    acceptance_status text DEFAULT 'pending'::text NOT NULL,
    unit text DEFAULT ''::text NOT NULL,
    button_mode character varying(255) DEFAULT 'both'::character varying
);


ALTER TABLE public.products_data_main OWNER TO postgres;

--
-- Name: COLUMN products_data_main.button_mode; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.products_data_main.button_mode IS 'Controls which action buttons show on the product card: both, cart_only, details_only';


--
-- Name: products_data_main_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_data_main_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_data_main_id_seq OWNER TO postgres;

--
-- Name: products_data_main_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_data_main_id_seq OWNED BY public.products_data_main.id;


--
-- Name: rate_limits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rate_limits (
    consumer_key character varying(700) NOT NULL,
    request_count integer DEFAULT 0,
    last_request_time integer NOT NULL
);


ALTER TABLE public.rate_limits OWNER TO postgres;

--
-- Name: refund_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.refund_requests (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    customer_id bigint NOT NULL,
    vendor_id bigint,
    type character varying(255) DEFAULT 'refund'::character varying NOT NULL,
    reason character varying(255) NOT NULL,
    description text,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    admin_note text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.refund_requests OWNER TO postgres;

--
-- Name: refund_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.refund_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.refund_requests_id_seq OWNER TO postgres;

--
-- Name: refund_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.refund_requests_id_seq OWNED BY public.refund_requests.id;


--
-- Name: shops; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.shops (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    shop_name character varying(255) NOT NULL,
    shop_address character varying(255) NOT NULL,
    shop_logo character varying(255),
    shop_banner character varying(255),
    secondary_banner character varying(255),
    status text DEFAULT 'pending'::text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    remember_token character varying(100)
);


ALTER TABLE public.shops OWNER TO postgres;

--
-- Name: shops_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.shops_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.shops_id_seq OWNER TO postgres;

--
-- Name: shops_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.shops_id_seq OWNED BY public.shops.id;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tags (
    id bigint NOT NULL,
    name character varying(255),
    slug character varying(255),
    description character varying(255),
    count integer,
    is_visible boolean,
    _links text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tags OWNER TO postgres;

--
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tags_id_seq OWNER TO postgres;

--
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- Name: time_line_configs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.time_line_configs (
    id integer NOT NULL,
    lang_code character varying(5) NOT NULL,
    config_json text NOT NULL
);


ALTER TABLE public.time_line_configs OWNER TO postgres;

--
-- Name: time_line_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.time_line_configs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.time_line_configs_id_seq OWNER TO postgres;

--
-- Name: time_line_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.time_line_configs_id_seq OWNED BY public.time_line_configs.id;


--
-- Name: user_notes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_notes (
    id bigint NOT NULL,
    user_id bigint,
    date_created timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    note character varying(255) NOT NULL,
    customer_note boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    date_created_gmt timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    order_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.user_notes OWNER TO postgres;

--
-- Name: user_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_notes_id_seq OWNER TO postgres;

--
-- Name: user_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_notes_id_seq OWNED BY public.user_notes.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_login character varying(255),
    username character varying(255),
    user_nicename character varying(255),
    display_name character varying(255),
    first_name character varying(255),
    last_name character varying(255),
    url text,
    avatar text,
    phone text DEFAULT ''::text NOT NULL,
    role character varying(255) DEFAULT 'normal_user'::character varying NOT NULL,
    nicename text DEFAULT ''::text NOT NULL,
    registered text DEFAULT ''::text NOT NULL,
    firstname text DEFAULT ''::text NOT NULL,
    lastname text DEFAULT ''::text NOT NULL,
    description text DEFAULT ''::text NOT NULL,
    capabilities text DEFAULT ''::text NOT NULL,
    shipping text DEFAULT ''::text NOT NULL,
    registration_method character varying(255),
    is_phone_verified boolean DEFAULT false NOT NULL,
    is_blocked boolean DEFAULT false NOT NULL,
    provider character varying(30),
    provider_id character varying(255)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vendor_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vendor_users (
    id bigint NOT NULL,
    profile_image character varying(255),
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    phone character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    shop_name character varying(255) NOT NULL,
    shop_address character varying(255) NOT NULL,
    shop_logo character varying(255),
    shop_banner character varying(255),
    secondary_banner character varying(255),
    bottom_banner text DEFAULT ''::text NOT NULL,
    status text DEFAULT 'pending'::text,
    rating character varying(50) DEFAULT '0'::character varying NOT NULL,
    rating_count integer DEFAULT 0 NOT NULL,
    temporary_close smallint DEFAULT '0'::smallint NOT NULL,
    vacation_end_date character varying(255) DEFAULT 'empty'::character varying NOT NULL,
    vacation_start_date character varying(255) DEFAULT 'empty'::character varying NOT NULL,
    vacation_status smallint DEFAULT '0'::smallint NOT NULL,
    offer_banner text DEFAULT 'empty'::text NOT NULL,
    product_count integer,
    orders_count integer,
    minimum_order_amount integer,
    free_delivery_over_amount integer,
    free_delivery_status integer,
    sales_commission_percentage double precision,
    auth_token character varying(255) NOT NULL,
    holder_name character varying(255) NOT NULL,
    account_no integer,
    bank_name character varying(255) NOT NULL,
    branch character varying(255) NOT NULL,
    free_delivery_features_status smallint,
    free_delivery_responsibility smallint,
    minimum_order_amount_by_seller smallint
);


ALTER TABLE public.vendor_users OWNER TO postgres;

--
-- Name: vendor_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vendor_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vendor_users_id_seq OWNER TO postgres;

--
-- Name: vendor_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vendor_users_id_seq OWNED BY public.vendor_users.id;


--
-- Name: version_config; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.version_config (
    id integer NOT NULL,
    supported_ver_from text DEFAULT '1.0.0'::text NOT NULL,
    supported_ver_to text DEFAULT '4.0.0'::text NOT NULL
);


ALTER TABLE public.version_config OWNER TO postgres;

--
-- Name: version_config_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.version_config_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.version_config_id_seq OWNER TO postgres;

--
-- Name: version_config_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.version_config_id_seq OWNED BY public.version_config.id;


--
-- Name: wishlists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wishlists (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    product_id bigint NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.wishlists OWNER TO postgres;

--
-- Name: wishlists_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.wishlists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wishlists_id_seq OWNER TO postgres;

--
-- Name: wishlists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.wishlists_id_seq OWNED BY public.wishlists.id;


--
-- Name: api_keys id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_keys ALTER COLUMN id SET DEFAULT nextval('public.api_keys_id_seq'::regclass);


--
-- Name: app_config id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_config ALTER COLUMN id SET DEFAULT nextval('public.app_config_id_seq'::regclass);


--
-- Name: app_configs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_configs ALTER COLUMN id SET DEFAULT nextval('public.app_configs_id_seq'::regclass);


--
-- Name: attributes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes ALTER COLUMN id SET DEFAULT nextval('public.attributes_id_seq'::regclass);


--
-- Name: blogposts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blogposts ALTER COLUMN id SET DEFAULT nextval('public.blogposts_id_seq'::regclass);


--
-- Name: brands id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.brands ALTER COLUMN id SET DEFAULT nextval('public.brands_id_seq'::regclass);


--
-- Name: cart_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cart_items ALTER COLUMN id SET DEFAULT nextval('public.cart_items_id_seq'::regclass);


--
-- Name: categories2 id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories2 ALTER COLUMN id SET DEFAULT nextval('public.categories2_id_seq'::regclass);


--
-- Name: category_brand_requests id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_brand_requests ALTER COLUMN id SET DEFAULT nextval('public.category_brand_requests_id_seq'::regclass);


--
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.countries_id_seq'::regclass);


--
-- Name: coupon_user_limits id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.coupon_user_limits ALTER COLUMN id SET DEFAULT nextval('public.coupon_user_limits_id_seq'::regclass);


--
-- Name: coupons id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.coupons ALTER COLUMN id SET DEFAULT nextval('public.coupons_id_seq'::regclass);


--
-- Name: device_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.device_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.device_access_tokens_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: getposttest id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.getposttest ALTER COLUMN id SET DEFAULT nextval('public.getposttest_id_seq'::regclass);


--
-- Name: idempotency_keys id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idempotency_keys ALTER COLUMN id SET DEFAULT nextval('public.idempotency_keys_id_seq'::regclass);


--
-- Name: image_gallery_images id; Type: DEFAULT; Schema: public; Owner: ramo_app
--

ALTER TABLE ONLY public.image_gallery_images ALTER COLUMN id SET DEFAULT nextval('public.image_gallery_images_id_seq'::regclass);


--
-- Name: koto id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.koto ALTER COLUMN id SET DEFAULT nextval('public.koto_id_seq'::regclass);


--
-- Name: link_access_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.link_access_logs ALTER COLUMN id SET DEFAULT nextval('public.link_access_logs_id_seq'::regclass);


--
-- Name: links id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links ALTER COLUMN id SET DEFAULT nextval('public.links_id_seq'::regclass);


--
-- Name: links_json_res id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links_json_res ALTER COLUMN id SET DEFAULT nextval('public.links_json_res_id_seq'::regclass);


--
-- Name: links_logs_two id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links_logs_two ALTER COLUMN id SET DEFAULT nextval('public.links_logs_two_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_messages ALTER COLUMN id SET DEFAULT nextval('public.order_messages_id_seq'::regclass);


--
-- Name: order_sub_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_sub_orders ALTER COLUMN id SET DEFAULT nextval('public.order_sub_orders_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- Name: payment_receipts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_receipts ALTER COLUMN id SET DEFAULT nextval('public.payment_receipts_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: product_reviews id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_reviews ALTER COLUMN id SET DEFAULT nextval('public.product_reviews_id_seq'::regclass);


--
-- Name: product_variations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variations ALTER COLUMN id SET DEFAULT nextval('public.product_variations_id_seq'::regclass);


--
-- Name: products_data id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products_data ALTER COLUMN id SET DEFAULT nextval('public.products_data_id_seq'::regclass);


--
-- Name: products_data_main id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products_data_main ALTER COLUMN id SET DEFAULT nextval('public.products_data_main_id_seq'::regclass);


--
-- Name: refund_requests id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.refund_requests ALTER COLUMN id SET DEFAULT nextval('public.refund_requests_id_seq'::regclass);


--
-- Name: shops id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.shops ALTER COLUMN id SET DEFAULT nextval('public.shops_id_seq'::regclass);


--
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- Name: time_line_configs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.time_line_configs ALTER COLUMN id SET DEFAULT nextval('public.time_line_configs_id_seq'::regclass);


--
-- Name: user_notes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notes ALTER COLUMN id SET DEFAULT nextval('public.user_notes_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vendor_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vendor_users ALTER COLUMN id SET DEFAULT nextval('public.vendor_users_id_seq'::regclass);


--
-- Name: version_config id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.version_config ALTER COLUMN id SET DEFAULT nextval('public.version_config_id_seq'::regclass);


--
-- Name: wishlists id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists ALTER COLUMN id SET DEFAULT nextval('public.wishlists_id_seq'::regclass);


--
-- Data for Name: api_keys; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.api_keys (id, service_name, api_key, encrypted) FROM stdin;
\.


--
-- Data for Name: app_config; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.app_config (id, config_json, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: app_configs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.app_configs (id, config_key, config_group, lang, value, label, description, is_public, sort_order, updated_at) FROM stdin;
2	horizon_layout	layout	ar	[{"layout":"logo","showMenu":true,"showSearch":true,"showLogo":true,"showliked":true},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"هواتف","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/phones_image.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":23,"label":"حقائب","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":25,"label":"بليزرات","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/women_blazers.webp","colors":["#53A2CC","#53A2CC"]},{"category":28,"label":"أحذية","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/sheos.jpg","colors":["#53688A","#53688A"]},{"category":29,"label":"جينز","image":"https:\\/\\/us.dockers.com\\/cdn\\/shop\\/files\\/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]}]},{"layout":"bannerImage","isSlider":true,"autoPlay":true,"design":"default","radius":2,"items":[{"category":29,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/HP-Banner.webp","padding":7},{"category":28,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/Campaign-LP-07.webp","padding":7}]},{"layout":"saleImages","category":23,"headerText":"تسوق بالمظهر","maxItemsToShow":8,"productWidth":130,"productConfig":{"imageRatio":1.4,"borderRadius":10}},{"name":"مجموعات الرجال","layout":"twoColumn","headerText":"تخفيضات اليوم ⚡️","productWidth":200,"maxItemsToShow":7,"category":23,"productConfig":{"borderRadius":12.5,"showHeart":true,"imageRatio":1.5,"layout":"grid"}},{"layout":"category","name":"Men's Collection","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"Men","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/men_cat.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":19,"label":"Shirts","image":"","colors":["#53A2CC","#53A2CC"]},{"category":21,"label":"T-Shirts","image":"","colors":["#3CC2BF","#3CC2BF"]},{"category":30,"label":"Jeans Man","image":"https:\\/\\/us.dockers.com\\/cdn\\/shop\\/files\\/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]},{"category":28,"label":"Jackets","image":"","colors":["#53688A","#53688A"]}]},{"layout":"category","name":"Women's Collection","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":22,"label":"Women","image":"","colors":["#EC4899","#EC4899"]},{"category":25,"label":"Blazers","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/women_blazers.webp","colors":["#8B5CF6","#8B5CF6"]},{"category":26,"label":"Dresses","image":"","colors":["#F59E0B","#F59E0B"]},{"category":29,"label":"Jeans","image":"https:\\/\\/us.dockers.com\\/cdn\\/shop\\/files\\/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]},{"category":23,"label":"Bags","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]}]},{"layout":"category","name":"All Categories","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":208,"label":"Clothing","image":"","colors":["#E85D26","#E85D26"]},{"category":18,"label":"Men","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/men_cat.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":22,"label":"Women","image":"","colors":["#EC4899","#EC4899"]},{"category":23,"label":"Bags","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/bag_image_.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":311,"label":"Phones","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/phones_image.jpg","colors":["#22C55E","#22C55E"]}]}]	Homepage Layout (AR)	\N	t	0	2026-05-06 21:26:09
5	shipping_settings	shipping	\N	{"free_shipping_enabled":false,"free_shipping_threshold":1000,"standard_shipping_fee":100}	Shipping Settings	Free shipping threshold and standard shipping fee	f	0	2026-08-09 14:53:16
3	manual_payment_methods	payment	\N	{"cod_enabled":false,"cod_data":"Pay when your order arrives","vodafone_cash_enabled":false,"vodafone_cash_data":"Send to 01xxxxxxxxx","bank_transfer_enabled":false,"bank_transfer_data":"Transfer to our bank account","fawry_enabled":false,"fawry_data":"Pay at any Fawry outlet","credit_card_enabled":false,"credit_card_data":"Visa \\/ Mastercard","wallet_enabled":true,"wallet_number":"010065464565","instapay_enabled":true,"instapay_number":"010065464565","instapay_link":"https:\\/\\/ipn.eg\\/S\\/ramezasaad500\\/instapay\\/7lyGc3"}	Manual Payment Methods	Wallet and InstaPay transfer instructions for website orders	f	0	2026-08-09 14:55:55
4	auth_settings	auth	\N	{"otp_length": 6, "email_login": false, "google_login": false, "guest_checkout": false, "phone_otp_login": true, "max_otp_attempts": 3, "auto_register_otp": true, "max_login_attempts": 5, "otp_expiry_minutes": 5, "auto_register_google": true, "max_resends_per_hour": 3, "session_expiry_hours": 24, "resend_cooldown_seconds": 60, "lockout_duration_minutes": 15, "require_name_on_register": true, "require_email_on_register": false, "require_email_verification": false}	Auth Settings	Login methods and security configuration	f	0	2026-08-12 21:44:18
6	shop_mobile_product_layout	layout	\N	"grid"	Shop mobile product layout	Choose two products per row or a horizontal product row on narrow phones.	f	100	2026-08-12 11:11:34
1	horizon_layout	layout	en	[{"hidden":true,"layout":"logo","showLogo":true,"showMenu":true,"showliked":true,"showSearch":true},{"size":1,"type":"icon","wrap":false,"items":[{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg","label":"Phones","colors":["#3CC2BF","#3CC2BF"],"category":18},{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg","label":"Bag","colors":["#3E6AB5","#3E6AB5"],"category":23},{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp","label":"Blazers","colors":["#53A2CC","#53A2CC"],"category":25},{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg","label":"Shoes","colors":["#53688A","#53688A"],"category":28},{"image":"https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","label":"Jeans","colors":["#43506A","#43506A"],"category":29},{"image":"https://images.squarespace-cdn.com/content/v1/58add8dd6a49639a87822092/1654105465923-95DJO7H19YLTGOSB4CLO/how-to-style-mens-jeans.jpg?format=750w","label":"Jeans Man","colors":["#12B58C","#12B58C"],"category":30}],"hidden":false,"layout":"category","radius":50},{"gap":12,"items":[{"alt":"","link":"","image":"https://5000-iyqms9tcbk0ie59dd61nq-a577ee3b.sg1.manus.computer/storage/image-gallery/Oz5lvZeeXPHipbtH3n4tCJ20Lv5GxrFUBFn0lNst.png","width":"half"},{"alt":"","link":"","image":"https://5000-iyqms9tcbk0ie59dd61nq-a577ee3b.sg1.manus.computer/storage/image-gallery/JRsRpX1ME2SEp6UNZXwFb5JJeapo51tOT1QLncH3.jpg","width":"half"},{"alt":"","link":"","image":"https://5000-iyqms9tcbk0ie59dd61nq-a577ee3b.sg1.manus.computer/storage/image-gallery/wplKQURnGABGUo1uRrqykvc48AM5IQ7fO7fozO5y.png","width":"quarter"}],"layout":"flexBannerGrid","radius":14,"headerText":"Galary","mobileColumns":1},{"hidden":false,"layout":"saleImages","nameGap":15,"category":null,"buttonGap":15,"cardHeight":216,"headerText":"Shop by Look","imageWidth":0,"optionsGap":15,"responsive":{"mobile":{"imageHeight":140,"productWidth":150,"elementSpacing":7,"cardHeight":250},"desktop":{"nameGap":15,"buttonGap":15,"elementSpacing":6,"imageWidth":0,"imageHeight":267,"productWidth":230,"cardHeight":300,"paddingBottom":40},"breakpoint":600},"imageHeight":240,"productWidth":200,"productConfig":{"imageRatio":1.4,"borderRadius":10},"elementSpacing":0,"maxItemsToShow":8,"showDetails":false},{"layout":"brands"},{"items":[{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp","padding":7,"category":29},{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-04.webp","padding":7,"product":30,"category":18},{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp","padding":7,"category":28}],"design":"default","layout":"bannerImage","radius":10,"autoPlay":true,"isSlider":true,"showNumber":false,"bannerHeight":260,"showBackGround":true},{"name":"Man Collections","layout":"twoColumn","category":23,"headerText":"On Sale Today ⚡️","responsive":{"mobile":{"imageHeight":100,"productWidth":130}},"productWidth":200,"productConfig":{"layout":"grid","hMargin":10,"vMargin":6,"showHeart":true,"imageRatio":1.5,"borderRadius":12.5},"maxItemsToShow":7,"addToCartButtonStyle":{"style":"iconed","textColor":"#3D3D3D","backgroundColor":"#E0E0E0"}},{"fit":"fitWidth","items":[{"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/kobunatkhasm.png","padding":7,"product":30}],"design":"static","height":0.15,"layout":"bannerImage","radius":7,"marginTop":20,"marginLeft":0,"marginRight":0,"bannerHeight":280,"marginBottom":0},{"name":"SuperMarket Stars","layout":"seupermarketstars","category":18},{"name":"Brands","layout":"brands","category":21},{"layout":"topVendors","sortBy":"products","headerText":"Top Sellers","maxItemsToShow":6},{"name":"Featured","layout":"seupermarketstars","category":26},{"layout":"coupons","sortBy":"amount","subLabel":"Use code at checkout","headerText":"This Week's Deals","hideWhenEmpty":true,"maxItemsToShow":6,"showExpiredFallback":true},{"layout":"twoColumn","category":"","headerText":"New Section","maxItemsToShow":8},{"layout":"categoryCards","columns":3,"showCount":true,"cardHeight":220,"headerText":"Shop by Category","parentOnly":true,"maxItemsToShow":12,"cardBorderRadius":14}]	Homepage Layout (EN)	\N	t	0	2026-08-12 18:17:58
\.


--
-- Data for Name: attributes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attributes (id, name, slug, type, order_by, has_archives, is_visible, _links, updated_at, created_at) FROM stdin;
\.


--
-- Data for Name: blogposts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blogposts (id, date, date_gmt, guid, modified, modified_gmt, slug, status, type, link, title, content, excerpt, author, featured_media, comment_status, ping_status, sticky, template, format, meta, categories, tags, class_list, better_featured_image, image_feature, author_name, _links, _embedded, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: brands; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.brands (id, name, image) FROM stdin;
1	Apple	\N
2	Samsung	\N
3	Microsoft	\N
4	Sony	\N
5	Intel	\N
\.


--
-- Data for Name: cart_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cart_items (id, user_id, product_id, variation_id, qty, created_at, updated_at) FROM stdin;
3	2	2	\N	1	2026-08-09 14:19:39	2026-08-09 14:19:39
22	3	19	\N	1	2026-08-09 14:52:37	2026-08-09 14:52:37
23	3	15	\N	2	2026-08-09 14:52:37	2026-08-09 14:52:37
24	3	14	\N	1	2026-08-09 14:52:37	2026-08-09 14:52:37
25	3	13	\N	1	2026-08-09 14:52:37	2026-08-09 14:52:37
26	3	22	\N	1	2026-08-09 14:52:37	2026-08-09 14:52:37
103	9	12	40	1	2026-08-12 12:21:23	2026-08-12 12:21:23
104	9	8	23	2	2026-08-12 12:21:23	2026-08-12 12:21:23
105	9	19	71	1	2026-08-12 12:21:23	2026-08-12 12:21:23
106	9	17	62	1	2026-08-12 12:21:23	2026-08-12 12:21:23
70	8	2	4	39	2026-08-12 10:40:34	2026-08-12 10:40:34
71	8	2	5	1	2026-08-12 10:40:34	2026-08-12 10:40:34
72	8	3	7	15	2026-08-12 10:40:34	2026-08-12 10:40:34
142	1	8	24	1	2026-08-13 08:24:20	2026-08-13 08:24:20
143	1	11	36	1	2026-08-13 08:24:20	2026-08-13 08:24:20
144	1	8	23	35	2026-08-13 08:24:20	2026-08-13 08:24:20
150	78	22	85	1	2026-08-13 10:49:42	2026-08-13 10:49:42
\.


--
-- Data for Name: categories2; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories2 (id, name, slug, parent, description, display, image, menu_order, count, has_children, _links) FROM stdin;
18	Men	men	0	\N	\N	\N	6	\N	\N	\N
19	Shirts	shirts	18	\N	\N	\N	11	\N	\N	\N
20	Shoes	shoes-men	28	\N	\N	\N	10	\N	\N	\N
21	T-Shirts	t-shirts	18	\N	\N	\N	12	\N	\N	\N
22	Women	women	24	\N	\N	\N	13	\N	\N	\N
23	Bags-ramo	bags	0	\N	\N	\N	4	\N	\N	\N
24	Bag-ramo	bags-men-ramo	18	\N	\N	\N	7	\N	\N	\N
25	Blazers-ramo	blazers	22	\N	\N	\N	14	\N	\N	\N
26	Dresses	dresses	22	\N	\N	\N	15	\N	\N	\N
28	Jackets	jackets-men	30	\N	\N	\N	9	\N	\N	\N
29	Jeans	jeans	22	\N	\N	\N	17	\N	\N	\N
30	Jeans Man	jeans-men	18	\N	\N	\N	8	\N	\N	\N
311	mobile-phones	Mobile-phones	2	\N	\N	\N	2	\N	\N	\N
314	Uncategorized	uncategorized-ar	0	\N	\N	\N	0	\N	\N	\N
208	Clothing	clothing	0	\N	\N	categories/wsYZvWbNXsKROWvSSK2iyQtI8L0xEXLxUFGCnn8j.png	3	\N	\N	\N
\.


--
-- Data for Name: category_brand_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.category_brand_requests (id, type, name, description, status, admin_note, vendor_user_id, vendor_name, created_at, updated_at, parent_category_id, parent_category_name) FROM stdin;
\.


--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.countries (id, code, name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: coupon_user_limits; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.coupon_user_limits (id, coupon_id, user_id, use_count, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: coupons; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.coupons (id, code, amount, status, discount_type, date_created, date_created_gmt, date_modified, date_modified_gmt, date_expires, date_expires_gmt, usage_count, individual_use, usage_limit, usage_limit_per_user, limit_usage_to_x_items, product_ids, excluded_product_ids, product_categories, excluded_product_categories, free_shipping, exclude_sale_items, minimum_amount, maximum_amount, email_restrictions, used_by, description, meta_data, vendor_id) FROM stdin;
1	SAVER20	20.00	publish	percent	2026-05-06 17:10:02	2026-05-06 17:10:02	2026-05-06 17:10:02	2026-05-06 17:10:02	\N	\N	0	f	\N	\N	\N	[]	[]	[]	[]	f	f	50.00	0.00	[]	[]	\N	[]	\N
2	SAVERR20	20.00	publish	percent	2026-05-06 17:10:02	2026-05-06 17:10:02	2026-05-06 17:10:02	2026-05-06 17:10:02	\N	\N	0	f	\N	\N	\N	[]	[]	[]	[]	f	f	50.00	0.00	[]	[]	\N	[]	\N
3	FFFF344	50.05	publish	percent	2026-08-10 12:06:14	2026-08-10 12:06:14	2026-08-10 12:06:14	2026-08-10 12:06:14	2026-08-18 00:00:00	\N	0	f	7	\N	\N	[]	[]	[]	[]	f	f	500.03	0.00	[]	[]	\N	[]	\N
\.


--
-- Data for Name: device_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.device_access_tokens (id, device_id, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at, key_pass, identifier, blocked, about_device) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: getposttest; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.getposttest (id, title, content, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: idempotency_keys; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.idempotency_keys (id, key, user_id, order_id, created_at) FROM stdin;
\.


--
-- Data for Name: image_gallery_images; Type: TABLE DATA; Schema: public; Owner: ramo_app
--

COPY public.image_gallery_images (id, path, original_name, mime_type, file_size, width, height, uploaded_by, created_at, updated_at) FROM stdin;
18	image-gallery/TS3jg0Uwd2cOWGo7107QhMXOpGk0bkE8roB7qFsY.png	Firefly_create a lot of roots that matches the same type of roots (1).png	image/png	1350332	1536	1046	1	2026-08-12 14:31:10	2026-08-12 14:31:10
19	image-gallery/wplKQURnGABGUo1uRrqykvc48AM5IQ7fO7fozO5y.png	ChatGPT Image May 22, 2026, 09_06_49 PM.png	image/png	1851744	1024	1024	1	2026-08-12 14:31:46	2026-08-12 14:31:46
20	image-gallery/Oz5lvZeeXPHipbtH3n4tCJ20Lv5GxrFUBFn0lNst.png	thumbnail_Esigns_Additional_Banner_Image_Gallery.png	image/png	497375	815	384	1	2026-08-12 14:33:35	2026-08-12 14:33:35
21	image-gallery/JRsRpX1ME2SEp6UNZXwFb5JJeapo51tOT1QLncH3.jpg	images (1).jfif	image/jpeg	38821	570	350	1	2026-08-12 14:33:42	2026-08-12 14:33:42
\.


--
-- Data for Name: koto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.koto (id, key_in, identfier) FROM stdin;
\.


--
-- Data for Name: link_access_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.link_access_logs (id, link_name, usage_times, user_call_id) FROM stdin;
\.


--
-- Data for Name: links; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.links (id, link, data, created_at, updated_at, post_data) FROM stdin;
\.


--
-- Data for Name: links_json_res; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.links_json_res (id, link, data, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: links_logs_two; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.links_logs_two (id, link, data, post_data, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2019_12_14_000001_create_personal_access_tokens_table	1
2	2024_01_01_000001_create_ramo_store_schema	1
3	2025_05_06_000001_create_category_brand_requests_table	1
4	2025_05_06_000002_add_parent_to_category_brand_requests	1
5	2026_01_18_155149_add_registration_fields_to_users_table	1
6	2026_05_02_000001_create_ecommerce_tables	1
7	2026_05_02_100000_add_is_blocked_to_users_table	1
8	2026_05_03_011946_create_refund_requests_table	1
9	2026_05_03_011947_create_order_messages_table	1
10	2026_05_03_012000_create_order_sub_orders_table	1
11	2026_05_03_012001_add_sub_order_id_to_order_messages	1
12	2026_05_04_000001_add_auth_fields_and_otp_verifications	1
13	2026_05_06_152830_add_image_to_brands_table	1
14	2026_05_12_000001_add_status_columns_to_product_variations_table	2
15	2026_05_12_000002_create_idempotency_keys_table	2
16	2026_07_11_131407_add_button_mode_to_products_data	2
17	2026_08_08_000001_add_manual_payment_verification	2
18	2026_08_09_000001_add_computed_order_statuses	3
19	2026_08_09_000001_make_user_email_nullable_for_phone_otp	4
20	2026_08_10_000001_add_vendor_id_to_coupons_table	5
21	2026_08_12_000002_create_image_gallery_images_table	6
\.


--
-- Data for Name: order_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.order_messages (id, order_id, customer_id, vendor_id, sender_type, message, is_vendor_response, created_at, updated_at, sub_order_id) FROM stdin;
1	4	3	16	customer	Hola	f	2026-08-09 14:33:46	2026-08-09 14:33:46	4
2	7	5	3	customer	Hello bitch	f	2026-08-09 15:09:04	2026-08-09 15:09:04	7
3	7	5	3	vendor	hey there	t	2026-08-09 15:09:26	2026-08-09 15:09:26	7
\.


--
-- Data for Name: order_sub_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.order_sub_orders (id, parent_order_id, vendor_id, customer_id, status, line_items, subtotal, discount_total, total, tracking_number, tracking_carrier, timeline, notes, created_at, updated_at, vendor_status) FROM stdin;
2	2	16	1	pending	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	890.00	0.00	890.00	\N	\N	[]	\N	2026-08-08 04:06:23	2026-08-08 04:06:23	pending
1	1	12	1	returned	[{"product_id":4,"variation_id":9,"name":"Canvas Backpack","sku":null,"quantity":1,"price":760,"subtotal":760,"attributes":{"Color":"Navy"}}]	760.00	0.00	760.00	\N	\N	[{"status":"shipped","note":"","by":"admin:1","at":"2026-08-09 09:49:47"},{"status":"delivered","note":"","by":"admin:1","at":"2026-08-09 09:50:00"},{"status":"returned","note":"","by":"admin:1","at":"2026-08-09 09:50:11"}]	\N	2026-08-08 04:04:32	2026-08-09 09:50:11	returned
3	3	17	3	pending	[{"product_id":7,"variation_id":null,"name":"Distressed Boyfriend Jeans","sku":null,"quantity":1,"price":615,"subtotal":615,"attributes":[]}]	615.00	0.00	615.00	\N	\N	[]	\N	2026-08-09 14:25:25	2026-08-09 14:25:25	pending
4	4	16	3	pending	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	890.00	0.00	890.00	\N	\N	[]	\N	2026-08-09 14:26:24	2026-08-09 14:26:24	pending
5	5	3	3	delivered	[{"product_id":22,"variation_id":null,"name":"Luxe Velvet Jeans \\u2014 Olive","sku":null,"quantity":1,"price":2526,"subtotal":2526,"attributes":[]}]	2526.00	0.00	2526.00	\N	\N	[{"status":"returned","note":null,"by":"vendor:3","at":"2026-08-09 14:47:12"},{"status":"delivered","note":null,"by":"vendor:3","at":"2026-08-09 14:48:06"}]	\N	2026-08-09 14:42:02	2026-08-09 14:48:06	delivered
6	6	16	5	pending	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	890.00	0.00	890.00	\N	\N	[]	\N	2026-08-09 15:04:29	2026-08-09 15:04:29	pending
7	7	3	5	shipped	[{"product_id":22,"variation_id":null,"name":"Luxe Velvet Jeans \\u2014 Olive","sku":null,"quantity":1,"price":2526,"subtotal":2526,"attributes":[]}]	2526.00	0.00	2526.00	\N	\N	[{"status":"processing","note":null,"by":"vendor:3","at":"2026-08-09 15:08:04"},{"status":"shipped","note":null,"by":"vendor:3","at":"2026-08-09 15:08:25"}]	\N	2026-08-09 15:06:57	2026-08-09 15:08:25	shipped
8	8	12	6	pending	[{"product_id":1,"variation_id":null,"name":"Classic Leather Tote Bag","sku":null,"quantity":1,"price":1850,"subtotal":1850,"attributes":[]}]	1850.00	0.00	1850.00	\N	\N	[]	\N	2026-08-10 04:34:02	2026-08-10 04:34:02	pending
9	9	12	7	pending	[{"product_id":3,"variation_id":null,"name":"Quilted Chain Shoulder Bag","sku":null,"quantity":1,"price":2200,"subtotal":2200,"attributes":[]}]	2200.00	0.00	2200.00	\N	\N	[]	\N	2026-08-10 04:37:19	2026-08-10 04:37:19	pending
10	10	12	1	pending	[{"product_id":2,"variation_id":null,"name":"Mini Crossbody Bag","sku":null,"quantity":1,"price":637.5,"subtotal":637.5,"attributes":[]}]	637.50	0.00	637.50	\N	\N	[]	\N	2026-08-10 12:07:01	2026-08-10 12:07:01	pending
11	11	12	1	pending	[{"product_id":3,"variation_id":7,"name":"Quilted Chain Shoulder Bag","sku":null,"quantity":1,"price":2200,"subtotal":2200,"attributes":{"Color":"Black"}}]	2200.00	0.00	2200.00	\N	\N	[]	\N	2026-08-11 09:49:04	2026-08-11 09:49:04	pending
12	12	12	8	pending	[{"product_id":2,"variation_id":4,"name":"Mini Crossbody Bag","sku":null,"quantity":1,"price":637.5,"subtotal":637.5,"attributes":{"Color":"Beige"}}]	637.50	0.00	637.50	\N	\N	[]	\N	2026-08-12 10:11:02	2026-08-12 10:11:02	pending
13	13	16	10	shipped	[{"product_id":11,"variation_id":36,"name":"Women's Tailored Blazer","sku":null,"quantity":1,"price":1250,"subtotal":1250,"attributes":{"Color":"Black","Size":"S"}}]	1250.00	0.00	1250.00	\N	\N	[{"status":"shipped","note":"","by":"admin:1","at":"2026-08-12 11:26:13"}]	\N	2026-08-12 11:24:08	2026-08-12 11:26:13	shipped
14	14	16	\N	pending	[{"product_id":12,"variation_id":40,"name":"Men's Double-Breasted Blazer","sku":null,"quantity":1,"price":1512,"subtotal":1512,"attributes":{"Color":"Navy","Size":"M"}}]	1512.00	0.00	1512.00	\N	\N	[]	\N	2026-08-12 21:42:14	2026-08-12 21:42:14	pending
15	15	16	75	pending	[{"product_id":20,"variation_id":76,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":{"Color":"Multi","Size":"XS"}}]	890.00	0.00	890.00	\N	\N	[]	\N	2026-08-12 21:49:10	2026-08-12 21:49:10	pending
16	16	12	131	pending	[{"product_id":1,"variation_id":1,"name":"Classic Leather Tote Bag","sku":null,"quantity":1,"price":1850,"subtotal":1850,"attributes":{"Color":"Black"}}]	1850.00	0.00	1850.00	\N	\N	[]	\N	2026-08-13 11:13:13	2026-08-13 11:13:13	pending
17	16	16	131	pending	[{"product_id":21,"variation_id":83,"name":"Midi Slip Dress","sku":null,"quantity":1,"price":880,"subtotal":880,"attributes":{"Color":"Nude","Size":"S"}}]	880.00	0.00	880.00	\N	\N	[]	\N	2026-08-13 11:13:13	2026-08-13 11:13:13	pending
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.orders (id, parent_id, parent_vendors_ids, parent_vendors_data, status, currency, version, prices_include_tax, date_created, date_modified, discount_total, discount_tax, shipping_total, shipping_tax, cart_tax, coupon_code, final_total, original_total, coupon_applied, total_tax, customer_id, order_key, billing, shipping, payment_method, payment_method_title, transaction_id, customer_ip_address, customer_user_agent, created_via, customer_note, date_completed, date_paid, cart_hash, meta_data, line_items, tax_lines, shipping_lines, fee_lines, coupon_lines, refunds, payment_url, is_editable, needs_payment, needs_processing, bacs_info, currency_symbol, _links, date_created_gmt, date_modified_gmt, date_completed_gmt, date_paid_gmt, set_paid, number, timeline, updated_at, created_at, payment_status, payment_receipt_path, payment_receipt_name, payment_receipt_uploaded_at, payment_reviewed_at, payment_reviewed_by, payment_rejection_reason, general_order_status, general_order_status_override, general_order_status_override_reason, general_order_status_override_by, general_order_status_override_at) FROM stdin;
1	0	\N	\N	completed	EGP	\N	f	2026-08-08 04:04:32	2026-08-09 09:51:04	0.00	0.00	0.00	0.00	0.00	\N	760.00	760	0	0.00	1	wc_RDL3pTG00L8EvYMMmudN	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544960931289","longitude":"30.80587494633731"}	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544960931289","longitude":"30.80587494633731"}	manual_wallet	Pay by Wallet	\N	10.56.4.51	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	11287c40829e028b61ec6d73ec1b074d	\N	[{"product_id":4,"variation_id":9,"name":"Canvas Backpack","sku":null,"quantity":1,"price":760,"subtotal":760,"attributes":{"Color":"Navy"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-08 04:04:32	2026-08-08 04:04:32			f	1	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:05:27"},{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:05:49"},{"status":"processing","note":"General order status force-overridden to processing: klj","by":"admin:1","at":"2026-08-09 09:48:33","type":"general_status_override"},{"status":"partially_shipped","note":"General order status force-overridden to partially_shipped: klj","by":"admin:1","at":"2026-08-09 09:49:14","type":"general_status_override"},{"status":"partially_delivered","note":"General order status force-overridden to partially_delivered: klj","by":"admin:1","at":"2026-08-09 09:50:40","type":"general_status_override"},{"status":"completed","note":"General order status force-overridden to completed: klj","by":"admin:1","at":"2026-08-09 09:51:04","type":"general_status_override"}]	2026-08-09 09:51:04	2026-08-08 04:04:32	pending_verification	payment-receipts/eo5R4QKHI4TWNOQHlWaL2RlVxjDHfTG4TDtmVoQW.png	Screenshot (3).png	2026-08-08 04:05:49	\N	\N	\N	pending	completed	klj	1	2026-08-09 09:51:04
2	0	\N	\N	partially_cancelled	EGP	\N	f	2026-08-08 04:06:23	2026-08-09 14:13:26	0.00	0.00	0.00	0.00	0.00	\N	890.00	890	0	0.00	1	wc_1gUBnyOU3kLaME5jPMMK	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	manual_wallet	Pay by Wallet	\N	10.56.4.51	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	2026-08-08 04:14:49	6bcb195325a706e72a319e5582cb3d38	\N	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-08 04:06:23	2026-08-08 04:06:23		2026-08-08 04:14:49	t	2	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:06:33"},{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:09:06"},{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:09:19"},{"status":"rejected","note":"Payment receipt rejected: Blurred","by":"admin:1","at":"2026-08-08 04:12:43"},{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-08 04:14:38"},{"status":"confirmed","note":"Payment receipt approved.","by":"admin:1","at":"2026-08-08 04:14:49"},{"status":"partially_shipped","note":"General order status force-overridden to partially_shipped: dff","by":"admin:1","at":"2026-08-09 14:08:59","type":"general_status_override"},{"status":"partially_delivered","note":"General order status force-overridden to partially_delivered: dff","by":"admin:1","at":"2026-08-09 14:12:21","type":"general_status_override"},{"status":"partially_delivered","note":"General order status force-overridden to partially_delivered: dfdf","by":"admin:1","at":"2026-08-09 14:13:13","type":"general_status_override"},{"status":"partially_cancelled","note":"General order status force-overridden to partially_cancelled.","by":"admin:1","at":"2026-08-09 14:13:26","type":"general_status_override"}]	2026-08-09 14:13:26	2026-08-08 04:06:23	confirmed	payment-receipts/HUbhyU0YazbFgvWf4EY8GuAaggWwHTvEYIArJTDG.png	Screenshot (7).png	2026-08-08 04:14:38	2026-08-08 04:14:49	1	\N	pending	partially_cancelled	\N	1	2026-08-09 14:13:26
6	0	\N	\N	processing	EGP	\N	f	2026-08-09 15:04:29	2026-08-09 15:05:29	0.00	0.00	0.00	0.00	0.00	\N	890.00	890	0	0.00	5	wc_YNJSRtafCjCVZF0aHI6c	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+201002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457762","longitude":"30.804594"}	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+201002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457762","longitude":"30.804594"}	manual_wallet	Pay by Wallet	\N	10.48.12.10	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36	website		\N	2026-08-09 15:05:24	6bcb195325a706e72a319e5582cb3d38	\N	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-09 15:04:29	2026-08-09 15:04:29		2026-08-09 15:05:24	t	6	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-09 15:04:45"},{"status":"confirmed","note":"Payment receipt approved.","by":"admin:1","at":"2026-08-09 15:05:24"},{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-09 15:05:29","type":"general_status_override"}]	2026-08-09 15:05:29	2026-08-09 15:04:29	confirmed	payment-receipts/daKsZCitLJCWmHy5IsHEUn4VN3lzIRPQMcWnUPEg.png	Screenshot_20260809-060354.png	2026-08-09 15:04:45	2026-08-09 15:05:24	1	\N	pending	processing	\N	1	2026-08-09 15:05:29
7	0	\N	\N	shipped	EGP	\N	f	2026-08-09 15:06:57	2026-08-09 15:08:25	0.00	0.00	0.00	0.00	0.00	\N	2526.00	2526	0	0.00	5	wc_UqV0gBLWtlj8oBxV1Wte	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+201002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457762","longitude":"30.804594"}	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+201002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457762","longitude":"30.804594"}	manual_wallet	Pay by Wallet	\N	10.48.12.10	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36	website		\N	2026-08-09 15:07:32	c449c9fc0bb6a74c094b8b7a725f88de	\N	[{"product_id":22,"variation_id":null,"name":"Luxe Velvet Jeans \\u2014 Olive","sku":null,"quantity":1,"price":2526,"subtotal":2526,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-09 15:06:57	2026-08-09 15:06:57		2026-08-09 15:07:32	t	7	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-09 15:07:30"},{"status":"confirmed","note":"Payment receipt approved.","by":"vendor:3","at":"2026-08-09 15:07:32"}]	2026-08-09 15:08:25	2026-08-09 15:06:57	confirmed	payment-receipts/T947R4miyFd76ObAJHJJfelr6BJLdrwgHMaIX6Mh.png	Screenshot_20260809-060354.png	2026-08-09 15:07:30	2026-08-09 15:07:32	3	\N	shipped	\N	\N	\N	\N
3	0	\N	\N	shipped	EGP	\N	f	2026-08-09 14:25:25	2026-08-09 14:32:49	0.00	0.00	0.00	0.00	0.00	\N	615.00	615	0	0.00	3	wc_mTDP3AzuN9QY3wn6shr3	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	manual_wallet	Pay by Wallet	\N	10.48.0.234	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	4b678843897198843d3ff64bafdd657a	\N	[{"product_id":7,"variation_id":null,"name":"Distressed Boyfriend Jeans","sku":null,"quantity":1,"price":615,"subtotal":615,"attributes":[]}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-09 14:25:25	2026-08-09 14:25:25			f	3	[{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-09 14:32:06","type":"general_status_override"},{"status":"partially_delivered","note":"General order status force-overridden to partially_delivered.","by":"admin:1","at":"2026-08-09 14:32:17","type":"general_status_override"},{"status":"shipped","note":"General order status force-overridden to shipped.","by":"admin:1","at":"2026-08-09 14:32:49","type":"general_status_override"}]	2026-08-09 14:32:49	2026-08-09 14:25:25	pending_verification	\N	\N	\N	\N	\N	\N	pending	shipped	\N	1	2026-08-09 14:32:49
4	0	\N	\N	shipped	EGP	\N	f	2026-08-09 14:26:24	2026-08-09 14:34:12	0.00	0.00	0.00	0.00	0.00	\N	890.00	890	0	0.00	3	wc_VpPIDJkTCdCi0q91ojv8	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	manual_wallet	Pay by Wallet	\N	10.48.0.234	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	2026-08-09 14:29:07	6bcb195325a706e72a319e5582cb3d38	\N	[{"product_id":20,"variation_id":null,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-09 14:26:24	2026-08-09 14:26:24		2026-08-09 14:29:07	t	4	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-09 14:26:49"},{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-09 14:27:17","type":"general_status_override"},{"status":"confirmed","note":"Payment receipt approved.","by":"admin:1","at":"2026-08-09 14:29:07"},{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-09 14:29:31","type":"general_status_override"},{"status":"partially_shipped","note":"General order status force-overridden to partially_shipped.","by":"admin:1","at":"2026-08-09 14:30:49","type":"general_status_override"},{"status":"partially_shipped","note":"General order status force-overridden to partially_shipped.","by":"admin:1","at":"2026-08-09 14:31:12","type":"general_status_override"},{"status":"shipped","note":"General order status force-overridden to shipped.","by":"admin:1","at":"2026-08-09 14:33:26","type":"general_status_override"}]	2026-08-09 14:34:12	2026-08-09 14:26:24	confirmed	payment-receipts/twS6B1DlW3IZc5BTkMtRWKDMrH6rb1uV87bSrUGq.png	Screenshot (2).png	2026-08-09 14:26:49	2026-08-09 14:29:07	1	\N	pending	shipped	\N	1	2026-08-09 14:33:26
5	0	\N	\N	processing	EGP	\N	f	2026-08-09 14:42:02	2026-08-09 14:48:06	0.00	0.00	0.00	0.00	0.00	\N	2526.00	2526	0	0.00	3	wc_jlJ8Bciasb7mjRyl0acH	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	{"first_name":"Ramona","last_name":"hgg","email":null,"phone":"+3453454555","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.44544435121235","longitude":"30.805890948199856"}	manual_wallet	Pay by Wallet	\N	10.48.26.141	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	2026-08-09 14:42:39	c449c9fc0bb6a74c094b8b7a725f88de	\N	[{"product_id":22,"variation_id":null,"name":"Luxe Velvet Jeans \\u2014 Olive","sku":null,"quantity":1,"price":2526,"subtotal":2526,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-09 14:42:02	2026-08-09 14:42:02		2026-08-09 14:42:39	t	5	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-09 14:42:11"},{"status":"confirmed","note":"Payment receipt approved.","by":"admin:1","at":"2026-08-09 14:42:39"},{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-09 14:42:48","type":"general_status_override"}]	2026-08-09 14:48:06	2026-08-09 14:42:02	confirmed	payment-receipts/fLb892cnsOb316DxUId7cNACVA3SMYHjtHighatd.png	Screenshot (7).png	2026-08-09 14:42:11	2026-08-09 14:42:39	1	\N	completed	processing	\N	1	2026-08-09 14:42:48
9	0	\N	\N	pending	EGP	\N	f	2026-08-10 04:37:19	2026-08-10 04:37:19	0.00	0.00	0.00	0.00	0.00	\N	2200.00	2200	0	0.00	7	wc_oepGwXR6mw5fFCiXzz2q	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+200885255566","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457688","longitude":"30.8046002"}	{"first_name":"Ramez","last_name":"Malak","email":null,"phone":"+200885255566","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457688","longitude":"30.8046002"}	manual_instapay	Pay by InstaPay	\N	10.28.20.184	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36	website		\N	\N	6c3ffbdb6b72eac0fa0d4b34addc84ae	\N	[{"product_id":3,"variation_id":null,"name":"Quilted Chain Shoulder Bag","sku":null,"quantity":1,"price":2200,"subtotal":2200,"attributes":[]}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-10 04:37:19	2026-08-10 04:37:19			f	9	[]	2026-08-10 04:37:19	2026-08-10 04:37:19	pending_verification	\N	\N	\N	\N	\N	\N	pending	\N	\N	\N	\N
8	0	\N	\N	pending	EGP	\N	f	2026-08-10 04:34:02	2026-08-10 04:34:55	0.00	0.00	0.00	0.00	0.00	\N	1850.00	1850	0	0.00	6	wc_X2GEH2scvThgGMRVxYaG	{"first_name":"Ramo","last_name":"Ramez","email":null,"phone":"+200196464666","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457836","longitude":"30.8046024"}	{"first_name":"Ramo","last_name":"Ramez","email":null,"phone":"+200196464666","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4457836","longitude":"30.8046024"}	manual_instapay	Pay by InstaPay	\N	10.28.8.185	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36	website		\N	\N	7159e64201fe43c2be043d0d73a77314	\N	[{"product_id":1,"variation_id":null,"name":"Classic Leather Tote Bag","sku":null,"quantity":1,"price":1850,"subtotal":1850,"attributes":[]}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-10 04:34:02	2026-08-10 04:34:02			f	8	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-10 04:34:55"}]	2026-08-10 04:34:55	2026-08-10 04:34:02	pending_verification	payment-receipts/gK07XR4w2DItYrO42ulj66D67V0RGSOToe2TdUCV.png	Screenshot_20260808-194630.png	2026-08-10 04:34:55	\N	\N	\N	pending	\N	\N	\N	\N
10	0	\N	\N	pending	EGP	\N	f	2026-08-10 12:07:01	2026-08-10 12:23:40	0.00	0.00	0.00	0.00	0.00	\N	637.50	638	0	0.00	1	wc_limnt51d9ubAxGsamuQp	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	manual_wallet	Pay by Wallet	\N	197.59.76.10	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	97f31b1cff812dfd9260d5bbabc0a6b3	\N	[{"product_id":2,"variation_id":null,"name":"Mini Crossbody Bag","sku":null,"quantity":1,"price":637.5,"subtotal":637.5,"attributes":[]}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-10 12:07:01	2026-08-10 12:07:01			f	10	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-10 12:23:40"}]	2026-08-10 12:23:40	2026-08-10 12:07:01	pending_verification	payment-receipts/Nv5KlnZaAg5ZABGn5IjqygrCAXCSqm1hdhBX0Lvw.png	download.png	2026-08-10 12:23:40	\N	\N	\N	pending	\N	\N	\N	\N
11	0	\N	\N	pending	EGP	\N	f	2026-08-11 09:49:04	2026-08-11 09:49:16	0.00	0.00	0.00	0.00	0.00	\N	2200.00	2200	0	0.00	1	wc_y1OBryxSUm0Z43OxtXCD	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	{"first_name":"Sara","last_name":"Ehab","email":"adminramoui@gmail.com","phone":"7865876587","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445440824393827","longitude":"30.805906818883177"}	manual_wallet	Pay by Wallet	\N	10.64.12.102	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	5c5f4aa5a60cf4913213430341e77b3f	\N	[{"product_id":3,"variation_id":7,"name":"Quilted Chain Shoulder Bag","sku":null,"quantity":1,"price":2200,"subtotal":2200,"attributes":{"Color":"Black"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-11 09:49:04	2026-08-11 09:49:04			f	11	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-11 09:49:16"}]	2026-08-11 09:49:16	2026-08-11 09:49:04	pending_verification	payment-receipts/cAkI4vcTdDGvBFOFdQMfP2JUbKSRbEcDseB1vyjo.png	Screenshot (6).png	2026-08-11 09:49:16	\N	\N	\N	pending	\N	\N	\N	\N
12	0	\N	\N	pending	EGP	\N	f	2026-08-12 10:11:02	2026-08-12 10:11:02	0.00	0.00	100.00	0.00	0.00	\N	737.50	638	0	0.00	8	wc_1VpMcXzArSJfnS4PR3Xp	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+34523452444","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445439501836887","longitude":"30.80590917009552"}	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+34523452444","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445439501836887","longitude":"30.80590917009552"}	manual_wallet	Pay by Wallet	\N	172.16.0.210	Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36	website		\N	\N	663d9e23e198acc3e27d16d4c3990877	\N	[{"product_id":2,"variation_id":4,"name":"Mini Crossbody Bag","sku":null,"quantity":1,"price":637.5,"subtotal":637.5,"attributes":{"Color":"Beige"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-12 10:11:02	2026-08-12 10:11:02			f	12	[]	2026-08-12 10:11:02	2026-08-12 10:11:02	pending_verification	\N	\N	\N	\N	\N	\N	pending	\N	\N	\N	\N
13	0	\N	\N	processing	EGP	\N	f	2026-08-12 11:24:08	2026-08-12 12:53:30	0.00	0.00	100.00	0.00	0.00	\N	1350.00	1250	0	0.00	10	wc_nFtLNiJgvtrEl8FfD6bg	{"first_name":"Kkkhh","last_name":"Hgfgh","email":"gggf@gmail.com","phone":"+20123654525","address_1":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","address_2":null,"city":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","state":"Aswan","country":"EG","latitude":"28.445801","longitude":"30.804578"}	{"first_name":"Kkkhh","last_name":"Hgfgh","email":"gggf@gmail.com","phone":"+20123654525","address_1":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","address_2":null,"city":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","state":"Aswan","country":"EG","latitude":"28.445801","longitude":"30.804578"}	manual_instapay	Pay by InstaPay	\N	172.16.0.210	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36	website		\N	2026-08-12 11:25:59	316a5a5ca1fc2df3a269335d6adeba90	\N	[{"product_id":11,"variation_id":36,"name":"Women's Tailored Blazer","sku":null,"quantity":1,"price":1250,"subtotal":1250,"attributes":{"Color":"Black","Size":"S"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-08-12 11:24:08	2026-08-12 11:24:08		2026-08-12 11:25:59	t	13	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-12 11:24:31"},{"status":"confirmed","note":"Payment receipt approved.","by":"admin:1","at":"2026-08-12 11:25:59"},{"status":"processing","note":"General order status force-overridden to processing.","by":"admin:1","at":"2026-08-12 11:26:05","type":"general_status_override"}]	2026-08-12 12:53:30	2026-08-12 11:24:08	confirmed	payment-receipts/dvICGcMQsz06WPnOCuXpukECXtDF1dY83HronUqV.jpg	1000321976.jpg	2026-08-12 11:24:31	2026-08-12 11:25:59	1	\N	shipped	processing	\N	1	2026-08-12 11:26:05
14	0	\N	\N	pending	EGP	\N	f	2026-08-12 21:42:14	2026-08-12 21:42:14	0.00	0.00	100.00	0.00	0.00	\N	1612.00	1512	0	0.00	\N	wc_97ZxvRLQYueTKbglOIK4	{"first_name":"Ramez","last_name":"Asaad","email":"ramezmfarouk@gmail.com","phone":"01002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445442","longitude":"30.8059005"}	{"first_name":"Ramez","last_name":"Asaad","email":"ramezmfarouk@gmail.com","phone":"01002722375","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445442","longitude":"30.8059005"}	manual_wallet	Pay by Wallet	\N	172.16.0.210	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	45a2d5bddf51103b5f909c7fa4e74ee0	\N	[{"product_id":12,"variation_id":40,"name":"Men's Double-Breasted Blazer","sku":null,"quantity":1,"price":1512,"subtotal":1512,"attributes":{"Color":"Navy","Size":"M"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-12 21:42:14	2026-08-12 21:42:14			f	14	[]	2026-08-12 21:42:14	2026-08-12 21:42:14	pending_verification	\N	\N	\N	\N	\N	\N	pending	\N	\N	\N	\N
16	0	\N	\N	pending	EGP	\N	f	2026-08-13 11:13:13	2026-08-13 11:13:13	0.00	0.00	100.00	0.00	0.00	\N	2830.00	2730	0	0.00	131	wc_w79kEYzKGD4EIXS24dgh	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+200000086666","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445777","longitude":"30.8046012"}	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+200000086666","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445777","longitude":"30.8046012"}	manual_wallet	Pay by Wallet	\N	172.16.0.210	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36	website		\N	\N	80ea97717037c60a9c3d4d5538a2da0d	\N	[{"product_id":1,"variation_id":1,"name":"Classic Leather Tote Bag","sku":null,"quantity":1,"price":1850,"subtotal":1850,"attributes":{"Color":"Black"}},{"product_id":21,"variation_id":83,"name":"Midi Slip Dress","sku":null,"quantity":1,"price":880,"subtotal":880,"attributes":{"Color":"Nude","Size":"S"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-13 11:13:13	2026-08-13 11:13:13			f	16	[]	2026-08-13 11:13:13	2026-08-13 11:13:13	pending_verification	\N	\N	\N	\N	\N	\N	pending	\N	\N	\N	\N
15	0	\N	\N	pending	EGP	\N	f	2026-08-12 21:49:10	2026-08-12 21:49:25	0.00	0.00	100.00	0.00	0.00	\N	990.00	890	0	0.00	75	wc_wuvLgkOLglOAe9TGhJso	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+78608769876","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445441886428167","longitude":"30.805899818569"}	{"first_name":"Ramez","last_name":"malak","email":null,"phone":"+78608769876","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445441886428167","longitude":"30.805899818569"}	manual_wallet	Pay by Wallet	\N	172.16.0.210	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36	website		\N	\N	bebbdbd636ad93f7ec9ac00fcc8a33c8	\N	[{"product_id":20,"variation_id":76,"name":"Floral Wrap Dress","sku":null,"quantity":1,"price":890,"subtotal":890,"attributes":{"Color":"Multi","Size":"XS"}}]	\N	\N	\N	\N	\N		t	t	t	\N	ج.م	\N	2026-08-12 21:49:10	2026-08-12 21:49:10			f	15	[{"status":"pending_verification","note":"Payment receipt uploaded for review.","at":"2026-08-12 21:49:25"}]	2026-08-12 21:49:25	2026-08-12 21:49:10	pending_verification	payment-receipts/hiiCCGHQm0LP3bqTXQJRFnr1pfZBFwvaVdaxmjqu.png	thumbnail_Esigns_Additional_Banner_Image_Gallery.png	2026-08-12 21:49:25	\N	\N	\N	pending	\N	\N	\N	\N
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, phone, otp_code, expires_at, attempts, resend_count, resend_window_start, verified, created_at, updated_at) FROM stdin;
1	+45234534444	866041	2026-08-09 14:24:17	0	0	\N	t	2026-08-09 14:19:17	2026-08-09 14:19:22
2	+3453454555	180770	2026-08-09 14:29:39	0	0	\N	t	2026-08-09 14:24:39	2026-08-09 14:24:44
3	+76587657876	921205	2026-08-09 15:06:28	0	0	\N	t	2026-08-09 15:01:28	2026-08-09 15:01:42
4	+201002722375	905454	2026-08-09 15:08:36	0	0	\N	t	2026-08-09 15:03:36	2026-08-09 15:03:48
5	+200196464666	876163	2026-08-10 04:38:15	0	0	\N	t	2026-08-10 04:33:15	2026-08-10 04:33:22
6	+200885255566	192005	2026-08-10 04:41:37	0	0	\N	t	2026-08-10 04:36:37	2026-08-10 04:36:47
7	+200885255566	208198	2026-08-10 04:42:59	0	0	\N	t	2026-08-10 04:37:59	2026-08-10 04:38:08
8	+34523452444	274230	2026-08-12 10:05:45	0	0	\N	t	2026-08-12 10:00:45	2026-08-12 10:00:52
9	+205888558888	284018	2026-08-12 11:22:59	0	0	\N	t	2026-08-12 11:17:59	2026-08-12 11:18:17
10	+20123654525	629349	2026-08-12 11:25:20	0	0	\N	t	2026-08-12 11:20:20	2026-08-12 11:20:40
11	+78608769876	171757	2026-08-12 21:51:11	0	0	\N	t	2026-08-12 21:46:11	2026-08-12 21:46:19
12	+201123456789	676892	2026-08-13 06:25:36	0	0	\N	t	2026-08-13 06:20:36	2026-08-13 06:20:46
13	+205556655555	994730	2026-08-13 11:13:42	0	0	\N	t	2026-08-13 11:08:42	2026-08-13 11:08:52
15	+85566699988	041486	2026-08-13 11:16:50	0	1	2026-08-13 11:11:50	f	2026-08-13 11:11:50	2026-08-13 11:11:50
16	+200000086666	769328	2026-08-13 11:17:14	0	0	\N	t	2026-08-13 11:12:14	2026-08-13 11:12:25
17	+6998888899	054330	2026-08-13 11:18:53	0	0	\N	f	2026-08-13 11:13:53	2026-08-13 11:13:53
18	+208555688888	911105	2026-08-13 11:22:52	0	0	\N	t	2026-08-13 11:17:52	2026-08-13 11:18:03
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: payment_receipts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payment_receipts (id, order_id, payment_method, file_path, original_name, status, rejection_reason, uploaded_by, reviewed_by, uploaded_at, reviewed_at, created_at, updated_at) FROM stdin;
1	1	manual_wallet	payment-receipts/CS5W4tdfc3V844et881EJdY0gpGKbZfOnbN5V9va.png	Screenshot (2).png	pending	\N	1	\N	2026-08-08 04:05:27	\N	2026-08-08 04:05:27	2026-08-08 04:05:27
2	1	manual_wallet	payment-receipts/eo5R4QKHI4TWNOQHlWaL2RlVxjDHfTG4TDtmVoQW.png	Screenshot (3).png	pending	\N	1	\N	2026-08-08 04:05:49	\N	2026-08-08 04:05:49	2026-08-08 04:05:49
3	2	manual_wallet	payment-receipts/TAJcbPvOVnDipgaZphL5LjhWY1fzpo8K3gnArkWc.png	Screenshot (3).png	pending	\N	1	\N	2026-08-08 04:06:33	\N	2026-08-08 04:06:33	2026-08-08 04:06:33
4	2	manual_wallet	payment-receipts/Mew1KOXtbSOC4jrXdkryJIqcs0Ti0yRdlej4UERM.png	Screenshot (3).png	pending	\N	1	\N	2026-08-08 04:09:06	\N	2026-08-08 04:09:06	2026-08-08 04:09:06
5	2	manual_wallet	payment-receipts/4bXHcmWDC6LAtBi2aOiNvd6HBZjPIrEUt24YERI6.png	Screenshot (6).png	rejected	Blurred	1	1	2026-08-08 04:09:19	2026-08-08 04:12:43	2026-08-08 04:09:19	2026-08-08 04:12:43
6	2	manual_wallet	payment-receipts/HUbhyU0YazbFgvWf4EY8GuAaggWwHTvEYIArJTDG.png	Screenshot (7).png	confirmed	\N	1	1	2026-08-08 04:14:38	2026-08-08 04:14:49	2026-08-08 04:14:38	2026-08-08 04:14:49
7	4	manual_wallet	payment-receipts/twS6B1DlW3IZc5BTkMtRWKDMrH6rb1uV87bSrUGq.png	Screenshot (2).png	confirmed	\N	3	1	2026-08-09 14:26:49	2026-08-09 14:29:07	2026-08-09 14:26:49	2026-08-09 14:29:07
8	5	manual_wallet	payment-receipts/fLb892cnsOb316DxUId7cNACVA3SMYHjtHighatd.png	Screenshot (7).png	confirmed	\N	3	1	2026-08-09 14:42:11	2026-08-09 14:42:39	2026-08-09 14:42:11	2026-08-09 14:42:39
9	6	manual_wallet	payment-receipts/daKsZCitLJCWmHy5IsHEUn4VN3lzIRPQMcWnUPEg.png	Screenshot_20260809-060354.png	confirmed	\N	5	1	2026-08-09 15:04:45	2026-08-09 15:05:24	2026-08-09 15:04:45	2026-08-09 15:05:24
10	7	manual_wallet	payment-receipts/T947R4miyFd76ObAJHJJfelr6BJLdrwgHMaIX6Mh.png	Screenshot_20260809-060354.png	confirmed	\N	5	3	2026-08-09 15:07:30	2026-08-09 15:07:32	2026-08-09 15:07:30	2026-08-09 15:07:32
11	8	manual_instapay	payment-receipts/gK07XR4w2DItYrO42ulj66D67V0RGSOToe2TdUCV.png	Screenshot_20260808-194630.png	pending	\N	6	\N	2026-08-10 04:34:55	\N	2026-08-10 04:34:55	2026-08-10 04:34:55
12	10	manual_wallet	payment-receipts/Nv5KlnZaAg5ZABGn5IjqygrCAXCSqm1hdhBX0Lvw.png	download.png	pending	\N	1	\N	2026-08-10 12:23:40	\N	2026-08-10 12:23:40	2026-08-10 12:23:40
13	11	manual_wallet	payment-receipts/cAkI4vcTdDGvBFOFdQMfP2JUbKSRbEcDseB1vyjo.png	Screenshot (6).png	pending	\N	1	\N	2026-08-11 09:49:16	\N	2026-08-11 09:49:16	2026-08-11 09:49:16
14	13	manual_instapay	payment-receipts/dvICGcMQsz06WPnOCuXpukECXtDF1dY83HronUqV.jpg	1000321976.jpg	confirmed	\N	10	1	2026-08-12 11:24:31	2026-08-12 11:25:59	2026-08-12 11:24:31	2026-08-12 11:25:59
15	15	manual_wallet	payment-receipts/hiiCCGHQm0LP3bqTXQJRFnr1pfZBFwvaVdaxmjqu.png	thumbnail_Esigns_Additional_Banner_Image_Gallery.png	pending	\N	75	\N	2026-08-12 21:49:25	\N	2026-08-12 21:49:25	2026-08-12 21:49:25
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: product_category; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_category (product_id, category_id) FROM stdin;
1	23
2	23
3	23
4	23
5	29
6	29
7	29
8	19
9	19
10	19
11	25
12	25
13	28
14	28
15	28
16	21
17	21
18	21
19	30
20	26
21	26
22	18
\.


--
-- Data for Name: product_reviews; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_reviews (id, product_id, user_id, rating, title, body, created_at, updated_at, approved, is_verified_purchase, helpful_count) FROM stdin;
1	22	3	5	\N	Great one	2026-08-09 14:49:23	2026-08-10 12:30:10	t	t	0
\.


--
-- Data for Name: product_variations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_variations (id, product_id, main_variation, attributes, price, regular_price, sale_price, stock_quantity, images, created_at, updated_at, stock_status, status) FROM stdin;
2	1	f	{"Color":"Tan"}	1850.00	1850.00	\N	18	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
3	1	f	{"Color":"Brown"}	1850.00	1850.00	\N	12	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
5	2	f	{"Color":"Black"}	637.50	750.00	637.50	35	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
6	2	f	{"Color":"Red"}	637.50	750.00	637.50	20	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
7	3	t	{"Color":"Black"}	2200.00	2200.00	\N	15	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish
8	3	f	{"Color":"Cream"}	2200.00	2200.00	\N	10	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish
9	4	t	{"Color":"Navy"}	760.00	950.00	760.00	60	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish
10	4	f	{"Color":"Khaki"}	760.00	950.00	760.00	45	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish
11	4	f	{"Color":"Black"}	760.00	950.00	760.00	55	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish
12	5	t	{"Size":"S"}	699.00	699.00	\N	30	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish
13	5	f	{"Size":"M"}	699.00	699.00	\N	45	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish
14	5	f	{"Size":"L"}	699.00	699.00	\N	35	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish
15	5	f	{"Size":"XL"}	699.00	699.00	\N	20	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish
16	6	t	{"Size":"XS"}	674.10	749.00	674.10	25	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish
17	6	f	{"Size":"S"}	674.10	749.00	674.10	40	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish
18	6	f	{"Size":"M"}	674.10	749.00	674.10	50	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish
19	6	f	{"Size":"L"}	674.10	749.00	674.10	30	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish
20	7	t	{"Size":"S"}	615.00	820.00	615.00	22	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish
21	7	f	{"Size":"M"}	615.00	820.00	615.00	38	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish
22	7	f	{"Size":"L"}	615.00	820.00	615.00	28	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish
23	8	t	{"Size":"S"}	450.00	450.00	\N	35	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
24	8	f	{"Size":"M"}	450.00	450.00	\N	50	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
25	8	f	{"Size":"L"}	450.00	450.00	\N	40	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
26	8	f	{"Size":"XL"}	450.00	450.00	\N	25	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
27	8	f	{"Size":"XXL"}	450.00	450.00	\N	15	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
28	9	t	{"Color":"White","Size":"M"}	520.00	520.00	\N	30	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish
29	9	f	{"Color":"Blue","Size":"M"}	520.00	520.00	\N	28	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish
30	9	f	{"Color":"White","Size":"L"}	520.00	520.00	\N	25	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish
31	9	f	{"Color":"Blue","Size":"L"}	520.00	520.00	\N	22	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish
32	10	t	{"Color":"Navy","Size":"S"}	323.00	380.00	323.00	40	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish
33	10	f	{"Color":"Navy","Size":"M"}	323.00	380.00	323.00	55	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish
34	10	f	{"Color":"Red","Size":"M"}	323.00	380.00	323.00	35	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish
35	10	f	{"Color":"White","Size":"L"}	323.00	380.00	323.00	30	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish
37	11	f	{"Color":"Black","Size":"M"}	1250.00	1250.00	\N	22	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish
38	11	f	{"Color":"Black","Size":"L"}	1250.00	1250.00	\N	15	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish
39	11	f	{"Color":"Camel","Size":"M"}	1250.00	1250.00	\N	12	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish
41	12	f	{"Color":"Navy","Size":"L"}	1512.00	1890.00	1512.00	12	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish
42	12	f	{"Color":"Grey","Size":"M"}	1512.00	1890.00	1512.00	8	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish
43	12	f	{"Color":"Grey","Size":"L"}	1512.00	1890.00	1512.00	9	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish
44	13	t	{"Color":"White","Size":"40"}	1150.00	1150.00	\N	20	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish
45	13	f	{"Color":"White","Size":"41"}	1150.00	1150.00	\N	30	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish
46	13	f	{"Color":"White","Size":"42"}	1150.00	1150.00	\N	28	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish
47	13	f	{"Color":"Black","Size":"41"}	1150.00	1150.00	\N	25	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish
48	13	f	{"Color":"Black","Size":"42"}	1150.00	1150.00	\N	22	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish
49	14	t	{"Color":"Black","Size":"37"}	1332.00	1480.00	1332.00	15	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish
50	14	f	{"Color":"Black","Size":"38"}	1332.00	1480.00	1332.00	20	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish
51	14	f	{"Color":"Black","Size":"39"}	1332.00	1480.00	1332.00	18	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish
52	14	f	{"Color":"Brown","Size":"38"}	1332.00	1480.00	1332.00	12	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish
53	15	t	{"Color":"Black","Size":"41"}	2100.00	2100.00	\N	10	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish
54	15	f	{"Color":"Black","Size":"42"}	2100.00	2100.00	\N	12	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish
55	15	f	{"Color":"Brown","Size":"41"}	2100.00	2100.00	\N	8	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish
56	15	f	{"Color":"Brown","Size":"42"}	2100.00	2100.00	\N	10	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish
57	16	t	{"Color":"White","Size":"S"}	280.00	280.00	\N	60	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish
58	16	f	{"Color":"White","Size":"M"}	280.00	280.00	\N	80	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish
59	16	f	{"Color":"Black","Size":"M"}	280.00	280.00	\N	75	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish
60	16	f	{"Color":"Black","Size":"L"}	280.00	280.00	\N	65	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish
61	16	f	{"Color":"Grey","Size":"L"}	280.00	280.00	\N	50	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish
62	17	t	{"Color":"Sand","Size":"S"}	455.00	650.00	455.00	30	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish
63	17	f	{"Color":"Sand","Size":"M"}	455.00	650.00	455.00	45	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish
4	2	t	{"Color":"Beige"}	637.50	750.00	637.50	39	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
36	11	t	{"Color":"Black","Size":"S"}	1250.00	1250.00	\N	17	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish
40	12	t	{"Color":"Navy","Size":"M"}	1512.00	1890.00	1512.00	9	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish
64	17	f	{"Color":"Black","Size":"M"}	455.00	650.00	455.00	50	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish
65	17	f	{"Color":"Black","Size":"L"}	455.00	650.00	455.00	40	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish
66	17	f	{"Color":"Grey","Size":"XL"}	455.00	650.00	455.00	25	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish
67	18	t	{"Color":"Navy\\/White","Size":"S"}	320.00	320.00	\N	35	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish
68	18	f	{"Color":"Navy\\/White","Size":"M"}	320.00	320.00	\N	50	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish
69	18	f	{"Color":"Red\\/White","Size":"M"}	320.00	320.00	\N	40	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish
70	18	f	{"Color":"Red\\/White","Size":"L"}	320.00	320.00	\N	30	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish
71	19	t	{"Color":"Khaki","Size":"S"}	550.00	550.00	\N	30	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish
72	19	f	{"Color":"Khaki","Size":"M"}	550.00	550.00	\N	45	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish
73	19	f	{"Color":"Khaki","Size":"L"}	550.00	550.00	\N	35	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish
74	19	f	{"Color":"Navy","Size":"M"}	550.00	550.00	\N	40	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish
75	19	f	{"Color":"Navy","Size":"L"}	550.00	550.00	\N	30	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish
77	20	f	{"Color":"Multi","Size":"S"}	890.00	890.00	\N	35	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish
78	20	f	{"Color":"Multi","Size":"M"}	890.00	890.00	\N	40	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish
79	20	f	{"Color":"Multi","Size":"L"}	890.00	890.00	\N	25	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish
80	21	t	{"Color":"Black","Size":"XS"}	880.00	1100.00	880.00	15	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
81	21	f	{"Color":"Black","Size":"S"}	880.00	1100.00	880.00	22	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
82	21	f	{"Color":"Black","Size":"M"}	880.00	1100.00	880.00	28	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
84	21	f	{"Color":"Nude","Size":"M"}	880.00	1100.00	880.00	20	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
85	22	t	{}	2526.00	2526.00	2526.00	142	[]	2026-08-09 14:39:05	2026-08-09 14:39:05	instock	publish
76	20	t	{"Color":"Multi","Size":"XS"}	890.00	890.00	\N	19	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish
1	1	t	{"Color":"Black"}	1850.00	1850.00	\N	24	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
83	21	f	{"Color":"Nude","Size":"S"}	880.00	1100.00	880.00	17	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
\.


--
-- Data for Name: products_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products_data (id, name, slug, search_text, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount_percentage, short_description, sku, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, related_ids, meta_data, stock_status, has_options, has_variations, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, whatsapp, button_mode) FROM stdin;
1	Classic Leather Tote Bag	classic-leather-tote-bag	classic leather tote bag premium full-grain leather tote perfect for everyday use. spacious interior with magnetic closure.		2026-02-12 17:10:14		2026-02-12 17:10:14		variable	publish	f		Premium full-grain leather tote perfect for everyday use. Spacious interior with magnetic closure.	0	Premium full-grain leather tote perfect for everyday use. Spacious interior with magnetic closure.	\N	\N	\N	\N	\N	f	t	142	f	f	[]	0	0	\N		t	55		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":23}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1584917865442-de89df76afd3?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	12	[{"name": "حقيبة توت جلدية كلاسيكية", "locale": "ar", "description": "حقيبة توت من جلد كامل الحبوب الفاخر، مثالية للاستخدام اليومي. تتميز بداخل واسع وإغلاق مغناطيسي."}]	approved			both
2	Mini Crossbody Bag	mini-crossbody-bag	mini crossbody bag compact crossbody bag with adjustable strap. fits your phone, keys, and essentials.		2025-12-18 17:10:14		2025-12-18 17:10:14		variable	publish	f		Compact crossbody bag with adjustable strap. Fits your phone, keys, and essentials.	15	Compact crossbody bag with adjustable strap. Fits your phone, keys, and essentials.	\N	\N	\N	\N	\N	t	t	98	f	f	[]	0	0	\N		t	95		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":23}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1548036328-c9fa89d128fa?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	12	[{"name": "حقيبة كروس بودي صغيرة", "locale": "ar", "description": "حقيبة كروس بودي مدمجة بحزام قابل للتعديل. تتسع لهاتفك ومفاتيحك والأساسيات."}]	approved			both
3	Quilted Chain Shoulder Bag	quilted-chain-shoulder-bag	quilted chain shoulder bag elegant quilted bag with gold-tone chain strap. a timeless piece for any outfit.		2025-08-16 17:10:14		2025-08-16 17:10:14		variable	publish	f		Elegant quilted bag with gold-tone chain strap. A timeless piece for any outfit.	0	Elegant quilted bag with gold-tone chain strap. A timeless piece for any outfit.	\N	\N	\N	\N	\N	f	t	67	f	f	[]	0	0	\N		t	25		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":23}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1591561954555-607968c989ab?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	12	[{"name": "حقيبة كتف مبطنة بسلسلة", "locale": "ar", "description": "حقيبة مبطنة أنيقة بحزام سلسلة بلون ذهبي. قطعة خالدة تناسب أي إطلالة."}]	approved			both
10	Polo Shirt	polo-shirt	polo shirt classic piqué polo shirt with ribbed collar and cuffs. available in vibrant colors.		2024-06-03 17:10:14		2024-06-03 17:10:14		variable	publish	f		Classic piqué polo shirt with ribbed collar and cuffs. Available in vibrant colors.	15	Classic piqué polo shirt with ribbed collar and cuffs. Available in vibrant colors.	\N	\N	\N	\N	\N	t	t	201	f	f	[]	0	0	\N		t	160		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":19}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1586363104862-3a5e2ab60d99?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	10	[{"name": "قميص بولو", "locale": "ar", "description": "قميص بولو بيكيه كلاسيكي بياقة وأساور مضلعة. متوفر بألوان زاهية."}]	approved			both
4	Canvas Backpack	canvas-backpack	canvas backpack durable canvas backpack with laptop sleeve and multiple pockets. perfect for work or travel.		2025-07-21 17:10:14		2025-07-21 17:10:14		variable	publish	f		Durable canvas backpack with laptop sleeve and multiple pockets. Perfect for work or travel.	20	Durable canvas backpack with laptop sleeve and multiple pockets. Perfect for work or travel.	\N	\N	\N	\N	\N	t	t	210	f	f	[]	0	0	\N		t	160		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":23}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1553062407-98eeb64c6a62?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	12	[{"name": "حقيبة ظهر من الكانفاس", "locale": "ar", "description": "حقيبة ظهر متينة من الكانفاس مع جراب للابتوب والعديد من الجيوب. مثالية للعمل أو السفر."}]	approved			both
5	Slim Fit Blue Denim Jeans	slim-fit-blue-denim-jeans	slim fit blue denim jeans classic slim-fit jeans in mid-wash blue denim. stretch fabric for all-day comfort.		2025-01-29 17:10:14		2025-01-29 17:10:14		variable	publish	f		Classic slim-fit jeans in mid-wash blue denim. Stretch fabric for all-day comfort.	0	Classic slim-fit jeans in mid-wash blue denim. Stretch fabric for all-day comfort.	\N	\N	\N	\N	\N	f	t	325	f	f	[]	0	0	\N		t	130		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":29}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1542272454315-4c01d7abdf4a?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	17	[{"name": "جينز دنيم أزرق بقصة ضيقة", "locale": "ar", "description": "جينز بقصة ضيقة كلاسيكية بلون أزرق متوسط الغسيل. قماش مرن لراحة طوال اليوم."}]	approved			both
6	Black Skinny Jeans	black-skinny-jeans	black skinny jeans sleek black skinny jeans with a high-rise waist. a wardrobe essential for every season.		2024-12-05 17:10:14		2024-12-05 17:10:14		variable	publish	f		Sleek black skinny jeans with a high-rise waist. A wardrobe essential for every season.	10	Sleek black skinny jeans with a high-rise waist. A wardrobe essential for every season.	\N	\N	\N	\N	\N	t	t	280	f	f	[]	0	0	\N		t	145		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":29}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1541099649105-f69ad21f3246?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	17	[{"name": "جينز سكيني أسود", "locale": "ar", "description": "جينز سكيني أسود بخصر مرتفع. قطعة أساسية لخزانة الملابس في كل موسم."}]	approved			both
7	Distressed Boyfriend Jeans	distressed-boyfriend-jeans	distressed boyfriend jeans relaxed boyfriend fit with authentic distressed detailing. effortlessly cool streetwear look.		2024-11-17 17:10:14		2024-11-17 17:10:14		variable	publish	f		Relaxed boyfriend fit with authentic distressed detailing. Effortlessly cool streetwear look.	25	Relaxed boyfriend fit with authentic distressed detailing. Effortlessly cool streetwear look.	\N	\N	\N	\N	\N	t	t	189	f	f	[]	0	0	\N		t	88		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":29}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1580651315530-69c8e0026377?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	17	[{"name": "جينز بوي فريند ممزق", "locale": "ar", "description": "قصة بوي فريند مريحة بتفاصيل ممزقة أصلية. مظهر شارع عصري وأنيق بلا مجهود."}]	approved			both
8	Classic White Oxford Shirt	classic-white-oxford-shirt	classic white oxford shirt crisp white oxford shirt crafted from 100% cotton. timeless style suitable for work or weekend.		2024-09-12 17:10:14		2024-09-12 17:10:14		variable	publish	f		Crisp white Oxford shirt crafted from 100% cotton. Timeless style suitable for work or weekend.	0	Crisp white Oxford shirt crafted from 100% cotton. Timeless style suitable for work or weekend.	\N	\N	\N	\N	\N	f	t	175	f	f	[]	0	0	\N		t	165		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":19}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1598033129183-c4f50c736f10?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	10	[{"name": "قميص أوكسفورد أبيض كلاسيكي", "locale": "ar", "description": "قميص أوكسفورد أبيض ناصع مصنوع من 100% قطن. طراز كلاسيكي مناسب للعمل أو لعطلة نهاية الأسبوع."}]	approved			both
9	Linen Casual Shirt	linen-casual-shirt	linen casual shirt breathable linen shirt perfect for warm weather. relaxed fit with a button-down collar.		2024-06-06 17:10:14		2024-06-06 17:10:14		variable	publish	f		Breathable linen shirt perfect for warm weather. Relaxed fit with a button-down collar.	0	Breathable linen shirt perfect for warm weather. Relaxed fit with a button-down collar.	\N	\N	\N	\N	\N	f	t	134	f	f	[]	0	0	\N		t	105		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":19}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1607962837359-5e7e89f86776?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	10	[{"name": "قميص كتان كاجوال", "locale": "ar", "description": "قميص من الكتان قابل للتنفس مثالي للطقس الحار. قصة مريحة مع ياقة بأزرار."}]	approved			both
11	Women's Tailored Blazer	womens-tailored-blazer	women's tailored blazer sharp tailored blazer with a modern slim fit. perfect for the office or a night out.		2024-02-14 17:10:14		2024-02-14 17:10:14		variable	publish	f		Sharp tailored blazer with a modern slim fit. Perfect for the office or a night out.	0	Sharp tailored blazer with a modern slim fit. Perfect for the office or a night out.	\N	\N	\N	\N	\N	f	t	88	f	f	[]	0	0	\N		t	67		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":25}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1594938298603-c8148c4dae35?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	16	[{"name": "بليزر نسائي مفصل بقصة ضيقة عصرية", "locale": "ar", "description": "بليزر مُفصّل بقصة عصرية ضيقة. مثالي للمكتب أو لأمسية خارجية."}]	approved			both
12	Men's Double-Breasted Blazer	mens-double-breasted-blazer	men's double-breasted blazer sophisticated double-breasted blazer in premium wool blend. a statement piece for any wardrobe.		2023-12-10 17:10:14		2023-12-10 17:10:14		variable	publish	f		Sophisticated double-breasted blazer in premium wool blend. A statement piece for any wardrobe.	20	Sophisticated double-breasted blazer in premium wool blend. A statement piece for any wardrobe.	\N	\N	\N	\N	\N	t	t	55	f	f	[]	0	0	\N		t	39		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":25}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1507003211169-0a1dd7228f2d?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	16	[{"name": "بليزر رجالي مزدوج الصدر", "locale": "ar", "description": "بليزر رجالي أنيق مزدوج الصدر من مزيج صوف فاخر. قطعة مميزة لأي خزانة ملابس."}]	approved			both
13	Men's Classic Sneakers	mens-classic-sneakers	men's classic sneakers iconic low-top leather sneakers with cushioned sole. goes with anything, from jeans to chinos.		2023-11-07 17:10:14		2023-11-07 17:10:14		variable	publish	f		Iconic low-top leather sneakers with cushioned sole. Goes with anything, from jeans to chinos.	0	Iconic low-top leather sneakers with cushioned sole. Goes with anything, from jeans to chinos.	\N	\N	\N	\N	\N	f	t	412	f	f	[]	0	0	\N		t	125		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":28}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1542291026-7eec264c27ff?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	13	[{"name": "سنيكرز كلاسيكي رجالي", "locale": "ar", "description": "سنيكرز جلدية منخفضة الكاحل أيقونية بنعل مبطّن. ينسق مع أي شيء، من الجينز إلى الشينو."}]	approved			both
14	Women's Ankle Boots	womens-ankle-boots	women's ankle boots sleek leather ankle boots with a block heel. versatile enough for day or night wear.		2023-11-06 17:10:14		2023-11-06 17:10:14		variable	publish	f		Sleek leather ankle boots with a block heel. Versatile enough for day or night wear.	10	Sleek leather ankle boots with a block heel. Versatile enough for day or night wear.	\N	\N	\N	\N	\N	t	t	167	f	f	[]	0	0	\N		t	65		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":28}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1543163521-1bf539c55dd2?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	13	[{"name": "بوت كاحل نسائي", "locale": "ar", "description": "بوت كاحل جلدي أنيق بكعب بلوك. متعدد الاستخدامات لارتداء نهاراً أو ليلاً."}]	approved			both
15	Formal Oxford Shoes	formal-oxford-shoes	formal oxford shoes hand-crafted leather oxford shoes with goodyear welt construction. built to last a lifetime.		2023-08-07 17:10:14		2023-08-07 17:10:14		variable	publish	f		Hand-crafted leather Oxford shoes with Goodyear welt construction. Built to last a lifetime.	0	Hand-crafted leather Oxford shoes with Goodyear welt construction. Built to last a lifetime.	\N	\N	\N	\N	\N	f	t	93	f	f	[]	0	0	\N		t	40		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":28}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1533867617858-e7b97e060509?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	13	[{"name": "حذاء أوكسفورد رسمي", "locale": "ar", "description": "أحذية أوكسفورد جلدية مصنوعة يدوياً بتطويق جووديار (Goodyear welt). مصممة لتدوم مدى الحياة."}]	approved			both
16	Graphic Print T-Shirt	graphic-print-tshirt	graphic print t-shirt bold graphic tee printed on 100% organic cotton. express your style with attitude.		2023-06-18 17:10:14		2023-06-18 17:10:14		variable	publish	f		Bold graphic tee printed on 100% organic cotton. Express your style with attitude.	0	Bold graphic tee printed on 100% organic cotton. Express your style with attitude.	\N	\N	\N	\N	\N	f	t	398	f	f	[]	0	0	\N		t	330		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":21}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1521572163474-6864f9cf17ab?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	14	[{"name": "تيشيرت بطبعة جرافيك", "locale": "ar", "description": "تيشيرت بطبعة جريئة مطبوع على قطن عضوي 100%. عبّر عن أسلوبك بثقة."}]	approved			both
17	Oversized Hoodie	oversized-hoodie	oversized hoodie super-soft heavyweight fleece hoodie with a relaxed oversized fit. cozy all day long.		2023-03-09 17:10:14		2023-03-09 17:10:14		variable	publish	f		Super-soft heavyweight fleece hoodie with a relaxed oversized fit. Cozy all day long.	30	Super-soft heavyweight fleece hoodie with a relaxed oversized fit. Cozy all day long.	\N	\N	\N	\N	\N	t	t	244	f	f	[]	0	0	\N		t	190		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":21}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1556821840-3a63f15732ce?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	14	[{"name": "هودي واسع من فليس ثقيل", "locale": "ar", "description": "هودي من فليس ثقيل ناعم جداً بقصة واسعة ومريحة. دافئ ومريح طوال اليوم."}]	approved			both
18	Striped Long-Sleeve Tee	striped-long-sleeve-tee	striped long-sleeve tee classic breton stripes on a breathable long-sleeve tee. a french-inspired everyday essential.		2022-10-24 17:10:14		2022-10-24 17:10:14		variable	publish	f		Classic Breton stripes on a breathable long-sleeve tee. A French-inspired everyday essential.	0	Classic Breton stripes on a breathable long-sleeve tee. A French-inspired everyday essential.	\N	\N	\N	\N	\N	f	t	156	f	f	[]	0	0	\N		t	155		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":21}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1581655353564-df123a1eb820?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	14	[{"name": "تيشيرت مخطط بأكمام طويلة", "locale": "ar", "description": "خطوط بريتون الكلاسيكية على تيشيرت بأكمام طويلة قابل للتنفس. قطعة يومية مستوحاة من الأناقة الفرنسية."}]	approved			both
19	Slim-Fit Chino Trousers	slim-fit-chino-trousers	slim-fit chino trousers smart-casual chinos in stretch cotton twill. office-ready yet weekend-worthy.		2022-10-07 17:10:14		2022-10-07 17:10:14		variable	publish	f		Smart-casual chinos in stretch cotton twill. Office-ready yet weekend-worthy.	0	Smart-casual chinos in stretch cotton twill. Office-ready yet weekend-worthy.	\N	\N	\N	\N	\N	f	t	188	f	f	[]	0	0	\N		t	180		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":30}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1552902865-b72c031ac5ea?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	17	[{"name": "بنطال تشينو بقصة ضيقة", "locale": "ar", "description": "تشينو ذكي كاجوال من تويل قطني مرن. جاهز للمكتب وفي عطلة نهاية الأسبوع."}]	approved			both
20	Floral Wrap Dress	floral-wrap-dress	floral wrap dress feminine wrap dress in a vibrant floral print. v-neckline and adjustable tie waist for a flattering fit.		2022-08-23 17:10:14		2022-08-23 17:10:14		variable	publish	f		Feminine wrap dress in a vibrant floral print. V-neckline and adjustable tie waist for a flattering fit.	0	Feminine wrap dress in a vibrant floral print. V-neckline and adjustable tie waist for a flattering fit.	\N	\N	\N	\N	\N	f	t	223	f	f	[]	0	0	\N		t	120		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":26}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1595777457583-95e059d581b8?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	16	[{"name": "فستان ملفوف بنقشة زهور", "locale": "ar", "description": "فستان ملفوف أنثوي بنقشة زهور زاهية. ياقة على شكل V وحزام قابل للتعديل عند الخصر لمقاس ملائم وجذاب."}]	approved			both
21	Midi Slip Dress	midi-slip-dress	midi slip dress satin midi slip dress with thin adjustable straps. effortlessly elegant for any occasion.		2022-05-22 17:10:14		2022-05-22 17:10:14		variable	publish	f		Satin midi slip dress with thin adjustable straps. Effortlessly elegant for any occasion.	20	Satin midi slip dress with thin adjustable straps. Effortlessly elegant for any occasion.	\N	\N	\N	\N	\N	t	t	145	f	f	[]	0	0	\N		t	103		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":26}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1614170153058-7a8e04b58f76?w=600&h=700&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]		f	f		\N	f	[]	f	[]	["en", "ar"]	0		0	2026-05-06 17:10:15	2026-08-13 08:10:56	0	0	physical	16	[{"name": "فستان سليب ميدي", "locale": "ar", "description": "فستان ساتان ميدي بحمالات رفيعة قابلة للتعديل. أنيق بسهولة لأي مناسبة."}]	approved			both
22	Luxe Velvet Jeans — Olive	luxe-velvet-jeans-olive	luxe velvet jeans — olive high-quality linen skirt perfect for everyday wear. comfortable fit with a modern look. features: • premium leather fabric • available in multiple sizes • machine washable • true to size fit care instructions: wash at 30°c, do not tumble dry. high-quality linen skirt perfect for everyday wear. comfortable fit with a modern look. summer trending gift بنطلون جينز أنيق منتج مصنوع من أجود الخامات، مريح وعملي للاستخدام اليومي. المميزات: • خامة ممتازة • مقاسات متعددة • سهل العناية تعليمات العناية: اغسل على 30 درجة مئوية.	luxe-velvet-jeans-olive					physical	publish	f	visible	High-quality linen skirt perfect for everyday wear. Comfortable fit with a modern look.\r\n\r\nFeatures:\r\n• Premium leather fabric\r\n• Available in multiple sizes\r\n• Machine washable\r\n• True to size fit\r\n\r\nCare instructions: Wash at 30°C, do not tumble dry.	0	High-quality linen skirt perfect for everyday wear. Comfortable fit with a modern look.	SKU-BZL2XA	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		t	142		f	f	0	f	[]	t	f		0	t	0	0	[]	[]	0		[]	["summer","trending","gift"]	{"thumbnail":"products\\/thumbnails\\/jWxe2g5AHxyoQJgVxo8FknSBZq8ohIJy3W1G29QP.jpg","other_images":["products\\/other_images\\/nVBVb7y51SbUZuaEfKPlJVsYmzUAbbUdQrGlOQRF.jpg"],"natural_images":["products\\/natural_images\\/ILzxE3ijlarqJIbl6BJGyWZXcgTun45P5ydqzWMh.jpg"]}	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]	["en", "0", "ar"]	0	1	0	2026-08-09 14:39:05	2026-08-13 08:10:56	1	0	physical	3	[{"name": "بنطلون جينز أنيق", "locale": "0", "description": "منتج مصنوع من أجود الخامات، مريح وعملي للاستخدام اليومي.\\r\\n\\r\\nالمميزات:\\r\\n• خامة ممتازة\\r\\n• مقاسات متعددة\\r\\n• سهل العناية\\r\\n\\r\\nتعليمات العناية: اغسل على 30 درجة مئوية."}, {"name": "جينز مخملي فاخر — زيتي", "locale": "ar", "description": "تنورة كتان عالية الجودة مثالية للارتداء اليومي. ملاءمة مريحة بمظهر عصري.\\r\\n\\r\\nالميزات:\\r\\n• قماش جلدي فاخر\\r\\n• متوفر بمقاسات متعددة\\r\\n• قابل للغسل في الغسالة\\r\\n• يتناسب مع المقاس المعتاد\\r\\n\\r\\nتعليمات العناية: اغسل على درجة حرارة 30°C، لا تجفف في المجفف."}]	approved	{"piece":1}	{"whatsapp":{"available":false,"number":null}}	both
\.


--
-- Data for Name: products_data_main; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products_data_main (id, name, slug, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount, short_description, sku, price, regular_price, sale_price, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, price_html, related_ids, meta_data, stock_status, has_options, post_password, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, button_mode) FROM stdin;
\.


--
-- Data for Name: rate_limits; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rate_limits (consumer_key, request_count, last_request_time) FROM stdin;
\.


--
-- Data for Name: refund_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.refund_requests (id, order_id, customer_id, vendor_id, type, reason, description, status, admin_note, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: shops; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.shops (id, user_id, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, status, created_at, updated_at, remember_token) FROM stdin;
\.


--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tags (id, name, slug, description, count, is_visible, _links, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: time_line_configs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.time_line_configs (id, lang_code, config_json) FROM stdin;
\.


--
-- Data for Name: user_notes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_notes (id, user_id, date_created, note, customer_note, created_at, updated_at, date_created_gmt, order_id) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, user_login, username, user_nicename, display_name, first_name, last_name, url, avatar, phone, role, nicename, registered, firstname, lastname, description, capabilities, shipping, registration_method, is_phone_verified, is_blocked, provider, provider_id) FROM stdin;
1	Admin	adminramoui@gmail.com	2026-08-08 03:57:49	$2y$12$IoSnZPF4/2zQ9lVavUstge6X8OUqEWEbP14c0ae4fcf1p64rNCcUO	\N	2026-05-06 17:10:02	2026-08-08 04:06:23	\N	\N	\N	\N	Sara	Ehab	\N	\N	7865876587	["admin"]							{"first_name":"Sara","last_name":"Ehab","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":"adminramoui@gmail.com","phone":"7865876587","latitude":"28.445440824393827","longitude":"30.805906818883177"}	\N	f	f	\N	\N
2	34	\N	\N	$2y$12$zNRdGaamT/iR3aBNTQRBQu39sdiPF7ywdvTx1li4x.rbTK74W0PXy	\N	2026-08-09 14:19:30	2026-08-09 14:19:30	\N	\N	\N	\N	34		\N	\N	+45234534444	["customer"]	34-4444	2026-08-09 14:19:30	34			{"customer":true}	[]	phone_otp	t	f	\N	\N
3	Ramona hgg	\N	\N	$2y$12$fbE.7MBzZ7UhgnRBG/2XMOrU6Sa1s0gG.T3i4oblUsABpbkCTgjOC	\N	2026-08-09 14:24:56	2026-08-09 14:25:25	\N	\N	\N	\N	Ramona	hgg	\N	\N	+3453454555	["customer"]	ramona-hgg-4555	2026-08-09 14:24:56	Ramona	hgg		{"customer":true}	{"first_name":"Ramona","last_name":"hgg","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+3453454555","latitude":"28.44544435121235","longitude":"30.805890948199856"}	phone_otp	t	f	\N	\N
4	jhgjkhg	\N	\N	$2y$12$5og.pwYI8RgIsGqTI.O0e.NdTne.6pyq.xhB6A3L6fQNhwx5KCghu	\N	2026-08-09 15:01:49	2026-08-09 15:01:49	\N	\N	\N	\N	jhgjkhg		\N	\N	+76587657876	["customer"]	jhgjkhg-7876	2026-08-09 15:01:49	jhgjkhg			{"customer":true}	[]	phone_otp	t	f	\N	\N
5	Ramez Malak	\N	\N	$2y$12$1Uu.SRqoEfjzBD7w4qSVXO4pljtc5cwbqKLX9pgmW4sHvKJXkrgfm	\N	2026-08-09 15:04:00	2026-08-09 15:04:29	\N	\N	\N	\N	Ramez	Malak	\N	\N	+201002722375	["customer"]	ramez-malak-2375	2026-08-09 15:04:00	Ramez	Malak		{"customer":true}	{"first_name":"Ramez","last_name":"Malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+201002722375","latitude":"28.4457762","longitude":"30.804594"}	phone_otp	t	f	\N	\N
6	Ramo	\N	\N	$2y$12$lC.ojYc6/k6Zs2ch/FUhU./fovlKVD1yVJOls7uTBmnKvRbA1uDXG	\N	2026-08-10 04:33:30	2026-08-10 04:34:02	\N	\N	\N	\N	Ramo	Ramez	\N	\N	+200196464666	["customer"]	ramo-4666	2026-08-10 04:33:30	Ramo			{"customer":true}	{"first_name":"Ramo","last_name":"Ramez","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+200196464666","latitude":"28.4457836","longitude":"30.8046024"}	phone_otp	t	f	\N	\N
7	Ramez Malak	\N	\N	$2y$12$0neRnrwZtGMtDuSZqEsK.uPtY6TdEMZSR/YID5z8ORuwR.jRG0R5.	\N	2026-08-10 04:36:54	2026-08-10 04:37:19	\N	\N	\N	\N	Ramez	Malak	\N	\N	+200885255566	["customer"]	ramez-malak-5566	2026-08-10 04:36:54	Ramez	Malak		{"customer":true}	{"first_name":"Ramez","last_name":"Malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+200885255566","latitude":"28.4457688","longitude":"30.8046002"}	phone_otp	t	f	\N	\N
8	Ramez malak	\N	\N	$2y$12$64f7ge2BDuG376T3ryCz/u7bwQXqwPhVlgr9EMubo3uz00wVH2xbi	\N	2026-08-12 10:00:58	2026-08-12 10:11:02	\N	\N	\N	\N	Ramez	malak	\N	\N	+34523452444	["customer"]	ramez-malak-2444	2026-08-12 10:00:58	Ramez	malak		{"customer":true}	{"first_name":"Ramez","last_name":"malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+34523452444","latitude":"28.445439501836887","longitude":"30.80590917009552"}	phone_otp	t	f	\N	\N
9	Ramez Malak	\N	\N	$2y$12$noKU4YpmNmm1prOxKPO6vedzBNtTW4on75yarfpHnB6yhIhwU88zG	\N	2026-08-12 11:18:28	2026-08-12 11:18:28	\N	\N	\N	\N	Ramez	Malak	\N	\N	+205888558888	["customer"]	ramez-malak-8888	2026-08-12 11:18:28	Ramez	Malak		{"customer":true}	[]	phone_otp	t	f	\N	\N
10	Kkkhh	gggf@gmail.com	\N	$2y$12$MXs.q4sF4PhLWh99ZYXPkObz4MDSjpLuziXjgdE1k0KVcTdxj.pDW	\N	2026-08-12 11:21:09	2026-08-12 11:24:08	\N	\N	\N	\N	Kkkhh	Hgfgh	\N	\N	+20123654525	["customer"]	kkkhh-4525	2026-08-12 11:21:09	Kkkhh			{"customer":true}	{"first_name":"Kkkhh","last_name":"Hgfgh","address":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","address_note":null,"city":"\\u0643\\u0641\\u0648\\u0631 \\u0627\\u0644\\u0635\\u0648\\u0644\\u064a\\u0629","state":"Aswan","email":"gggf@gmail.com","phone":"+20123654525","latitude":"28.445801","longitude":"30.804578"}	phone_otp	t	f	\N	\N
131	Ramez malak	\N	\N	$2y$12$sh0kPFb9B0Y/XjkStFMQ7u5r.OlgIXKcHTWzVKgyhFtkDeYDoPDem	\N	2026-08-13 11:12:37	2026-08-13 11:13:13	\N	\N	\N	\N	Ramez	malak	\N	\N	+200000086666	["customer"]	ramez-malak-6666	2026-08-13 11:12:37	Ramez	malak		{"customer":true}	{"first_name":"Ramez","last_name":"malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+200000086666","latitude":"28.445777","longitude":"30.8046012"}	phone_otp	t	f	\N	\N
75	Ramez malak	\N	\N	$2y$12$CqTT2v2KWDKR5.N7Z4V1Xumiu.EaIe/niSier.WILJR4QS85ZTRYu	\N	2026-08-12 21:46:25	2026-08-12 21:49:10	\N	\N	\N	\N	Ramez	malak	\N	\N	+78608769876	["customer"]	ramez-malak-9876	2026-08-12 21:46:25	Ramez	malak		{"customer":true}	{"first_name":"Ramez","last_name":"malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":null,"phone":"+78608769876","latitude":"28.445441886428167","longitude":"30.805899818569"}	phone_otp	t	f	\N	\N
78	Checkout QA	qa-checkout-test@example.invalid	\N	$2y$12$u2tQzfxg9/flsYwKKMKxGuI9T.06T3QKwPK5vz7MJ0X06CGAuShTW	\N	2026-08-13 06:21:10	2026-08-13 06:21:10	\N	\N	\N	\N	Checkout	QA	\N	\N	+201123456789	["customer"]	checkout-qa-6789	2026-08-13 06:21:10	Checkout	QA		{"customer":true}	[]	phone_otp	t	f	\N	\N
136	Ramez malak	\N	\N	$2y$12$IvYsBQBAx3RyLm0rxX.oiOELv470cXQAfKRhjeecFpW.d0.DRCwOG	\N	2026-08-13 11:18:12	2026-08-13 11:18:12	\N	\N	\N	\N	Ramez	malak	\N	\N	+208555688888	["customer"]	ramez-malak-8888	2026-08-13 11:18:12	Ramez	malak		{"customer":true}	[]	phone_otp	t	f	\N	\N
\.


--
-- Data for Name: vendor_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vendor_users (id, profile_image, first_name, last_name, phone, email, password, email_verified_at, remember_token, created_at, updated_at, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, bottom_banner, status, rating, rating_count, temporary_close, vacation_end_date, vacation_start_date, vacation_status, offer_banner, product_count, orders_count, minimum_order_amount, free_delivery_over_amount, free_delivery_status, sales_commission_percentage, auth_token, holder_name, account_no, bank_name, branch, free_delivery_features_status, free_delivery_responsibility, minimum_order_amount_by_seller) FROM stdin;
3	\N	Cairo	Fashion	01000000000	cairo.fashion@ramostore.com	$2y$12$P0uaQxRMKLQ6BdgyT6W.RezeI18HgWfFfaDGtouWzvtwJapdLj6Fu	\N	\N	2026-05-06 17:10:50	2026-08-09 14:51:01	Cairo Fashion Hub	Cairo, Egypt	stores/logo/hQtoaO74Fy0PBL6nPx27rH3W4CtpArKsnGrLKI34.jpg	stores/banner/csTJSSNvlkBaoEap6P4XPfncFmbd89Z6jEZwGfo3.jpg	\N		approved	0	0	0	empty	empty	0	empty	\N	\N	0	0	0	\N	token123	Cairo Fashion	\N	National Bank	Cairo Branch	\N	\N	\N
\.


--
-- Data for Name: version_config; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.version_config (id, supported_ver_from, supported_ver_to) FROM stdin;
1	1.0.0	4.0.0
\.


--
-- Data for Name: wishlists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wishlists (id, user_id, product_id, created_at) FROM stdin;
1	1	2	2026-05-06 17:43:14
32	9	8	2026-08-12 12:27:10
33	9	19	2026-08-12 12:27:10
34	9	21	2026-08-12 12:27:10
35	9	4	2026-08-12 12:27:10
36	9	11	2026-08-12 12:27:10
\.


--
-- Name: api_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.api_keys_id_seq', 1, false);


--
-- Name: app_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.app_config_id_seq', 1, false);


--
-- Name: app_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.app_configs_id_seq', 6, true);


--
-- Name: attributes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attributes_id_seq', 1, false);


--
-- Name: blogposts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blogposts_id_seq', 1, false);


--
-- Name: brands_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.brands_id_seq', 1, false);


--
-- Name: cart_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cart_items_id_seq', 152, true);


--
-- Name: categories2_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories2_id_seq', 1, false);


--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.category_brand_requests_id_seq', 1, false);


--
-- Name: countries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.countries_id_seq', 1, false);


--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.coupon_user_limits_id_seq', 1, false);


--
-- Name: coupons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.coupons_id_seq', 3, true);


--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.device_access_tokens_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: getposttest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.getposttest_id_seq', 1, false);


--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.idempotency_keys_id_seq', 1, false);


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ramo_app
--

SELECT pg_catalog.setval('public.image_gallery_images_id_seq', 74, true);


--
-- Name: koto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.koto_id_seq', 1, false);


--
-- Name: link_access_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.link_access_logs_id_seq', 1, false);


--
-- Name: links_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.links_id_seq', 1, false);


--
-- Name: links_json_res_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.links_json_res_id_seq', 1, false);


--
-- Name: links_logs_two_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.links_logs_two_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 21, true);


--
-- Name: order_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_messages_id_seq', 3, true);


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_sub_orders_id_seq', 17, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.orders_id_seq', 16, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 18, true);


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payment_receipts_id_seq', 15, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: product_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_reviews_id_seq', 1, true);


--
-- Name: product_variations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_variations_id_seq', 85, true);


--
-- Name: products_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_data_id_seq', 22, true);


--
-- Name: products_data_main_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_data_main_id_seq', 1, false);


--
-- Name: refund_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.refund_requests_id_seq', 1, false);


--
-- Name: shops_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.shops_id_seq', 1, false);


--
-- Name: tags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tags_id_seq', 1, false);


--
-- Name: time_line_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.time_line_configs_id_seq', 1, false);


--
-- Name: user_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_notes_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 140, true);


--
-- Name: vendor_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vendor_users_id_seq', 3, true);


--
-- Name: version_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.version_config_id_seq', 1, false);


--
-- Name: wishlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wishlists_id_seq', 37, true);


--
-- Name: api_keys api_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_keys
    ADD CONSTRAINT api_keys_pkey PRIMARY KEY (id);


--
-- Name: app_config app_config_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_config
    ADD CONSTRAINT app_config_pkey PRIMARY KEY (id);


--
-- Name: app_configs app_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_configs
    ADD CONSTRAINT app_configs_pkey PRIMARY KEY (id);


--
-- Name: attributes attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes
    ADD CONSTRAINT attributes_pkey PRIMARY KEY (id);


--
-- Name: blogposts blogposts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blogposts
    ADD CONSTRAINT blogposts_pkey PRIMARY KEY (id);


--
-- Name: brands brands_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_pkey PRIMARY KEY (id);


--
-- Name: cart_items cart_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cart_items
    ADD CONSTRAINT cart_items_pkey PRIMARY KEY (id);


--
-- Name: categories2 categories2_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories2
    ADD CONSTRAINT categories2_pkey PRIMARY KEY (id);


--
-- Name: category_brand_requests category_brand_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_brand_requests
    ADD CONSTRAINT category_brand_requests_pkey PRIMARY KEY (id);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- Name: coupon_user_limits coupon_user_limits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.coupon_user_limits
    ADD CONSTRAINT coupon_user_limits_pkey PRIMARY KEY (id);


--
-- Name: coupons coupons_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_code_unique UNIQUE (code);


--
-- Name: coupons coupons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_pkey PRIMARY KEY (id);


--
-- Name: device_access_tokens device_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.device_access_tokens
    ADD CONSTRAINT device_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: getposttest getposttest_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.getposttest
    ADD CONSTRAINT getposttest_pkey PRIMARY KEY (id);


--
-- Name: idempotency_keys idempotency_keys_key_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idempotency_keys
    ADD CONSTRAINT idempotency_keys_key_user_id_unique UNIQUE (key, user_id);


--
-- Name: idempotency_keys idempotency_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idempotency_keys
    ADD CONSTRAINT idempotency_keys_pkey PRIMARY KEY (id);


--
-- Name: image_gallery_images image_gallery_images_path_unique; Type: CONSTRAINT; Schema: public; Owner: ramo_app
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_path_unique UNIQUE (path);


--
-- Name: image_gallery_images image_gallery_images_pkey; Type: CONSTRAINT; Schema: public; Owner: ramo_app
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_pkey PRIMARY KEY (id);


--
-- Name: koto koto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.koto
    ADD CONSTRAINT koto_pkey PRIMARY KEY (id);


--
-- Name: link_access_logs link_access_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.link_access_logs
    ADD CONSTRAINT link_access_logs_pkey PRIMARY KEY (id);


--
-- Name: links_json_res links_json_res_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links_json_res
    ADD CONSTRAINT links_json_res_pkey PRIMARY KEY (id);


--
-- Name: links_logs_two links_logs_two_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links_logs_two
    ADD CONSTRAINT links_logs_two_pkey PRIMARY KEY (id);


--
-- Name: links links_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.links
    ADD CONSTRAINT links_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: order_messages order_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_messages
    ADD CONSTRAINT order_messages_pkey PRIMARY KEY (id);


--
-- Name: order_sub_orders order_sub_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_sub_orders
    ADD CONSTRAINT order_sub_orders_pkey PRIMARY KEY (id);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payment_receipts payment_receipts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_receipts
    ADD CONSTRAINT payment_receipts_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: product_category product_category_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_category
    ADD CONSTRAINT product_category_pkey PRIMARY KEY (product_id, category_id);


--
-- Name: product_reviews product_reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_reviews
    ADD CONSTRAINT product_reviews_pkey PRIMARY KEY (id);


--
-- Name: product_variations product_variations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variations
    ADD CONSTRAINT product_variations_pkey PRIMARY KEY (id);


--
-- Name: products_data_main products_data_main_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products_data_main
    ADD CONSTRAINT products_data_main_pkey PRIMARY KEY (id);


--
-- Name: products_data products_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products_data
    ADD CONSTRAINT products_data_pkey PRIMARY KEY (id);


--
-- Name: rate_limits rate_limits_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rate_limits
    ADD CONSTRAINT rate_limits_pkey PRIMARY KEY (consumer_key);


--
-- Name: refund_requests refund_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.refund_requests
    ADD CONSTRAINT refund_requests_pkey PRIMARY KEY (id);


--
-- Name: shops shops_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.shops
    ADD CONSTRAINT shops_pkey PRIMARY KEY (id);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: time_line_configs time_line_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.time_line_configs
    ADD CONSTRAINT time_line_configs_pkey PRIMARY KEY (id);


--
-- Name: user_notes user_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notes
    ADD CONSTRAINT user_notes_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vendor_users vendor_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vendor_users
    ADD CONSTRAINT vendor_users_pkey PRIMARY KEY (id);


--
-- Name: version_config version_config_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.version_config
    ADD CONSTRAINT version_config_pkey PRIMARY KEY (id);


--
-- Name: wishlists wishlists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_pkey PRIMARY KEY (id);


--
-- Name: coupons_vendor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX coupons_vendor_id_index ON public.coupons USING btree (vendor_id);


--
-- Name: idempotency_keys_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idempotency_keys_created_at_index ON public.idempotency_keys USING btree (created_at);


--
-- Name: image_gallery_images_created_at_index; Type: INDEX; Schema: public; Owner: ramo_app
--

CREATE INDEX image_gallery_images_created_at_index ON public.image_gallery_images USING btree (created_at);


--
-- Name: otp_verifications_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_phone_index ON public.otp_verifications USING btree (phone);


--
-- Name: payment_receipts_order_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payment_receipts_order_id_status_index ON public.payment_receipts USING btree (order_id, status);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: product_variations_product_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_variations_product_id_index ON public.product_variations USING btree (product_id);


--
-- Name: image_gallery_images image_gallery_images_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ramo_app
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO ramo_app;


--
-- Name: TABLE api_keys; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.api_keys TO ramo_app;


--
-- Name: SEQUENCE api_keys_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.api_keys_id_seq TO ramo_app;


--
-- Name: TABLE app_config; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.app_config TO ramo_app;


--
-- Name: SEQUENCE app_config_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.app_config_id_seq TO ramo_app;


--
-- Name: TABLE app_configs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.app_configs TO ramo_app;


--
-- Name: SEQUENCE app_configs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.app_configs_id_seq TO ramo_app;


--
-- Name: TABLE attributes; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.attributes TO ramo_app;


--
-- Name: SEQUENCE attributes_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.attributes_id_seq TO ramo_app;


--
-- Name: TABLE blogposts; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.blogposts TO ramo_app;


--
-- Name: SEQUENCE blogposts_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.blogposts_id_seq TO ramo_app;


--
-- Name: TABLE brands; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.brands TO ramo_app;


--
-- Name: SEQUENCE brands_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.brands_id_seq TO ramo_app;


--
-- Name: TABLE cart_items; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.cart_items TO ramo_app;


--
-- Name: SEQUENCE cart_items_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.cart_items_id_seq TO ramo_app;


--
-- Name: TABLE categories2; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.categories2 TO ramo_app;


--
-- Name: SEQUENCE categories2_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.categories2_id_seq TO ramo_app;


--
-- Name: TABLE category_brand_requests; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.category_brand_requests TO ramo_app;


--
-- Name: SEQUENCE category_brand_requests_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.category_brand_requests_id_seq TO ramo_app;


--
-- Name: TABLE countries; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.countries TO ramo_app;


--
-- Name: SEQUENCE countries_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.countries_id_seq TO ramo_app;


--
-- Name: TABLE coupon_user_limits; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.coupon_user_limits TO ramo_app;


--
-- Name: SEQUENCE coupon_user_limits_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.coupon_user_limits_id_seq TO ramo_app;


--
-- Name: TABLE coupons; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.coupons TO ramo_app;


--
-- Name: SEQUENCE coupons_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.coupons_id_seq TO ramo_app;


--
-- Name: TABLE device_access_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.device_access_tokens TO ramo_app;


--
-- Name: SEQUENCE device_access_tokens_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.device_access_tokens_id_seq TO ramo_app;


--
-- Name: TABLE failed_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.failed_jobs TO ramo_app;


--
-- Name: SEQUENCE failed_jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.failed_jobs_id_seq TO ramo_app;


--
-- Name: TABLE getposttest; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.getposttest TO ramo_app;


--
-- Name: SEQUENCE getposttest_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.getposttest_id_seq TO ramo_app;


--
-- Name: TABLE idempotency_keys; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.idempotency_keys TO ramo_app;


--
-- Name: SEQUENCE idempotency_keys_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.idempotency_keys_id_seq TO ramo_app;


--
-- Name: TABLE koto; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.koto TO ramo_app;


--
-- Name: SEQUENCE koto_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.koto_id_seq TO ramo_app;


--
-- Name: TABLE link_access_logs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.link_access_logs TO ramo_app;


--
-- Name: SEQUENCE link_access_logs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.link_access_logs_id_seq TO ramo_app;


--
-- Name: TABLE links; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.links TO ramo_app;


--
-- Name: SEQUENCE links_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.links_id_seq TO ramo_app;


--
-- Name: TABLE links_json_res; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.links_json_res TO ramo_app;


--
-- Name: SEQUENCE links_json_res_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.links_json_res_id_seq TO ramo_app;


--
-- Name: TABLE links_logs_two; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.links_logs_two TO ramo_app;


--
-- Name: SEQUENCE links_logs_two_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.links_logs_two_id_seq TO ramo_app;


--
-- Name: TABLE migrations; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.migrations TO ramo_app;


--
-- Name: SEQUENCE migrations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.migrations_id_seq TO ramo_app;


--
-- Name: TABLE order_messages; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.order_messages TO ramo_app;


--
-- Name: SEQUENCE order_messages_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.order_messages_id_seq TO ramo_app;


--
-- Name: TABLE order_sub_orders; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.order_sub_orders TO ramo_app;


--
-- Name: SEQUENCE order_sub_orders_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.order_sub_orders_id_seq TO ramo_app;


--
-- Name: TABLE orders; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.orders TO ramo_app;


--
-- Name: SEQUENCE orders_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.orders_id_seq TO ramo_app;


--
-- Name: TABLE otp_verifications; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.otp_verifications TO ramo_app;


--
-- Name: SEQUENCE otp_verifications_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.otp_verifications_id_seq TO ramo_app;


--
-- Name: TABLE password_reset_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.password_reset_tokens TO ramo_app;


--
-- Name: TABLE payment_receipts; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.payment_receipts TO ramo_app;


--
-- Name: SEQUENCE payment_receipts_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.payment_receipts_id_seq TO ramo_app;


--
-- Name: TABLE personal_access_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.personal_access_tokens TO ramo_app;


--
-- Name: SEQUENCE personal_access_tokens_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.personal_access_tokens_id_seq TO ramo_app;


--
-- Name: TABLE product_category; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.product_category TO ramo_app;


--
-- Name: TABLE product_reviews; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.product_reviews TO ramo_app;


--
-- Name: SEQUENCE product_reviews_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.product_reviews_id_seq TO ramo_app;


--
-- Name: TABLE product_variations; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.product_variations TO ramo_app;


--
-- Name: SEQUENCE product_variations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.product_variations_id_seq TO ramo_app;


--
-- Name: TABLE products_data; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.products_data TO ramo_app;


--
-- Name: SEQUENCE products_data_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.products_data_id_seq TO ramo_app;


--
-- Name: TABLE products_data_main; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.products_data_main TO ramo_app;


--
-- Name: SEQUENCE products_data_main_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.products_data_main_id_seq TO ramo_app;


--
-- Name: TABLE rate_limits; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.rate_limits TO ramo_app;


--
-- Name: TABLE refund_requests; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.refund_requests TO ramo_app;


--
-- Name: SEQUENCE refund_requests_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.refund_requests_id_seq TO ramo_app;


--
-- Name: TABLE shops; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.shops TO ramo_app;


--
-- Name: SEQUENCE shops_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.shops_id_seq TO ramo_app;


--
-- Name: TABLE tags; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.tags TO ramo_app;


--
-- Name: SEQUENCE tags_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.tags_id_seq TO ramo_app;


--
-- Name: TABLE time_line_configs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.time_line_configs TO ramo_app;


--
-- Name: SEQUENCE time_line_configs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.time_line_configs_id_seq TO ramo_app;


--
-- Name: TABLE user_notes; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.user_notes TO ramo_app;


--
-- Name: SEQUENCE user_notes_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.user_notes_id_seq TO ramo_app;


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.users TO ramo_app;


--
-- Name: SEQUENCE users_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.users_id_seq TO ramo_app;


--
-- Name: TABLE vendor_users; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.vendor_users TO ramo_app;


--
-- Name: SEQUENCE vendor_users_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.vendor_users_id_seq TO ramo_app;


--
-- Name: TABLE version_config; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.version_config TO ramo_app;


--
-- Name: SEQUENCE version_config_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.version_config_id_seq TO ramo_app;


--
-- Name: TABLE wishlists; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.wishlists TO ramo_app;


--
-- Name: SEQUENCE wishlists_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.wishlists_id_seq TO ramo_app;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO ramo_app;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLES TO ramo_app;


--
-- PostgreSQL database dump complete
--

\unrestrict FtjTJkpP3qmua08hLkmLo7Wb6HedX6jWDTzOALk7ehMHhbAqRdXTUyCV5CYMutn

