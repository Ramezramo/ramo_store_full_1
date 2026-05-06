--
-- PostgreSQL database dump
--

\restrict MP2UjsadX5oEsh5ir9engWsRr5GjJWB7acCf2SIE3S0lX4QqPfTDpkH4eaBqZGZ

-- Dumped from database version 16.10
-- Dumped by pg_dump version 16.10

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
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
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
    CONSTRAINT category_brand_requests_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[]))),
    CONSTRAINT category_brand_requests_type_check CHECK (((type)::text = ANY ((ARRAY['category'::character varying, 'brand'::character varying])::text[])))
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
    meta_data text DEFAULT '[]'::text NOT NULL
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
-- Name: email_verification_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.email_verification_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.email_verification_tokens OWNER TO postgres;

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
    updated_at timestamp(0) without time zone
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
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
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
    updated_at timestamp(0) without time zone
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
    whatsapp text DEFAULT ''::text NOT NULL
);


ALTER TABLE public.products_data OWNER TO postgres;

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
    unit text DEFAULT ''::text NOT NULL
);


ALTER TABLE public.products_data_main OWNER TO postgres;

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
    email character varying(255) NOT NULL,
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
2	horizon_layout	layout	ar	[{"layout":"logo","showMenu":true,"showSearch":true,"showLogo":true,"showliked":true},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"Phones","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/phones_image.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":23,"label":"Bag","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":25,"label":"Blazers","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/women_blazers.webp","colors":["#53A2CC","#53A2CC"]},{"category":28,"label":"Shoes","image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/sheos.jpg","colors":["#53688A","#53688A"]},{"category":29,"label":"Jeans","image":"https:\\/\\/us.dockers.com\\/cdn\\/shop\\/files\\/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]},{"category":30,"label":"Jeans Man","image":"https:\\/\\/images.squarespace-cdn.com\\/content\\/v1\\/58add8dd6a49639a87822092\\/1654105465923-95DJO7H19YLTGOSB4CLO\\/how-to-style-mens-jeans.jpg?format=750w","colors":["#12B58C","#12B58C"]}]},{"layout":"bannerImage","isSlider":true,"autoPlay":true,"showNumber":false,"design":"default","showBackGround":true,"radius":2,"items":[{"category":29,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/HP-Banner.webp","padding":7},{"product":30,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/Campaign-LP-04.webp","padding":7},{"category":28,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/Campaign-LP-07.webp","padding":7}]},{"layout":"saleImages","category":19,"headerText":"Shop by Look","maxItemsToShow":8,"productWidth":130,"productConfig":{"imageRatio":1.4,"borderRadius":10}},{"name":"Man Collections","layout":"twoColumn","headerText":"On Sale Today \\u26a1\\ufe0f","productWidth":200,"maxItemsToShow":7,"category":19,"addToCartButtonStyle":{"style":"iconed","backgroundColor":"#E0E0E0","textColor":"#3D3D3D"},"productConfig":{"borderRadius":12.5,"hMargin":10,"vMargin":6,"showHeart":true,"imageRatio":1.5,"layout":"grid"}},{"layout":"bannerImage","design":"static","fit":"fitWidth","marginLeft":0,"marginRight":0,"marginTop":20,"marginBottom":0,"height":0.15,"items":[{"product":30,"image":"https:\\/\\/raw.githubusercontent.com\\/Ramezramo\\/projectxmedia1\\/refs\\/heads\\/main\\/kobunatkhasm.png","padding":7}]},{"name":"SuperMarket Stars","layout":"seupermarketstars","category":21},{"name":"Brands","layout":"brands","category":21}]	Homepage Layout (AR)	\N	t	0	2026-05-03 00:39:38
3	auth_settings	auth	\N	{"otp_length": 6, "email_login": true, "google_login": true, "guest_checkout": false, "phone_otp_login": true, "max_otp_attempts": 3, "auto_register_otp": true, "max_login_attempts": 5, "otp_expiry_minutes": 5, "auto_register_google": true, "max_resends_per_hour": 3, "session_expiry_hours": 24, "resend_cooldown_seconds": 60, "lockout_duration_minutes": 15, "require_name_on_register": true, "require_email_on_register": false}	Auth Settings	Login methods and security configuration	f	0	2026-05-03 15:42:04
1	horizon_layout	layout	en	[{"layout":"coupons","headerText":"This Week's Deals","subLabel":"Use code at checkout","maxItemsToShow":6,"sortBy":"amount","showExpiredFallback":true,"hideWhenEmpty":true},{"layout":"logo","showMenu":true,"showSearch":true,"showLogo":true,"showliked":true},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"Phones","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":23,"label":"Bag","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":25,"label":"Blazers","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp","colors":["#53A2CC","#53A2CC"]},{"category":28,"label":"Shoes","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg","colors":["#53688A","#53688A"]},{"category":29,"label":"Jeans","image":"https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]},{"category":30,"label":"Jeans Man","image":"https://images.squarespace-cdn.com/content/v1/58add8dd6a49639a87822092/1654105465923-95DJO7H19YLTGOSB4CLO/how-to-style-mens-jeans.jpg?format=750w","colors":["#12B58C","#12B58C"]}]},{"layout":"bannerImage","isSlider":true,"autoPlay":false,"showNumber":false,"design":"default","showBackGround":true,"radius":2,"items":[{"category":29,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp","padding":7},{"product":30,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-04.webp","padding":7},{"category":28,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp","padding":7}]},{"layout":"saleImages","category":19,"headerText":"Shop by Look","maxItemsToShow":8,"productWidth":130,"productConfig":{"imageRatio":1.4,"borderRadius":10}},{"name":"Man Collections","layout":"twoColumn","headerText":"On Sale Today ⚡️","productWidth":200,"maxItemsToShow":7,"category":19,"addToCartButtonStyle":{"style":"iconed","backgroundColor":"#E0E0E0","textColor":"#3D3D3D"},"productConfig":{"borderRadius":12.5,"hMargin":10,"vMargin":6,"showHeart":true,"imageRatio":1.5,"layout":"grid"}},{"layout":"bannerImage","design":"static","fit":"fitWidth","marginLeft":0,"marginRight":0,"marginTop":20,"marginBottom":0,"height":0.15,"items":[{"product":30,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/kobunatkhasm.png","padding":7}]},{"name":"SuperMarket Stars","layout":"seupermarketstars","category":21},{"name":"Brands","layout":"brands","category":21},{"layout":"statsBar","bgColor":"#111111","textColor":"#ffffff","items":[{"key":"products","label":"Products"},{"key":"vendors","label":"Sellers"},{"key":"categories","label":"Categories"},{"key":"reviews","label":"Reviews"}]},{"layout":"promoBlock","headline":"New Season, New Look","subtext":"Discover the best deals from top brands. Limited time offers updated weekly.","btnText":"Shop All Deals","btnLink":"/shop","bgColor":"#1a1a2e","textColor":"#ffffff","btnColor":"#e85d26","align":"left"},{"layout":"testimonials","headerText":"What Our Customers Say","maxItemsToShow":4,"minRating":4}]	Homepage Layout (EN)	\N	t	0	2026-05-03 21:37:17
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
208	Clothing	clothing	0	\N	\N	\N	3	\N	\N	\N
311	mobile-phones	Mobile-phones	2	\N	\N	\N	2	\N	\N	\N
314	Uncategorized	uncategorized-ar	0	\N	\N	\N	0	\N	\N	\N
316	Shooter	shooter	315		visible	\N	0	0	0	\N
315	Games	games	\N		visible	\N	0	0	1	\N
\.


--
-- Data for Name: category_brand_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.category_brand_requests (id, type, name, description, status, admin_note, vendor_user_id, vendor_name, created_at, updated_at, parent_category_id, parent_category_name) FROM stdin;
1	category	Games	\N	approved	\N	10	Cairo Fashion Hub	2026-05-06 15:05:35	2026-05-06 15:05:51	\N	\N
2	category	Shooter	\N	approved	\N	10	Cairo Fashion Hub	2026-05-06 15:10:09	2026-05-06 15:10:26	315	Games
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

COPY public.coupons (id, code, amount, status, discount_type, date_created, date_created_gmt, date_modified, date_modified_gmt, date_expires, date_expires_gmt, usage_count, individual_use, usage_limit, usage_limit_per_user, limit_usage_to_x_items, product_ids, excluded_product_ids, product_categories, excluded_product_categories, free_shipping, exclude_sale_items, minimum_amount, maximum_amount, email_restrictions, used_by, description, meta_data) FROM stdin;
2	SAVERR20	20.00	publish	percent	2026-05-02 22:43:56	2026-05-02 22:43:56	2026-05-02 22:43:56	2026-05-02 22:43:56	\N	\N	0	f	\N	\N	\N	[]	[]	[]	[]	f	f	50.00	0.00	[]	[]	\N	[]
1	SAVER20	20.00	publish	percent	2026-05-02 22:43:56	2026-05-02 22:43:56	2026-05-02 22:43:56	2026-05-02 22:43:56	\N	\N	0	f	\N	\N	\N	[9]	[]	[]	[]	f	f	50.00	0.00	[]	[]	\N	[]
\.


--
-- Data for Name: device_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.device_access_tokens (id, device_id, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at, key_pass, identifier, blocked, about_device) FROM stdin;
\.


--
-- Data for Name: email_verification_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.email_verification_tokens (email, token, created_at) FROM stdin;
hadeer1hadeer11@gmail.com	$2y$12$lWxIfkNllou7xgCLlZY1n.Km6KyaebgjC9MUe8z.u2yocqNgKGU36	2026-05-03 22:09:09
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
2	2024_01_01_000001_create_ramo_store_schema	2
3	2026_01_18_155149_add_registration_fields_to_users_table	2
4	2026_05_02_000001_create_ecommerce_tables	2
5	2026_05_02_100000_add_is_blocked_to_users_table	2
6	2026_05_03_011946_create_refund_requests_table	3
7	2026_05_03_011947_create_order_messages_table	4
8	2026_05_03_012000_create_order_sub_orders_table	4
9	2026_05_03_012001_add_sub_order_id_to_order_messages	4
10	2026_05_04_000001_add_auth_fields_and_otp_verifications	5
11	2025_05_06_000001_create_category_brand_requests_table	6
12	2025_05_06_000002_add_parent_to_category_brand_requests	7
13	2026_05_06_152830_add_image_to_brands_table	8
\.


--
-- Data for Name: order_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.order_messages (id, order_id, customer_id, vendor_id, sender_type, message, is_vendor_response, created_at, updated_at, sub_order_id) FROM stdin;
1	5	1	1	vendor	phone number is invalid	t	2026-05-03 15:31:51	2026-05-03 15:31:51	5
2	5	1	1	customer	ok sorry update it to 343453454	f	2026-05-03 15:33:16	2026-05-03 15:33:16	5
\.


--
-- Data for Name: order_sub_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.order_sub_orders (id, parent_order_id, vendor_id, customer_id, status, line_items, subtotal, discount_total, total, tracking_number, tracking_carrier, timeline, notes, created_at, updated_at) FROM stdin;
1	1	1	1	completed	[{"product_id":7,"variation_id":11,"name":"MacBook Pro Test","quantity":2,"price":1500,"subtotal":3000,"attributes":{"RAM":"16GB","Storage":"512GB SSD","Color":"Space Gray"}}]	3000.00	600.00	2400.00	\N	\N	[{"status":"processing","note":null,"by":"vendor:1","at":"2026-05-03 01:52:08"},{"status":"shipped","note":null,"by":"vendor:1","at":"2026-05-03 01:59:53"},{"status":"processing","note":null,"by":"vendor:1","at":"2026-05-03 02:00:31"},{"status":"completed","note":null,"by":"vendor:1","at":"2026-05-03 02:10:41"}]	\N	2026-05-03 01:48:50	2026-05-03 02:10:41
2	2	1	1	pending	[{"product_id":8,"variation_id":23,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":10,"price":239.2,"subtotal":2392,"attributes":{"Color":"Navy Blue","Size":"S"}}]	2392.00	0.00	2392.00	\N	\N	[]	\N	2026-05-03 02:16:22	2026-05-03 02:16:22
3	3	1	1	pending	[{"product_id":8,"variation_id":23,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":1,"price":239.2,"subtotal":239.2,"attributes":{"Color":"Navy Blue","Size":"S"}}]	239.20	0.00	239.20	\N	\N	[]	\N	2026-05-03 02:29:15	2026-05-03 02:29:15
4	4	1	1	pending	[{"product_id":8,"variation_id":null,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":1,"price":239.2,"subtotal":239.2,"attributes":[]}]	239.20	0.00	239.20	\N	\N	[]	\N	2026-05-03 10:02:17	2026-05-03 10:02:17
5	5	1	1	processing	[{"product_id":9,"variation_id":72,"name":"ramez product 2","quantity":1,"price":2344,"subtotal":2344,"attributes":{"Color":"Green","Size":"XXL"}}]	2344.00	468.80	1875.20	\N	\N	[{"status":"processing","note":null,"by":"vendor:1","at":"2026-05-03 15:33:58"}]	\N	2026-05-03 15:31:00	2026-05-03 15:33:58
6	6	1	\N	pending	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":1,"price":2355,"subtotal":2355,"attributes":[]}]	2355.00	0.00	2355.00	\N	\N	[]	\N	2026-05-03 15:39:00	2026-05-03 15:39:00
7	18	1	28	pending	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":1,"price":2355,"subtotal":2355,"attributes":[]}]	2355.00	0.00	2355.00	\N	\N	[]	\N	2026-05-03 22:12:36	2026-05-03 22:12:36
40	51	1	29	pending	[{"product_id":9,"variation_id":71,"name":"ramez product 2","quantity":6,"price":1177.74,"subtotal":7066.44,"attributes":{"Color":"Black","Size":"38"}},{"product_id":9,"variation_id":73,"name":"ramez product 2","quantity":1,"price":117.02,"subtotal":117.02,"attributes":{"Color":"White","Size":"XXXL"}}]	7183.46	1436.69	5746.77	\N	\N	[]	\N	2026-05-04 02:07:38	2026-05-04 02:07:38
41	52	1	29	pending	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":132,"price":1177.74,"subtotal":155461.68,"attributes":[]}]	155461.68	31092.34	124369.34	\N	\N	[]	\N	2026-05-04 02:10:48	2026-05-04 02:10:48
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.orders (id, parent_id, parent_vendors_ids, parent_vendors_data, status, currency, version, prices_include_tax, date_created, date_modified, discount_total, discount_tax, shipping_total, shipping_tax, cart_tax, coupon_code, final_total, original_total, coupon_applied, total_tax, customer_id, order_key, billing, shipping, payment_method, payment_method_title, transaction_id, customer_ip_address, customer_user_agent, created_via, customer_note, date_completed, date_paid, cart_hash, meta_data, line_items, tax_lines, shipping_lines, fee_lines, coupon_lines, refunds, payment_url, is_editable, needs_payment, needs_processing, bacs_info, currency_symbol, _links, date_created_gmt, date_modified_gmt, date_completed_gmt, date_paid_gmt, set_paid, number, timeline, updated_at, created_at) FROM stdin;
1	0	\N	\N	completed	EGP	\N	f	2026-05-03 01:48:50	2026-05-03 02:10:41	600.00	0.00	0.00	0.00	0.00	SAVER20	2400.00	3000	1	0.00	1	wc_re0gNKaI967FgGXY2uh7	{"first_name":"ramez","last_name":"malak","email":"adminramoui@gmail.com","phone":"252355342","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Cairo","country":"EG"}	{"first_name":"ramez","last_name":"malak","email":"adminramoui@gmail.com","phone":"252355342","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Cairo","country":"EG"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	77495e89fcfbea2429e57f7c5707b6dd	\N	[{"product_id":7,"variation_id":11,"name":"MacBook Pro Test","quantity":2,"price":1500,"subtotal":3000,"attributes":{"RAM":"16GB","Storage":"512GB SSD","Color":"Space Gray"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 01:48:50	2026-05-03 01:48:50			f	1	[]	2026-05-03 02:10:41	2026-05-03 01:48:50
2	0	\N	\N	pending	EGP	\N	f	2026-05-03 02:16:22	2026-05-03 02:16:22	0.00	0.00	0.00	0.00	0.00	\N	2392.00	2392	0	0.00	1	wc_049XP0PVA0HbxnGOJ3EI	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"El Tahrir Square","address_2":null,"city":"Cairo","state":"Cairo","country":"EG"}	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"El Tahrir Square","address_2":null,"city":"Cairo","state":"Cairo","country":"EG"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	1b57e597b2aa467aa6c0f2632ce305d6	\N	[{"product_id":8,"variation_id":23,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":10,"price":239.2,"subtotal":2392,"attributes":{"Color":"Navy Blue","Size":"S"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 02:16:22	2026-05-03 02:16:22			f	2	[]	2026-05-03 02:16:22	2026-05-03 02:16:22
3	0	\N	\N	pending	EGP	\N	f	2026-05-03 02:29:15	2026-05-03 02:29:15	0.00	0.00	0.00	0.00	0.00	\N	239.20	239	0	0.00	1	wc_4KJPFOumgrFyMXC8EwiG	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445463","longitude":"30.80584"}	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445463","longitude":"30.80584"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	9ac7b847bf8fd7f0c7c964b76ccd0e96	\N	[{"product_id":8,"variation_id":23,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":1,"price":239.2,"subtotal":239.2,"attributes":{"Color":"Navy Blue","Size":"S"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 02:29:15	2026-05-03 02:29:15			f	3	[]	2026-05-03 02:29:15	2026-05-03 02:29:15
4	0	\N	\N	pending	EGP	\N	f	2026-05-03 10:02:17	2026-05-03 10:02:17	0.00	0.00	0.00	0.00	0.00	\N	239.20	239	0	0.00	1	wc_XtjYTkAVV7FnkooctZr8	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445431","longitude":"30.805944"}	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur, Al Minya, 61681, Egypt","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445431","longitude":"30.805944"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	fd82481340ab64f3727c535ab76b357b	\N	[{"product_id":8,"variation_id":null,"name":"Ramez Premium Cotton Polo Shirt \\u2014 Limited Edition","quantity":1,"price":239.2,"subtotal":239.2,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 10:02:17	2026-05-03 10:02:17			f	4	[]	2026-05-03 10:02:17	2026-05-03 10:02:17
10	0	\N	\N	completed	EGP	\N	f	2026-04-08 14:32:45	2026-04-11 14:32:45	0.00	0.00	30.00	0.00	0.00	\N	590.00	590	0	0.00	20	ORD-2025-0010	{"first_name":"Ahmed","last_name":"Hassan","address_1":"12 Tahrir Sq","city":"Cairo","country":"EG","phone":"+20111000001"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":33,"name":"Cotton V-Neck T-Shirt","quantity":2,"price":120,"subtotal":240},{"product_id":20,"name":"Classic White Oxford Shirt","quantity":1,"price":350,"subtotal":350}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					t	10	[{"status":"pending","time":"2025-04-08T10:00:00Z"},{"status":"processing","time":"2025-04-08T12:00:00Z"},{"status":"completed","time":"2025-04-10T14:00:00Z"}]	2026-05-03 14:32:45	2026-04-08 14:32:45
11	0	\N	\N	completed	EGP	\N	f	2026-04-11 14:32:45	2026-04-14 14:32:45	50.00	0.00	30.00	0.00	0.00	\N	750.00	800	0	0.00	21	ORD-2025-0011	{"first_name":"Sara","last_name":"Mohamed","address_1":"45 Corniche","city":"Alexandria","country":"EG","phone":"+20111000002"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":22,"name":"Women Floral Summer Dress","quantity":1,"price":520,"subtotal":520},{"product_id":31,"name":"Classic Aviator Sunglasses","quantity":1,"price":280,"subtotal":280}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					t	11	[{"status":"pending","time":"2025-04-11T09:00:00Z"},{"status":"processing","time":"2025-04-11T11:00:00Z"},{"status":"completed","time":"2025-04-14T16:00:00Z"}]	2026-05-03 14:32:45	2026-04-11 14:32:45
12	0	\N	\N	processing	EGP	\N	f	2026-04-23 14:32:45	2026-04-24 14:32:45	0.00	0.00	30.00	0.00	0.00	\N	1230.00	1230	0	0.00	22	ORD-2025-0012	{"first_name":"Omar","last_name":"Ali","address_1":"88 Pyramids Rd","city":"Giza","country":"EG","phone":"+20111000003"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":25,"name":"Wireless Noise-Cancelling Headphones","quantity":1,"price":1200,"subtotal":1200}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					f	12	[{"status":"pending","time":"2025-04-23T08:00:00Z"},{"status":"processing","time":"2025-04-23T10:00:00Z"}]	2026-05-03 14:32:45	2026-04-23 14:32:45
13	0	\N	\N	completed	EGP	\N	f	2026-04-18 14:32:45	2026-04-22 14:32:45	135.00	0.00	30.00	0.00	0.00	\N	1695.00	1830	0	0.00	23	ORD-2025-0013	{"first_name":"Nour","last_name":"Ibrahim","address_1":"22 Nasr City","city":"Cairo","country":"EG","phone":"+20111000004"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":26,"name":"Smart Watch Series X","quantity":1,"price":1800,"subtotal":1800}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					t	13	[{"status":"pending","time":"2025-04-18T09:30:00Z"},{"status":"processing","time":"2025-04-18T11:00:00Z"},{"status":"completed","time":"2025-04-22T15:00:00Z"}]	2026-05-03 14:32:45	2026-04-18 14:32:45
14	0	\N	\N	pending	EGP	\N	f	2026-05-01 14:32:45	2026-05-01 14:32:45	0.00	0.00	30.00	0.00	0.00	\N	1000.00	970	0	0.00	24	ORD-2025-0014	{"first_name":"Youssef","last_name":"Kamal","address_1":"17 Univ St","city":"Mansoura","country":"EG","phone":"+20111000005"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":24,"name":"Running Sneakers Pro","quantity":1,"price":750,"subtotal":750},{"product_id":27,"name":"Wireless Charging Pad","quantity":1,"price":220,"subtotal":220}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					f	14	[{"status":"pending","time":"2025-05-01T08:00:00Z"}]	2026-05-03 14:32:45	2026-05-01 14:32:45
15	0	\N	\N	processing	EGP	\N	f	2026-04-28 14:32:45	2026-04-29 14:32:45	0.00	0.00	30.00	0.00	0.00	\N	1740.00	1710	0	0.00	25	ORD-2025-0015	{"first_name":"Mariam","last_name":"Saad","address_1":"9 El-Geish St","city":"Tanta","country":"EG","phone":"+20111000006"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":23,"name":"Leather Shoulder Bag","quantity":1,"price":890,"subtotal":890},{"product_id":29,"name":"Women Leather Ankle Boots","quantity":1,"price":820,"subtotal":820}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					f	15	[{"status":"pending","time":"2025-04-28T10:00:00Z"},{"status":"processing","time":"2025-04-28T13:00:00Z"}]	2026-05-03 14:32:45	2026-04-28 14:32:45
16	0	\N	\N	completed	EGP	\N	f	2026-04-25 14:32:45	2026-04-28 14:32:45	78.00	0.00	30.00	0.00	0.00	\N	1022.00	1100	0	0.00	26	ORD-2025-0016	{"first_name":"Khaled","last_name":"Nasser","address_1":"3 Corniche El Nile","city":"Aswan","country":"EG","phone":"+20111000007"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":32,"name":"Laptop Backpack 15.6","quantity":1,"price":420,"subtotal":420},{"product_id":34,"name":"Portable Bluetooth Speaker","quantity":1,"price":650,"subtotal":650}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					t	16	[{"status":"pending","time":"2025-04-25T07:00:00Z"},{"status":"processing","time":"2025-04-25T09:00:00Z"},{"status":"completed","time":"2025-04-28T14:00:00Z"}]	2026-05-03 14:32:45	2026-04-25 14:32:45
17	0	\N	\N	pending	EGP	\N	f	2026-05-02 14:32:45	2026-05-02 14:32:45	0.00	0.00	30.00	0.00	0.00	\N	1460.00	1430	0	0.00	27	ORD-2025-0017	{"first_name":"Layla","last_name":"Farouk","address_1":"12 Zamalek","city":"Cairo","country":"EG","phone":"+20111000008"}	\N	cod	Cash on Delivery	\N	\N	\N	\N	\N	\N	\N	\N	\N	[{"product_id":28,"name":"Men Slim Blazer","quantity":1,"price":980,"subtotal":980},{"product_id":21,"name":"Slim Fit Blue Jeans","quantity":1,"price":450,"subtotal":450}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N					f	17	[{"status":"pending","time":"2025-05-02T18:00:00Z"}]	2026-05-03 14:32:45	2026-05-02 14:32:45
5	0	\N	\N	processing	EGP	\N	f	2026-05-03 15:31:00	2026-05-03 15:33:58	468.80	0.00	0.00	0.00	0.00	SAVER20	1875.20	2344	1	0.00	1	wc_rYAGCTj0ibWErfpxCR0C	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445427","longitude":"30.805958"}	{"first_name":"ramo","last_name":"malak","email":"adminramoui@gmail.com","phone":"q23wertw345","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.445427","longitude":"30.805958"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	dc39b21ea08ae05a3ec4cbd9823bb2f4	\N	[{"product_id":9,"variation_id":72,"name":"ramez product 2","quantity":1,"price":2344,"subtotal":2344,"attributes":{"Color":"Green","Size":"XXL"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 15:31:00	2026-05-03 15:31:00			f	5	[]	2026-05-03 15:33:58	2026-05-03 15:31:00
6	0	\N	\N	pending	EGP	\N	f	2026-05-03 15:39:00	2026-05-03 15:39:00	0.00	0.00	0.00	0.00	0.00	\N	2355.00	2355	0	0.00	\N	wc_aiL7hhZAFiD3QLZD3TJr	{"first_name":"Ramez","last_name":"Malak","email":"ramzmlak40@gmail.com","phone":"2455444","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4458292","longitude":"30.804548"}	{"first_name":"Ramez","last_name":"Malak","email":"ramzmlak40@gmail.com","phone":"2455444","address_1":"Al Kufur","address_2":null,"city":"Al Kufur","state":"Minya","country":"EG","latitude":"28.4458292","longitude":"30.804548"}	cod	Cash on Delivery	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	eec403f80a83dbae0ec1d345e2c75397	\N	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":1,"price":2355,"subtotal":2355,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 15:39:00	2026-05-03 15:39:00			f	6	[]	2026-05-03 15:39:00	2026-05-03 15:39:00
18	0	\N	\N	pending	EGP	\N	f	2026-05-03 22:12:36	2026-05-03 22:12:36	0.00	0.00	0.00	0.00	0.00	\N	2355.00	2355	0	0.00	28	wc_dn98d1HH1K4N2QWojUcy	{"first_name":"RAMEZ_HADE","last_name":"MALAK","email":"hadeer1hadeer11@gmail.com","phone":"01002722375","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	{"first_name":"RAMEZ_HADE","last_name":"MALAK","email":"hadeer1hadeer11@gmail.com","phone":"01002722375","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	cod	Cash on Delivery	\N	10.28.198.18	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	eec403f80a83dbae0ec1d345e2c75397	\N	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":1,"price":2355,"subtotal":2355,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-03 22:12:36	2026-05-03 22:12:36			f	18	[]	2026-05-03 22:12:36	2026-05-03 22:12:36
51	0	\N	\N	pending	EGP	\N	f	2026-05-04 02:07:38	2026-05-04 02:07:38	1436.69	0.00	0.00	0.00	0.00	SAVER20	5746.77	7183	1	0.00	29	wc_3l1NwZxXYQzGBrxMqCZt	{"first_name":"RAMEZ","last_name":"MALAK","email":"otp_202394857987@ramostore.local","phone":"+202394857987","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	{"first_name":"RAMEZ","last_name":"MALAK","email":"otp_202394857987@ramostore.local","phone":"+202394857987","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	cod	Cash on Delivery	\N	10.82.15.226	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	252125a785e7c80530374736173b3f16	\N	[{"product_id":9,"variation_id":71,"name":"ramez product 2","quantity":6,"price":1177.74,"subtotal":7066.44,"attributes":{"Color":"Black","Size":"38"}},{"product_id":9,"variation_id":73,"name":"ramez product 2","quantity":1,"price":117.02,"subtotal":117.02,"attributes":{"Color":"White","Size":"XXXL"}}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-04 02:07:38	2026-05-04 02:07:38			f	51	[]	2026-05-04 02:07:38	2026-05-04 02:07:38
52	0	\N	\N	pending	EGP	\N	f	2026-05-04 02:10:48	2026-05-04 02:10:48	31092.34	0.00	0.00	0.00	0.00	SAVER20	124369.34	155462	1	0.00	29	wc_Z16l3g1HmYRYd4Aiyusb	{"first_name":"RAMEZ","last_name":"MALAK","email":"otp_202394857987@ramostore.local","phone":"+202394857987","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	{"first_name":"RAMEZ","last_name":"MALAK","email":"otp_202394857987@ramostore.local","phone":"+202394857987","address_1":"1000 Factory Area","address_2":null,"city":"Cairo","state":"Cairo","country":"EG","latitude":"29.9714","longitude":"31.4808"}	cod	Cash on Delivery	\N	10.82.15.226	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	website		\N	\N	8b06bb2e1707b349b7ed5743e736f92b	\N	[{"product_id":9,"variation_id":null,"name":"ramez product 2","quantity":132,"price":1177.74,"subtotal":155461.68,"attributes":[]}]	\N	\N	\N	\N	\N		t	f	t	\N	ج.م	\N	2026-05-04 02:10:48	2026-05-04 02:10:48			f	52	[]	2026-05-04 02:10:48	2026-05-04 02:10:48
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, phone, otp_code, expires_at, attempts, resend_count, resend_window_start, verified, created_at, updated_at) FROM stdin;
1	+3454553455	777562	2026-05-03 17:54:35	0	0	\N	t	2026-05-03 17:49:35	2026-05-03 17:49:46
2	+3454553455	822316	2026-05-03 18:08:09	0	0	\N	t	2026-05-03 18:03:09	2026-05-03 18:03:20
3	+76587658787	363158	2026-05-03 20:37:15	0	0	\N	t	2026-05-03 20:32:15	2026-05-03 20:33:04
4	+202394857987	036300	2026-05-04 02:11:38	0	0	\N	t	2026-05-04 02:06:38	2026-05-04 02:06:45
6	+2023948579	082940	2026-05-04 02:18:52	0	1	2026-05-04 02:13:52	t	2026-05-04 02:13:52	2026-05-04 02:13:59
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
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
2	23
2	18
2	24
2	25
2	28
7	23
7	18
7	24
9	23
9	24
9	311
\.


--
-- Data for Name: product_reviews; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_reviews (id, product_id, user_id, rating, title, body, created_at, updated_at, approved, is_verified_purchase, helpful_count) FROM stdin;
1	20	20	5	Excellent quality!	Excellent shirt! The fabric quality is outstanding and the fit is perfect. Highly recommend!	2026-04-18 14:32:45	2026-05-03 14:32:45	t	t	8
2	20	21	4	Great shirt	Great shirt, very comfortable. Delivery was fast. Would order again.	2026-04-23 14:32:45	2026-05-03 14:32:45	t	f	3
3	21	22	5	Perfect jeans!	Perfect jeans! Great fit and the stretch makes them very comfortable for daily wear.	2026-04-21 14:32:45	2026-05-03 14:32:45	t	t	6
4	21	23	4	Nice quality denim	Nice jeans, good quality denim. Size was accurate.	2026-04-25 14:32:45	2026-05-03 14:32:45	t	f	2
5	22	24	5	Stunning dress!	Beautiful dress! The fabric is lightweight and the floral print is stunning.	2026-04-24 14:32:45	2026-05-03 14:32:45	t	t	12
6	22	25	5	Love it!	Wore it to a summer wedding and got so many compliments. Absolutely love it.	2026-04-28 14:32:45	2026-05-03 14:32:45	t	t	9
7	23	20	5	Gorgeous leather bag	Absolutely gorgeous bag. The leather quality is real and the stitching is perfect.	2026-04-26 14:32:45	2026-05-03 14:32:45	t	t	7
8	24	26	5	Best running shoes ever	Best running shoes I have owned. Very comfortable for long runs.	2026-04-27 14:32:45	2026-05-03 14:32:45	t	t	11
9	24	27	4	Good quality sneakers	Good quality sneakers. Cushioning is great. Shipping was faster than expected.	2026-04-30 14:32:45	2026-05-03 14:32:45	t	f	4
10	25	22	5	Amazing headphones!	Amazing headphones! Noise cancellation is top-notch. Worth every penny.	2026-04-28 14:32:45	2026-05-03 14:32:45	t	t	15
11	25	21	5	Incredible sound quality	Sound quality is incredible. Battery lasts exactly as advertised. Very happy.	2026-04-29 14:32:45	2026-05-03 14:32:45	t	t	10
12	26	20	5	Love the smart watch!	Smart watch is great! Health tracking is accurate. Love the always-on display.	2026-04-30 14:32:45	2026-05-03 14:32:45	t	t	8
13	28	23	5	Perfect fit blazer	Excellent blazer. Fits perfectly and looks very professional.	2026-05-01 14:32:45	2026-05-03 14:32:45	t	t	5
14	30	24	4	Super comfortable hoodie	Super comfortable hoodie. Very soft fabric. Great for winter.	2026-05-02 14:32:45	2026-05-03 14:32:45	t	f	3
15	32	25	5	Perfect laptop bag!	Perfect laptop bag! Very spacious and the USB charging port is super convenient.	2026-05-02 14:32:45	2026-05-03 14:32:45	t	t	6
16	34	26	5	Speaker sounds amazing!	Speaker sound is amazing for its size. Waterproof feature actually works!	2026-05-03 14:32:45	2026-05-03 14:32:45	t	t	9
17	4	20	5	Great T-shirt!	Great T-shirt! Premium cotton feel. Fits true to size.	2026-04-13 14:32:45	2026-05-03 14:32:45	t	f	5
18	4	21	4	Good quality	Good quality shirt. Color stays after washing.	2026-04-16 14:32:45	2026-05-03 14:32:45	t	f	2
19	5	22	5	Very sturdy stand	Laptop stand is very sturdy and adjustable. Helps a lot with posture.	2026-04-19 14:32:45	2026-05-03 14:32:45	t	t	4
20	9	1	4	great job	this is a pretty good and good quality	2026-05-04 01:08:12	2026-05-04 01:08:12	t	f	0
\.


--
-- Data for Name: product_variations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_variations (id, product_id, main_variation, attributes, price, regular_price, sale_price, stock_quantity, images, created_at, updated_at) FROM stdin;
2	2	t	{}	600.00	500.00	600.00	32	[]	2026-05-02 23:05:52	2026-05-02 23:05:52
3	4	t	{"Color":"Red","Size":"XL"}	120.00	150.00	120.00	10	[]	2026-05-02 23:25:11	2026-05-02 23:25:11
4	4	f	{"Color":"Blue","Size":"M"}	140.00	140.00	\N	5	[]	2026-05-02 23:25:11	2026-05-02 23:25:11
5	4	f	{"Color":"Black","Size":"L"}	130.00	160.00	130.00	8	[]	2026-05-02 23:25:11	2026-05-02 23:25:11
6	5	t	{}	249.00	299.00	249.00	25	[]	2026-05-02 23:25:11	2026-05-02 23:25:11
7	6	t	{"Color":"Red","Size":"XL"}	120.00	150.00	120.00	10	[]	2026-05-02 23:25:34	2026-05-02 23:25:34
8	6	f	{"Color":"Blue","Size":"M"}	140.00	140.00	\N	5	[]	2026-05-02 23:25:34	2026-05-02 23:25:34
9	6	f	{"Color":"Black","Size":"L"}	130.00	160.00	130.00	8	[]	2026-05-02 23:25:34	2026-05-02 23:25:34
10	7	t	{"RAM":"8GB","Storage":"256GB SSD","Color":"Space Gray"}	999.00	1200.00	999.00	5	[]	2026-05-02 23:25:43	2026-05-02 23:25:43
11	7	f	{"RAM":"16GB","Storage":"512GB SSD","Color":"Space Gray"}	1500.00	1500.00	\N	3	[]	2026-05-02 23:25:43	2026-05-02 23:25:43
12	7	f	{"RAM":"16GB","Storage":"1TB SSD","Color":"Silver"}	1650.00	1800.00	1650.00	2	[]	2026-05-02 23:25:43	2026-05-02 23:25:43
38	20	t	[{"name":"Size","option":"M"},{"name":"Color","option":"White"}]	350.00	350.00	\N	80	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
39	21	t	[{"name":"Size","option":"32"},{"name":"Color","option":"Blue"}]	405.00	450.00	405.00	60	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
40	21	f	[{"name":"Size","option":"30"},{"name":"Color","option":"Black"}]	450.00	450.00	\N	20	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
41	22	t	[{"name":"Size","option":"M"},{"name":"Color","option":"Red Floral"}]	442.00	520.00	442.00	45	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
42	22	f	[{"name":"Size","option":"S"},{"name":"Color","option":"Blue Floral"}]	520.00	520.00	\N	15	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
43	23	t	[{"name":"Color","option":"Brown"}]	890.00	890.00	\N	30	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
44	23	f	[{"name":"Color","option":"Black"}]	890.00	890.00	\N	15	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
45	24	t	[{"name":"Size","option":"42"},{"name":"Color","option":"White"}]	600.00	750.00	600.00	55	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
46	24	f	[{"name":"Size","option":"41"},{"name":"Color","option":"Black"}]	750.00	750.00	\N	20	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
47	25	t	[{"name":"Color","option":"Black"}]	1080.00	1200.00	1080.00	25	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
48	25	f	[{"name":"Color","option":"Midnight Blue"}]	1200.00	1200.00	\N	10	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
49	26	t	[{"name":"Color","option":"Black"},{"name":"Size","option":"45mm"}]	1710.00	1800.00	1710.00	20	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
50	26	f	[{"name":"Color","option":"Rose Gold"},{"name":"Size","option":"41mm"}]	1800.00	1800.00	\N	10	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
51	27	t	[{"name":"Color","option":"Black"}]	220.00	220.00	\N	100	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
52	27	f	[{"name":"Color","option":"White"}]	220.00	220.00	\N	50	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
53	28	t	[{"name":"Size","option":"48"},{"name":"Color","option":"Navy"}]	980.00	980.00	\N	35	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
54	28	f	[{"name":"Size","option":"50"},{"name":"Color","option":"Charcoal"}]	980.00	980.00	\N	15	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
55	29	t	[{"name":"Size","option":"38"},{"name":"Color","option":"Black"}]	697.00	820.00	697.00	40	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
56	29	f	[{"name":"Size","option":"39"},{"name":"Color","option":"Brown"}]	820.00	820.00	\N	20	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
57	30	t	[{"name":"Size","option":"L"},{"name":"Color","option":"Black"}]	380.00	380.00	\N	70	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
58	30	f	[{"name":"Size","option":"M"},{"name":"Color","option":"Dusty Pink"}]	380.00	380.00	\N	30	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
59	31	t	[{"name":"Color","option":"Gold/Green"}]	280.00	280.00	\N	90	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
60	31	f	[{"name":"Color","option":"Black/Black"}]	280.00	280.00	\N	40	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
61	32	t	[{"name":"Color","option":"Black"}]	420.00	420.00	\N	50	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
62	32	f	[{"name":"Color","option":"Navy"}]	420.00	420.00	\N	25	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
63	33	t	[{"name":"Size","option":"M"},{"name":"Color","option":"White"}]	120.00	120.00	\N	150	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
64	33	f	[{"name":"Size","option":"L"},{"name":"Color","option":"Black"}]	120.00	120.00	\N	80	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
65	34	t	[{"name":"Color","option":"Black"}]	650.00	650.00	\N	35	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
66	34	f	[{"name":"Color","option":"Camo Green"}]	650.00	650.00	\N	15	[]	2026-05-03 14:33:49	2026-05-03 14:33:49
71	9	t	{"Color":"Black","Size":"38"}	2355.00	2355.00	2355.00	32	["products\\/variations\\/black\\/Sa50lnKqhkx0tYU0BLJFGzVaV427rireGj2vA6RC.jpg"]	2026-05-03 15:01:54	2026-05-04 00:18:01
73	9	f	{"Color":"White","Size":"XXXL"}	234.00	234.00	234.00	234	["products\\/variations\\/white\\/zNPMws2JrKQPMjUo0zcEsniDDVVyhvfLyCvtvFF5.jpg"]	2026-05-03 15:01:54	2026-05-04 00:18:39
72	9	f	{"Color":"Green","Size":"XXL"}	2344.00	2344.00	2344.00	344	[]	2026-05-03 15:01:54	2026-05-04 01:06:52
\.


--
-- Data for Name: products_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products_data (id, name, slug, search_text, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount_percentage, short_description, sku, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, related_ids, meta_data, stock_status, has_options, has_variations, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, whatsapp) FROM stdin;
9	ramez product 2	ramez-product-2	ramez product 2 full description (english) short description (english) black	ramez-product-2					physical	publish	f	visible	Full Description (English)	49.99	Short Description (English)	asgddd	\N	\N	\N	\N	t	t	0	f	f	[]	0	0	\N		t	610		f	f	0	f	[]	t	f		0	t	0	0	[]	[]	0		[]	["dsfggg","sdfggg"]	{"other_images":["products\\/other_images\\/DKZhLkgSjshveMEyQ9TdKiPo8NNkUIyNoqEgP0kv.jpg","products\\/other_images\\/1k52EdldtKUNPoFZQQxvAp3Jd5A1tKBB1xmKIjxT.jpg","products\\/other_images\\/Mfr6Ez6Ox3kMJ8OAqT4L4w4LLa3hHjFLKdpNYmCV.jpg","products\\/other_images\\/KOs2ITvngKi8HlOOOdyotjOaHnDn0kmN9bnMLgLV.jpg"],"natural_images":["products\\/natural_images\\/BX2cDRSVIOq5NhQnwGujzV73vMXeVe0DwJPT58Sd.jpg","products\\/natural_images\\/ekLBai1usaLpwBdv0EtNn6ErwA5WHfDeJRJOTW6R.jpg","products\\/natural_images\\/GrI8fJqV1Yts3JnyReEuDv8e74ta09NdhWVCY5dr.jpg","products\\/natural_images\\/PLEr1pfWnQ3Pb18N0Omxa6w6XJR6Uv0umswYYxFm.jpg"],"thumbnail":"products\\/thumbnails\\/7FY9WFLyVqto7zR1SPzHU9izsRljSnff53Lc9yN3.jpg"}	[{"name":"sfg","values":["sdfggg"]},{"name":"sdfg","values":["sdfggg"]}]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	["en"]	0	3	0	2026-05-03 14:57:09	2026-05-04 01:06:34	1	100	physical	1	[]	approved	{"kg":1.06}	{"whatsapp":{"available":true,"number":"01012345678"}}
\.


--
-- Data for Name: products_data_main; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products_data_main (id, name, slug, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount, short_description, sku, price, regular_price, sale_price, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, price_html, related_ids, meta_data, stock_status, has_options, post_password, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit) FROM stdin;
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
1	10	Cairo Fashion Hub	12 Tahrir Square, Cairo, Egypt	https://picsum.photos/seed/shop1/200/200	https://picsum.photos/seed/shopbanner1/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
2	11	TechZone Egypt	45 Corniche Road, Alexandria, Egypt	https://picsum.photos/seed/shop2/200/200	https://picsum.photos/seed/shopbanner2/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
3	12	Luxury Bags Co	88 Pyramids Road, Giza, Egypt	https://picsum.photos/seed/shop3/200/200	https://picsum.photos/seed/shopbanner3/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
4	13	Shoe Palace Egypt	22 Nasr City, Cairo, Egypt	https://picsum.photos/seed/shop4/200/200	https://picsum.photos/seed/shopbanner4/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
5	14	Street Style Store	5 Zamalek, Cairo, Egypt	https://picsum.photos/seed/shop5/200/200	https://picsum.photos/seed/shopbanner5/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
6	15	Nile Electronics	17 University Street, Mansoura, Egypt	https://picsum.photos/seed/shop6/200/200	https://picsum.photos/seed/shopbanner6/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
7	16	Desert Rose Fashion	3 Corniche El Nile, Aswan, Egypt	https://picsum.photos/seed/shop7/200/200	https://picsum.photos/seed/shopbanner7/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
8	17	Delta Denim Co	9 El-Geish Street, Tanta, Egypt	https://picsum.photos/seed/shop8/200/200	https://picsum.photos/seed/shopbanner8/1200/300	\N	approved	2026-05-03 14:32:45	2026-05-03 14:32:45	\N
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
3	Test User	user@ramostore.com	2026-05-03 22:04:50	$2y$12$zIy.ZUk8WdFwslwbqeTO7OcIlw37kG/sAtjK2L1Sv5XPQ1K6KtF.O	uz8zLXsmR9rf3RNIu3yik1C2D5kY7DWLGKa2gRVTyKqPbsQN5tZbSviQ5c6n	2026-05-02 22:47:40	2026-05-02 22:47:40	\N	\N	\N	Test User	\N	\N	\N	\N		normal_user								\N	f	f	\N	\N
2	Vendor Store	vendor@ramostore.com	2026-05-03 22:04:50	$2y$12$YrFeeZgFhNZ8J3HxpLtQWOoLOG93fyzwn.YyQyHVCnOA4PCfdaxXG	UMHkL7WdAR0QX8v7KyyAoKcNzymbVn4LOnfSDzogSMXJ6Ul5UtyTIlvvaCGM	2026-05-02 22:47:40	2026-05-02 22:47:40	\N	\N	\N	Vendor Store	\N	\N	\N	\N		vendor								\N	f	f	\N	\N
10	Cairo Fashion Hub	cairo.fashion@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000001	vendor								\N	f	f	\N	\N
11	TechZone Egypt	techzone@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000002	vendor								\N	f	f	\N	\N
12	Luxury Bags Co	luxurybags@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000003	vendor								\N	f	f	\N	\N
13	Shoe Palace Egypt	shoepalace@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000004	vendor								\N	f	f	\N	\N
14	Street Style Store	streetstyle@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000005	vendor								\N	f	f	\N	\N
15	Nile Electronics	nile.elec@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000006	vendor								\N	f	f	\N	\N
16	Desert Rose Fashion	desert.rose@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000007	vendor								\N	f	f	\N	\N
17	Delta Denim Co	delta.denim@ramostore.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20100000008	vendor								\N	f	f	\N	\N
20	Ahmed Hassan	ahmed.hassan@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-03 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000001	normal_user								\N	f	f	\N	\N
21	Sara Mohamed	sara.mohamed@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-08 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000002	normal_user								\N	f	f	\N	\N
22	Omar Ali	omar.ali@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-13 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000003	normal_user								\N	f	f	\N	\N
23	Nour Ibrahim	nour.ibrahim@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-18 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000004	normal_user								\N	f	f	\N	\N
24	Youssef Kamal	youssef.kamal@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-23 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000005	normal_user								\N	f	f	\N	\N
25	Mariam Saad	mariam.saad@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-25 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000006	normal_user								\N	f	f	\N	\N
26	Khaled Nasser	khaled.nasser@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-04-28 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000007	normal_user								\N	f	f	\N	\N
27	Layla Farouk	layla.farouk@gmail.com	2026-05-03 22:04:50	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-05-01 14:32:45	2026-05-03 14:32:45	\N	\N	\N	\N	\N	\N	\N	\N	+20111000008	normal_user								\N	f	f	\N	\N
8	Ramez MalakFarouk	otp_3454553455@ramostore.local	2026-05-03 22:04:50	$2y$12$BTFjPtoL6Jg5QUnEczRa4urLCVU9xgO1DjNB.WQQRm8yEE7cLq8DK	\N	2026-05-03 17:54:52	2026-05-03 17:54:52	\N	\N	\N	\N	Ramez	MalakFarouk	\N	\N	+3454553455	normal_user	ramez-malakfarouk-3455	2026-05-03 17:54:52	Ramez	MalakFarouk		{"customer":true}	[]	phone_otp	t	f	\N	\N
9	ramez malak	otp_76587658787@ramostore.local	2026-05-03 22:04:50	$2y$12$UkHiyMpXbOA/yNQBh7rCMOLUDHFeNbaX.759/DuGqXg0/vTKujMe2	\N	2026-05-03 20:33:25	2026-05-03 20:33:25	\N	\N	\N	\N	ramez	malak	\N	\N	+76587658787	normal_user	ramez-malak-8787	2026-05-03 20:33:25	ramez	malak		{"customer":true}	[]	phone_otp	t	f	\N	\N
28	RAMEZ_HADE MALAK	hadeer1hadeer11@gmail.com	2026-05-03 22:10:04	$2y$12$RVH3SP6YJ0Qvxi9Fk1uTkuF/htYH4GbFIH3BqPfUk6FyEqwIVxxtq	\N	2026-05-03 22:07:59	2026-05-03 22:12:36	\N	\N	\N	\N	RAMEZ_HADE	MALAK	\N	\N	01002722375	normal_user	ramez_hade-malak	2026-05-03 22:07:59	RAMEZ_HADE	MALAK		{"customer":true}	{"first_name":"RAMEZ_HADE","last_name":"MALAK","address":"1000 Factory Area","address_note":null,"city":"Cairo","state":"Cairo","email":"hadeer1hadeer11@gmail.com","phone":"01002722375","latitude":"29.9714","longitude":"31.4808"}	email_password	f	f	\N	\N
29	RAMEZ MALAK	otp_202394857987@ramostore.local	\N	$2y$12$XPVOOEawY6IJaDkkRVRmaOsFkoMMExq4wrgTZy5EYSzfIqvu3tSvq	\N	2026-05-04 02:06:56	2026-05-04 02:15:11	\N	\N	\N	\N	RAMEZ	MALAK	\N	\N	+202394857987	normal_user	ramez-malak-7987	2026-05-04 02:06:56	RAMEZ	MALAK		{"customer":true}	{"first_name":"RAMEZ","last_name":"MALAK","address":"1000 Factory Area","address_note":null,"city":"Cairo","state":"Cairo","email":"otp_202394857987@ramostore.local","phone":"+202394857987","latitude":"29.9714","longitude":"31.4808"}	phone_otp	t	f	\N	\N
1	Admin	adminramoui@gmail.com	2026-05-06 15:03:45	$2y$12$1JP33X5fnHrSdPJZA3DbUOBSjM6YQA8Pd/r8pwL9P6sh4xhswMqzG	cKCN56QWYnas7doR1qrDz38urnGjJ0A6vU3VmgBHfQ0AbTozkb2Psqs9eWaM	2026-05-02 22:43:55	2026-05-03 15:31:00	\N	\N	\N	\N	ramo	malak	\N	\N	q23wertw345	admin							{"first_name":"ramo","last_name":"malak","address":"Al Kufur","address_note":null,"city":"Al Kufur","state":"Minya","email":"adminramoui@gmail.com","phone":"q23wertw345","latitude":"28.445427","longitude":"30.805958"}	\N	f	f	\N	\N
\.


--
-- Data for Name: vendor_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vendor_users (id, profile_image, first_name, last_name, phone, email, password, email_verified_at, remember_token, created_at, updated_at, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, bottom_banner, status, rating, rating_count, temporary_close, vacation_end_date, vacation_start_date, vacation_status, offer_banner, product_count, orders_count, minimum_order_amount, free_delivery_over_amount, free_delivery_status, sales_commission_percentage, auth_token, holder_name, account_no, bank_name, branch, free_delivery_features_status, free_delivery_responsibility, minimum_order_amount_by_seller) FROM stdin;
11	\N	Tech	Zone	+20100000002	techzone@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	TechZone Egypt	45 Corniche, Alexandria	\N	\N	\N		approved	4.8	42	0	empty	empty	0	empty	3	3	\N	\N	\N	\N	tok-tz-002	TechZone	\N	NBE Bank	Alexandria Main	\N	\N	\N
12	\N	Luxury	Bags	+20100000003	luxurybags@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Luxury Bags Co	88 Pyramids Rd, Giza	\N	\N	\N		approved	4.9	14	0	empty	empty	0	empty	2	2	\N	\N	\N	\N	tok-lb-003	Luxury Bags	\N	QNB Bank	Giza Branch	\N	\N	\N
13	\N	Shoe	Palace	+20100000004	shoepalace@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Shoe Palace Egypt	22 Nasr City, Cairo	\N	\N	\N		approved	4.6	22	0	empty	empty	0	empty	2	3	\N	\N	\N	\N	tok-sp-004	Shoe Palace	\N	CIB Bank	Nasr City Branch	\N	\N	\N
14	\N	Street	Style	+20100000005	streetstyle@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Street Style Store	5 Zamalek, Cairo	\N	\N	\N		approved	4.5	35	0	empty	empty	0	empty	3	4	\N	\N	\N	\N	tok-ss-005	Street Style	\N	Banque Misr	Zamalek Branch	\N	\N	\N
15	\N	Nile	Elec	+20100000006	nile.elec@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Nile Electronics	17 Univ Street, Mansoura	\N	\N	\N		approved	4.6	19	0	empty	empty	0	empty	2	2	\N	\N	\N	\N	tok-ne-006	Nile Elec	\N	NBE Bank	Mansoura Branch	\N	\N	\N
16	\N	Desert	Rose	+20100000007	desert.rose@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Desert Rose Fashion	3 Corniche El Nile, Aswan	\N	\N	\N		approved	4.8	31	0	empty	empty	0	empty	1	2	\N	\N	\N	\N	tok-dr-007	Desert Rose	\N	CIB Bank	Aswan Branch	\N	\N	\N
17	\N	Delta	Denim	+20100000008	delta.denim@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Delta Denim Co	9 El-Geish Street, Tanta	\N	\N	\N		approved	4.5	18	0	empty	empty	0	empty	1	1	\N	\N	\N	\N	tok-dd-008	Delta Denim	\N	QNB Bank	Tanta Branch	\N	\N	\N
1	\N	Demo	Vendor	+201234567890	vendor@ramostore.com	$2y$12$9hgmXfd.lytKSQUSCqufNeYgaYvBPpgFAxZKBkddoSOEL4kdetwLO	\N	dkCQS3yYE3LP9Omzr3ykusWKhpqyjG17s8r75JTMxmMkooPBPJAwDZK4aiCJ	2026-05-02 22:51:36	2026-05-03 15:16:08	Demo Shop	123 Main Street, Cairo	stores/logo/RDwg507gd88nsWamB9b3rb6SkkdOZ9Rw7kgKA7C1.jpg	\N	\N		approved	0	0	0	empty	empty	0	empty	\N	\N	0	0	0	\N	2vjpOvLzjU8O57TsDA5TkigQXt5jxdmqFzgUDo7B	Demo Vendor	\N	Demo Bank	Main Branch	\N	\N	\N
10	\N	Cairo	Fashion	+20100000001	cairo.fashion@ramostore.com	$2y$12$kMp81QiQtxIht9zsVqPZ3O7w0emD2cWdRu6/jOlND8V6XgR8uizHC	\N	\N	2026-05-03 14:42:03	2026-05-03 14:44:33	Cairo Fashion Hub	12 Tahrir Square, Cairo	\N	\N	\N		approved	4.7	23	0	empty	empty	0	empty	9	5	\N	\N	\N	\N	tok-cf-001	Cairo Fashion	\N	CIB Bank	Cairo Main	\N	\N	\N
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
2	20	22	2026-04-23 14:32:45
3	20	25	2026-04-25 14:32:45
4	21	23	2026-04-26 14:32:45
5	21	26	2026-04-27 14:32:45
6	22	28	2026-04-28 14:32:45
7	23	29	2026-04-29 14:32:45
8	24	32	2026-04-30 14:32:45
9	25	34	2026-05-01 14:32:45
10	26	30	2026-05-02 14:32:45
11	27	24	2026-05-03 14:32:45
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

SELECT pg_catalog.setval('public.app_configs_id_seq', 4, false);


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

SELECT pg_catalog.setval('public.brands_id_seq', 6, false);


--
-- Name: cart_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cart_items_id_seq', 1, false);


--
-- Name: categories2_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories2_id_seq', 316, true);


--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.category_brand_requests_id_seq', 2, true);


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

SELECT pg_catalog.setval('public.coupons_id_seq', 3, false);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 13, true);


--
-- Name: order_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_messages_id_seq', 3, false);


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_sub_orders_id_seq', 41, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.orders_id_seq', 52, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 6, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: product_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_reviews_id_seq', 20, true);


--
-- Name: product_variations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_variations_id_seq', 74, false);


--
-- Name: products_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_data_id_seq', 10, false);


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

SELECT pg_catalog.setval('public.shops_id_seq', 9, false);


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

SELECT pg_catalog.setval('public.users_id_seq', 30, true);


--
-- Name: vendor_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vendor_users_id_seq', 18, false);


--
-- Name: version_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.version_config_id_seq', 2, false);


--
-- Name: wishlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wishlists_id_seq', 12, false);


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
-- Name: email_verification_tokens email_verification_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.email_verification_tokens
    ADD CONSTRAINT email_verification_tokens_pkey PRIMARY KEY (email);


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
-- Name: otp_verifications_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_phone_index ON public.otp_verifications USING btree (phone);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: product_variations_product_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_variations_product_id_index ON public.product_variations USING btree (product_id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

\unrestrict MP2UjsadX5oEsh5ir9engWsRr5GjJWB7acCf2SIE3S0lX4QqPfTDpkH4eaBqZGZ

