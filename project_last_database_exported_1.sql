--
-- PostgreSQL database dump
--

\restrict crqyXy1MgneeJnaJ9C5L5mg20dsr1RWxG7WAgLyiGYdKbiwcU31dVSPFlxThdVp

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
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: api_keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.api_keys (
    id integer NOT NULL,
    service_name character varying(255) NOT NULL,
    api_key text NOT NULL,
    encrypted boolean DEFAULT false
);


--
-- Name: api_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.api_keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: api_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.api_keys_id_seq OWNED BY public.api_keys.id;


--
-- Name: app_config; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.app_config (
    id integer NOT NULL,
    config_json text NOT NULL,
    created_at date NOT NULL,
    updated_at date NOT NULL
);


--
-- Name: app_config_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.app_config_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: app_config_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.app_config_id_seq OWNED BY public.app_config.id;


--
-- Name: app_configs; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: app_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.app_configs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: app_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.app_configs_id_seq OWNED BY public.app_configs.id;


--
-- Name: attributes; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: attributes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.attributes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: attributes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.attributes_id_seq OWNED BY public.attributes.id;


--
-- Name: blogposts; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: blogposts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blogposts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blogposts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.blogposts_id_seq OWNED BY public.blogposts.id;


--
-- Name: brands; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.brands (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    image character varying(255)
);


--
-- Name: brands_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.brands_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: brands_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.brands_id_seq OWNED BY public.brands.id;


--
-- Name: cart_items; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: cart_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_items_id_seq OWNED BY public.cart_items.id;


--
-- Name: categories2; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: categories2_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categories2_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categories2_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categories2_id_seq OWNED BY public.categories2.id;


--
-- Name: category_brand_requests; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.category_brand_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.category_brand_requests_id_seq OWNED BY public.category_brand_requests.id;


--
-- Name: countries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.countries (
    id bigint NOT NULL,
    code character varying(255),
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: countries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.countries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.countries_id_seq OWNED BY public.countries.id;


--
-- Name: coupon_user_limits; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.coupon_user_limits (
    id bigint NOT NULL,
    coupon_id bigint NOT NULL,
    user_id bigint NOT NULL,
    use_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.coupon_user_limits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.coupon_user_limits_id_seq OWNED BY public.coupon_user_limits.id;


--
-- Name: coupons; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: coupons_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.coupons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: coupons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.coupons_id_seq OWNED BY public.coupons.id;


--
-- Name: device_access_tokens; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.device_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.device_access_tokens_id_seq OWNED BY public.device_access_tokens.id;


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
-- Name: getposttest; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.getposttest (
    id integer NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    created_at text NOT NULL,
    updated_at text NOT NULL
);


--
-- Name: getposttest_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.getposttest_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: getposttest_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.getposttest_id_seq OWNED BY public.getposttest.id;


--
-- Name: idempotency_keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.idempotency_keys (
    id bigint NOT NULL,
    key character varying(36) NOT NULL,
    user_id bigint NOT NULL,
    order_id bigint,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.idempotency_keys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.idempotency_keys_id_seq OWNED BY public.idempotency_keys.id;


--
-- Name: image_gallery_images; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.image_gallery_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.image_gallery_images_id_seq OWNED BY public.image_gallery_images.id;


--
-- Name: koto; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.koto (
    id integer NOT NULL,
    key_in text NOT NULL,
    identfier text NOT NULL
);


--
-- Name: koto_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.koto_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: koto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.koto_id_seq OWNED BY public.koto.id;


--
-- Name: link_access_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.link_access_logs (
    id integer NOT NULL,
    link_name text NOT NULL,
    usage_times integer DEFAULT 0,
    user_call_id text
);


--
-- Name: link_access_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.link_access_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: link_access_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.link_access_logs_id_seq OWNED BY public.link_access_logs.id;


--
-- Name: links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.links (
    id bigint NOT NULL,
    link text NOT NULL,
    data text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    post_data text DEFAULT 'Was_Get_Or_Null'::text NOT NULL
);


--
-- Name: links_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.links_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: links_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.links_id_seq OWNED BY public.links.id;


--
-- Name: links_json_res; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.links_json_res (
    id bigint NOT NULL,
    link character varying(255) NOT NULL,
    data text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: links_json_res_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.links_json_res_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: links_json_res_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.links_json_res_id_seq OWNED BY public.links_json_res.id;


--
-- Name: links_logs_two; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.links_logs_two (
    id integer NOT NULL,
    link text NOT NULL,
    data text NOT NULL,
    post_data text NOT NULL,
    created_at text NOT NULL,
    updated_at text NOT NULL
);


--
-- Name: links_logs_two_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.links_logs_two_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: links_logs_two_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.links_logs_two_id_seq OWNED BY public.links_logs_two.id;


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
-- Name: order_messages; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: order_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.order_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: order_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.order_messages_id_seq OWNED BY public.order_messages.id;


--
-- Name: order_sub_orders; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.order_sub_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.order_sub_orders_id_seq OWNED BY public.order_sub_orders.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.orders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: payment_receipts; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.payment_receipts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.payment_receipts_id_seq OWNED BY public.payment_receipts.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: product_category; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_category (
    product_id bigint NOT NULL,
    category_id bigint NOT NULL
);


--
-- Name: product_reviews; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: product_reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.product_reviews_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: product_reviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.product_reviews_id_seq OWNED BY public.product_reviews.id;


--
-- Name: product_variations; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: product_variations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.product_variations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: product_variations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.product_variations_id_seq OWNED BY public.product_variations.id;


--
-- Name: products_data; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: COLUMN products_data.button_mode; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.products_data.button_mode IS 'Controls which action buttons show on the product card: both, cart_only, details_only';


--
-- Name: products_data_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.products_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: products_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.products_data_id_seq OWNED BY public.products_data.id;


--
-- Name: products_data_main; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: COLUMN products_data_main.button_mode; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.products_data_main.button_mode IS 'Controls which action buttons show on the product card: both, cart_only, details_only';


--
-- Name: products_data_main_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.products_data_main_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: products_data_main_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.products_data_main_id_seq OWNED BY public.products_data_main.id;


--
-- Name: rate_limits; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rate_limits (
    consumer_key character varying(700) NOT NULL,
    request_count integer DEFAULT 0,
    last_request_time integer NOT NULL
);


--
-- Name: refund_requests; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: refund_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.refund_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: refund_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.refund_requests_id_seq OWNED BY public.refund_requests.id;


--
-- Name: saved_addresses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saved_addresses (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    label character varying(80) DEFAULT 'Home'::character varying NOT NULL,
    recipient_name character varying(150) NOT NULL,
    phone character varying(30) NOT NULL,
    governorate character varying(100) NOT NULL,
    city character varying(100) NOT NULL,
    street character varying(255) NOT NULL,
    additional_details text,
    is_default boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: saved_addresses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saved_addresses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saved_addresses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saved_addresses_id_seq OWNED BY public.saved_addresses.id;


--
-- Name: shops; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: shops_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.shops_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: shops_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.shops_id_seq OWNED BY public.shops.id;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- Name: time_line_configs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.time_line_configs (
    id integer NOT NULL,
    lang_code character varying(5) NOT NULL,
    config_json text NOT NULL
);


--
-- Name: time_line_configs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.time_line_configs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: time_line_configs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.time_line_configs_id_seq OWNED BY public.time_line_configs.id;


--
-- Name: user_notes; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: user_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_notes_id_seq OWNED BY public.user_notes.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
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
-- Name: vendor_users; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: vendor_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendor_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendor_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendor_users_id_seq OWNED BY public.vendor_users.id;


--
-- Name: version_config; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.version_config (
    id integer NOT NULL,
    supported_ver_from text DEFAULT '1.0.0'::text NOT NULL,
    supported_ver_to text DEFAULT '4.0.0'::text NOT NULL
);


--
-- Name: version_config_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.version_config_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: version_config_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.version_config_id_seq OWNED BY public.version_config.id;


--
-- Name: wishlists; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wishlists (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    product_id bigint NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: wishlists_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.wishlists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: wishlists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.wishlists_id_seq OWNED BY public.wishlists.id;


--
-- Name: api_keys id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys ALTER COLUMN id SET DEFAULT nextval('public.api_keys_id_seq'::regclass);


--
-- Name: app_config id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_config ALTER COLUMN id SET DEFAULT nextval('public.app_config_id_seq'::regclass);


--
-- Name: app_configs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_configs ALTER COLUMN id SET DEFAULT nextval('public.app_configs_id_seq'::regclass);


--
-- Name: attributes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attributes ALTER COLUMN id SET DEFAULT nextval('public.attributes_id_seq'::regclass);


--
-- Name: blogposts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogposts ALTER COLUMN id SET DEFAULT nextval('public.blogposts_id_seq'::regclass);


--
-- Name: brands id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands ALTER COLUMN id SET DEFAULT nextval('public.brands_id_seq'::regclass);


--
-- Name: cart_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items ALTER COLUMN id SET DEFAULT nextval('public.cart_items_id_seq'::regclass);


--
-- Name: categories2 id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories2 ALTER COLUMN id SET DEFAULT nextval('public.categories2_id_seq'::regclass);


--
-- Name: category_brand_requests id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.category_brand_requests ALTER COLUMN id SET DEFAULT nextval('public.category_brand_requests_id_seq'::regclass);


--
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.countries_id_seq'::regclass);


--
-- Name: coupon_user_limits id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coupon_user_limits ALTER COLUMN id SET DEFAULT nextval('public.coupon_user_limits_id_seq'::regclass);


--
-- Name: coupons id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coupons ALTER COLUMN id SET DEFAULT nextval('public.coupons_id_seq'::regclass);


--
-- Name: device_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.device_access_tokens_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: getposttest id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.getposttest ALTER COLUMN id SET DEFAULT nextval('public.getposttest_id_seq'::regclass);


--
-- Name: idempotency_keys id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.idempotency_keys ALTER COLUMN id SET DEFAULT nextval('public.idempotency_keys_id_seq'::regclass);


--
-- Name: image_gallery_images id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image_gallery_images ALTER COLUMN id SET DEFAULT nextval('public.image_gallery_images_id_seq'::regclass);


--
-- Name: koto id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.koto ALTER COLUMN id SET DEFAULT nextval('public.koto_id_seq'::regclass);


--
-- Name: link_access_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_access_logs ALTER COLUMN id SET DEFAULT nextval('public.link_access_logs_id_seq'::regclass);


--
-- Name: links id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links ALTER COLUMN id SET DEFAULT nextval('public.links_id_seq'::regclass);


--
-- Name: links_json_res id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links_json_res ALTER COLUMN id SET DEFAULT nextval('public.links_json_res_id_seq'::regclass);


--
-- Name: links_logs_two id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links_logs_two ALTER COLUMN id SET DEFAULT nextval('public.links_logs_two_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_messages id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_messages ALTER COLUMN id SET DEFAULT nextval('public.order_messages_id_seq'::regclass);


--
-- Name: order_sub_orders id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_sub_orders ALTER COLUMN id SET DEFAULT nextval('public.order_sub_orders_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- Name: payment_receipts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_receipts ALTER COLUMN id SET DEFAULT nextval('public.payment_receipts_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: product_reviews id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_reviews ALTER COLUMN id SET DEFAULT nextval('public.product_reviews_id_seq'::regclass);


--
-- Name: product_variations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variations ALTER COLUMN id SET DEFAULT nextval('public.product_variations_id_seq'::regclass);


--
-- Name: products_data id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_data ALTER COLUMN id SET DEFAULT nextval('public.products_data_id_seq'::regclass);


--
-- Name: products_data_main id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_data_main ALTER COLUMN id SET DEFAULT nextval('public.products_data_main_id_seq'::regclass);


--
-- Name: refund_requests id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refund_requests ALTER COLUMN id SET DEFAULT nextval('public.refund_requests_id_seq'::regclass);


--
-- Name: saved_addresses id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_addresses ALTER COLUMN id SET DEFAULT nextval('public.saved_addresses_id_seq'::regclass);


--
-- Name: shops id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.shops ALTER COLUMN id SET DEFAULT nextval('public.shops_id_seq'::regclass);


--
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- Name: time_line_configs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.time_line_configs ALTER COLUMN id SET DEFAULT nextval('public.time_line_configs_id_seq'::regclass);


--
-- Name: user_notes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_notes ALTER COLUMN id SET DEFAULT nextval('public.user_notes_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vendor_users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_users ALTER COLUMN id SET DEFAULT nextval('public.vendor_users_id_seq'::regclass);


--
-- Name: version_config id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.version_config ALTER COLUMN id SET DEFAULT nextval('public.version_config_id_seq'::regclass);


--
-- Name: wishlists id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wishlists ALTER COLUMN id SET DEFAULT nextval('public.wishlists_id_seq'::regclass);


--
-- Data for Name: attributes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.attributes (id, name, slug, type, order_by, has_archives, is_visible, _links, updated_at, created_at) FROM stdin;
\.


--
-- Data for Name: blogposts; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.blogposts (id, date, date_gmt, guid, modified, modified_gmt, slug, status, type, link, title, content, excerpt, author, featured_media, comment_status, ping_status, sticky, template, format, meta, categories, tags, class_list, better_featured_image, image_feature, author_name, _links, _embedded, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: brands; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.brands (id, name, image) FROM stdin;
6	Unbranded	\N
\.


--
-- Data for Name: categories2; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categories2 (id, name, slug, parent, description, display, image, menu_order, count, has_children, _links) FROM stdin;
18	Men	men	0	\N	\N	\N	6	\N	\N	\N
19	Shirts	shirts	18	\N	\N	\N	11	\N	\N	\N
20	Shoes	shoes-men	28	\N	\N	\N	10	\N	\N	\N
21	T-Shirts	t-shirts	18	\N	\N	\N	12	\N	\N	\N
22	Women	women	24	\N	\N	\N	13	\N	\N	\N
25	Blazers-ramo	blazers	22	\N	\N	\N	14	\N	\N	\N
26	Dresses	dresses	22	\N	\N	\N	15	\N	\N	\N
28	Jackets	jackets-men	30	\N	\N	\N	9	\N	\N	\N
29	Jeans	jeans	22	\N	\N	\N	17	\N	\N	\N
30	Jeans Man	jeans-men	18	\N	\N	\N	8	\N	\N	\N
311	mobile-phones	Mobile-phones	2	\N	\N	\N	2	\N	\N	\N
314	Uncategorized	uncategorized-ar	0	\N	\N	\N	0	\N	\N	\N
208	Clothing	clothing	0	\N	\N	categories/wsYZvWbNXsKROWvSSK2iyQtI8L0xEXLxUFGCnn8j.png	3	\N	\N	\N
23	Bags	bags	0	\N	\N	\N	4	\N	\N	\N
24	Bag	bags-men-ramo	18	\N	\N	\N	7	\N	\N	\N
\.


--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.countries (id, code, name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: getposttest; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.getposttest (id, title, content, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: koto; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.koto (id, key_in, identfier) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
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
22	2026_08_13_130000_create_saved_addresses_table	7
23	2026_08_13_173000_add_production_lookup_indexes	8
24	2026_08_14_010000_repair_luxe_velvet_jeans_media	9
\.


--
-- Data for Name: product_category; Type: TABLE DATA; Schema: public; Owner: -
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
1224	208
1224	18
1224	24
1224	30
1224	19
1224	21
1224	311
1224	25
1224	26
1224	29
1224	22
1224	20
1224	28
1272	208
1272	18
1272	24
1272	30
1272	19
1272	21
1272	311
1272	25
1272	26
1272	29
1272	22
1272	20
1272	28
\.


--
-- Data for Name: product_variations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.product_variations (id, product_id, main_variation, attributes, price, regular_price, sale_price, stock_quantity, images, created_at, updated_at, stock_status, status) FROM stdin;
7	3	t	{"Color":"Black"}	2200.00	2200.00	\N	11	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish
8	3	f	{"Color":"Cream"}	2200.00	2200.00	\N	6	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish
5	2	f	{"Color":"Black"}	637.50	750.00	637.50	35	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
6	2	f	{"Color":"Red"}	637.50	750.00	637.50	20	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
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
2	1	f	{"Color":"Tan"}	1850.00	1850.00	\N	0	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
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
3	1	f	{"Color":"Brown"}	1850.00	1850.00	\N	1	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
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
1225	1224	t	{"Color":"Gron","Size":"XL"}	32.00	40.00	32.00	10	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish
1226	1224	f	{"Color":"Gron","Size":"36"}	36.00	45.00	36.00	15	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish
40	12	t	{"Color":"Navy","Size":"M"}	1512.00	1890.00	1512.00	9	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish
24	8	f	{"Size":"M"}	450.00	450.00	\N	49	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
1227	1224	f	{"Color":"Groner","Size":"S"}	48.00	60.00	48.00	15	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish
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
81	21	f	{"Color":"Black","Size":"S"}	880.00	1100.00	880.00	22	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
82	21	f	{"Color":"Black","Size":"M"}	880.00	1100.00	880.00	28	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
84	21	f	{"Color":"Nude","Size":"M"}	880.00	1100.00	880.00	20	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
1	1	t	{"Color":"Black"}	1850.00	1850.00	\N	0	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish
85	22	t	{}	2526.00	2526.00	2526.00	140	[]	2026-08-09 14:39:05	2026-08-09 14:39:05	instock	publish
83	21	f	{"Color":"Nude","Size":"S"}	880.00	1100.00	880.00	17	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
76	20	t	{"Color":"Multi","Size":"XS"}	890.00	890.00	\N	0	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish
4	2	t	{"Color":"Beige"}	637.50	750.00	637.50	0	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish
36	11	t	{"Color":"Black","Size":"S"}	1250.00	1250.00	\N	16	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish
23	8	t	{"Size":"S"}	450.00	450.00	\N	0	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish
80	21	t	{"Color":"Black","Size":"XS"}	880.00	1100.00	880.00	0	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish
1275	1272	t	{"RAM":"6 gb ram","Size":"256"}	400.00	500.00	400.00	27	["products\\/variations\\/6-gb-ram\\/XxQNtTnaonKbgKt8Rb5gSAHFSOGF4uJsIhI8Blga.jpg"]	2026-08-18 15:43:17	2026-08-18 15:43:17	instock	publish
1276	1272	f	{"RAM":"6 gb ram","Size":"512"}	560.00	700.00	560.00	34	["products\\/variations\\/6-gb-ram\\/XxQNtTnaonKbgKt8Rb5gSAHFSOGF4uJsIhI8Blga.jpg"]	2026-08-18 15:43:17	2026-08-18 15:43:17	instock	publish
\.


--
-- Data for Name: products_data; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.products_data (id, name, slug, search_text, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount_percentage, short_description, sku, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, related_ids, meta_data, stock_status, has_options, has_variations, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, whatsapp, button_mode) FROM stdin;
1224	Ramez malak	ramez-malak	ramez malak full discription ramez رامز رامز وصف كامل gron xl 36 groner s	ramez-malak					physical	publish	f	visible	Full discription	20	Ramez	SKU-8P6N8Z	\N	\N	\N	\N	t	t	0	f	f	[]	0	0	\N		t	40		f	f	0	f	[]	t	f		0	t	0	0	[]	[]	0		[]	[]	{"thumbnail":"products\\/thumbnails\\/hpiEvmYT1O5ndzMrIPzl7yLufgH2q4QoFXiu1FbZ.jpg","other_images":["products\\/other_images\\/Ab0r7yptkGwNjLEb8OXOQyA9aYogM9GyjAcLijxM.jpg"]}	[]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	["en","ar"]	0	6	0	2026-08-17 17:41:28	2026-08-17 17:41:42	1	10	physical	316	[{"locale":"ar","name":"\\u0631\\u0627\\u0645\\u0632","description":"\\u0631\\u0627\\u0645\\u0632 \\u0648\\u0635\\u0641 \\u0643\\u0627\\u0645\\u0644"}]	approved	{"piece":1}	{"whatsapp":{"available":false,"number":null}}	both
1272	Ramez malak	ramez-malak-2	ramez malak sddsdf 6 gb ram 256 512	ramez-malak-2					physical	publish	f	visible		20	sddsdf	SKU-8P6N8Z	\N	\N	\N	\N	t	t	0	f	f	[]	0	0	\N		t	61		f	f	0	f	[]	t	f		0	t	0	0	[]	[]	0		[]	[]	{"thumbnail":"products\\/thumbnails\\/E4X4HGA57YAdHZ7i6Pc3aobfhOOJEFnguuDoAg2f.jpg","other_images":["products\\/other_images\\/K8Rfi4dYfXwf3YimNfAIFC52mKRWAkz7vhnGaa9c.jpg"],"natural_images":["products\\/natural_images\\/j42QLJYXMgQF9ZyyxY9ARo6Pt5PbNpYClCT8z7HZ.jpg"]}	[]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	["en"]	0		0	2026-08-18 15:43:17	2026-08-18 15:44:06	1	10	physical	316	[]	approved	{"piece":1}	{"whatsapp":{"available":false,"number":null}}	both
22	Luxe Velvet Jeans — Olive	luxe-velvet-jeans-olive	luxe velvet jeans — olive high-quality linen skirt perfect for everyday wear. comfortable fit with a modern look. features: • premium leather fabric • available in multiple sizes • machine washable • true to size fit care instructions: wash at 30°c, do not tumble dry. high-quality linen skirt perfect for everyday wear. comfortable fit with a modern look. summer trending gift بنطلون جينز أنيق منتج مصنوع من أجود الخامات، مريح وعملي للاستخدام اليومي. المميزات: • خامة ممتازة • مقاسات متعددة • سهل العناية تعليمات العناية: اغسل على 30 درجة مئوية.	luxe-velvet-jeans-olive					physical	publish	f	visible	High-quality linen skirt perfect for everyday wear. Comfortable fit with a modern look.\r\n\r\nFeatures:\r\n• Premium leather fabric\r\n• Available in multiple sizes\r\n• Machine washable\r\n• True to size fit\r\n\r\nCare instructions: Wash at 30°C, do not tumble dry.	0	High-quality linen skirt perfect for everyday wear. Comfortable fit with a modern look.	SKU-BZL2XA	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		t	142		f	f	0	f	[]	t	f		0	t	0	0	[]	[]	0		[]	["summer","trending","gift"]	{"thumbnail":"products/luxe-velvet-jeans-olive.jpg","other_images":["products/luxe-velvet-jeans-olive.jpg"],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]	["en", "0", "ar"]	0	6	0	2026-08-09 14:39:05	2026-08-13 08:10:56	1	0	physical	3	[{"name": "بنطلون جينز أنيق", "locale": "0", "description": "منتج مصنوع من أجود الخامات، مريح وعملي للاستخدام اليومي.\\r\\n\\r\\nالمميزات:\\r\\n• خامة ممتازة\\r\\n• مقاسات متعددة\\r\\n• سهل العناية\\r\\n\\r\\nتعليمات العناية: اغسل على 30 درجة مئوية."}, {"name": "جينز مخملي فاخر — زيتي", "locale": "ar", "description": "تنورة كتان عالية الجودة مثالية للارتداء اليومي. ملاءمة مريحة بمظهر عصري.\\r\\n\\r\\nالميزات:\\r\\n• قماش جلدي فاخر\\r\\n• متوفر بمقاسات متعددة\\r\\n• قابل للغسل في الغسالة\\r\\n• يتناسب مع المقاس المعتاد\\r\\n\\r\\nتعليمات العناية: اغسل على درجة حرارة 30°C، لا تجفف في المجفف."}]	approved	{"piece":1}	{"whatsapp":{"available":false,"number":null}}	both
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
\.


--
-- Data for Name: products_data_main; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.products_data_main (id, name, slug, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount, short_description, sku, price, regular_price, sale_price, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, price_html, related_ids, meta_data, stock_status, has_options, post_password, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, button_mode) FROM stdin;
\.


--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tags (id, name, slug, description, count, is_visible, _links, created_at, updated_at) FROM stdin;
\.


--
-- Name: api_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.api_keys_id_seq', 1, false);


--
-- Name: app_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.app_config_id_seq', 1, false);


--
-- Name: app_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.app_configs_id_seq', 94, true);


--
-- Name: attributes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attributes_id_seq', 1, false);


--
-- Name: blogposts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.blogposts_id_seq', 1, false);


--
-- Name: brands_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.brands_id_seq', 6, true);


--
-- Name: cart_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cart_items_id_seq', 642, true);


--
-- Name: categories2_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories2_id_seq', 828, true);


--
-- Name: category_brand_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.category_brand_requests_id_seq', 1, false);


--
-- Name: countries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.countries_id_seq', 1, false);


--
-- Name: coupon_user_limits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.coupon_user_limits_id_seq', 116, true);


--
-- Name: coupons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.coupons_id_seq', 365, true);


--
-- Name: device_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.device_access_tokens_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: getposttest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.getposttest_id_seq', 1, false);


--
-- Name: idempotency_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.idempotency_keys_id_seq', 169, true);


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.image_gallery_images_id_seq', 189, true);


--
-- Name: koto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.koto_id_seq', 1, false);


--
-- Name: link_access_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.link_access_logs_id_seq', 1, false);


--
-- Name: links_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.links_id_seq', 1, false);


--
-- Name: links_json_res_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.links_json_res_id_seq', 1, false);


--
-- Name: links_logs_two_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.links_logs_two_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 24, true);


--
-- Name: order_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_messages_id_seq', 3, true);


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_sub_orders_id_seq', 202, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orders_id_seq', 1127, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 322, true);


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_receipts_id_seq', 377, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: product_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_reviews_id_seq', 156, true);


--
-- Name: product_variations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_variations_id_seq', 1543, true);


--
-- Name: products_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_id_seq', 1541, true);


--
-- Name: products_data_main_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_main_id_seq', 1, false);


--
-- Name: refund_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.refund_requests_id_seq', 139, true);


--
-- Name: saved_addresses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.saved_addresses_id_seq', 2, true);


--
-- Name: shops_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.shops_id_seq', 1, false);


--
-- Name: tags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tags_id_seq', 1, false);


--
-- Name: time_line_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.time_line_configs_id_seq', 1, false);


--
-- Name: user_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_notes_id_seq', 110, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2700, true);


--
-- Name: vendor_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_users_id_seq', 434, true);


--
-- Name: version_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.version_config_id_seq', 1, false);


--
-- Name: wishlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.wishlists_id_seq', 38, true);


--
-- Name: api_keys api_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys
    ADD CONSTRAINT api_keys_pkey PRIMARY KEY (id);


--
-- Name: app_config app_config_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_config
    ADD CONSTRAINT app_config_pkey PRIMARY KEY (id);


--
-- Name: app_configs app_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_configs
    ADD CONSTRAINT app_configs_pkey PRIMARY KEY (id);


--
-- Name: attributes attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attributes
    ADD CONSTRAINT attributes_pkey PRIMARY KEY (id);


--
-- Name: blogposts blogposts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogposts
    ADD CONSTRAINT blogposts_pkey PRIMARY KEY (id);


--
-- Name: brands brands_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_pkey PRIMARY KEY (id);


--
-- Name: cart_items cart_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items
    ADD CONSTRAINT cart_items_pkey PRIMARY KEY (id);


--
-- Name: categories2 categories2_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories2
    ADD CONSTRAINT categories2_pkey PRIMARY KEY (id);


--
-- Name: category_brand_requests category_brand_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.category_brand_requests
    ADD CONSTRAINT category_brand_requests_pkey PRIMARY KEY (id);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- Name: coupon_user_limits coupon_user_limits_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coupon_user_limits
    ADD CONSTRAINT coupon_user_limits_pkey PRIMARY KEY (id);


--
-- Name: coupons coupons_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_code_unique UNIQUE (code);


--
-- Name: coupons coupons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_pkey PRIMARY KEY (id);


--
-- Name: device_access_tokens device_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.device_access_tokens
    ADD CONSTRAINT device_access_tokens_pkey PRIMARY KEY (id);


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
-- Name: getposttest getposttest_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.getposttest
    ADD CONSTRAINT getposttest_pkey PRIMARY KEY (id);


--
-- Name: idempotency_keys idempotency_keys_key_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.idempotency_keys
    ADD CONSTRAINT idempotency_keys_key_user_id_unique UNIQUE (key, user_id);


--
-- Name: idempotency_keys idempotency_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.idempotency_keys
    ADD CONSTRAINT idempotency_keys_pkey PRIMARY KEY (id);


--
-- Name: image_gallery_images image_gallery_images_path_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_path_unique UNIQUE (path);


--
-- Name: image_gallery_images image_gallery_images_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_pkey PRIMARY KEY (id);


--
-- Name: koto koto_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.koto
    ADD CONSTRAINT koto_pkey PRIMARY KEY (id);


--
-- Name: link_access_logs link_access_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_access_logs
    ADD CONSTRAINT link_access_logs_pkey PRIMARY KEY (id);


--
-- Name: links_json_res links_json_res_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links_json_res
    ADD CONSTRAINT links_json_res_pkey PRIMARY KEY (id);


--
-- Name: links_logs_two links_logs_two_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links_logs_two
    ADD CONSTRAINT links_logs_two_pkey PRIMARY KEY (id);


--
-- Name: links links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links
    ADD CONSTRAINT links_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: order_messages order_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_messages
    ADD CONSTRAINT order_messages_pkey PRIMARY KEY (id);


--
-- Name: order_sub_orders order_sub_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_sub_orders
    ADD CONSTRAINT order_sub_orders_pkey PRIMARY KEY (id);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payment_receipts payment_receipts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_receipts
    ADD CONSTRAINT payment_receipts_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: product_category product_category_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_category
    ADD CONSTRAINT product_category_pkey PRIMARY KEY (product_id, category_id);


--
-- Name: product_reviews product_reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_reviews
    ADD CONSTRAINT product_reviews_pkey PRIMARY KEY (id);


--
-- Name: product_variations product_variations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variations
    ADD CONSTRAINT product_variations_pkey PRIMARY KEY (id);


--
-- Name: products_data_main products_data_main_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_data_main
    ADD CONSTRAINT products_data_main_pkey PRIMARY KEY (id);


--
-- Name: products_data products_data_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_data
    ADD CONSTRAINT products_data_pkey PRIMARY KEY (id);


--
-- Name: rate_limits rate_limits_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rate_limits
    ADD CONSTRAINT rate_limits_pkey PRIMARY KEY (consumer_key);


--
-- Name: refund_requests refund_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.refund_requests
    ADD CONSTRAINT refund_requests_pkey PRIMARY KEY (id);


--
-- Name: saved_addresses saved_addresses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_addresses
    ADD CONSTRAINT saved_addresses_pkey PRIMARY KEY (id);


--
-- Name: shops shops_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.shops
    ADD CONSTRAINT shops_pkey PRIMARY KEY (id);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: time_line_configs time_line_configs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.time_line_configs
    ADD CONSTRAINT time_line_configs_pkey PRIMARY KEY (id);


--
-- Name: user_notes user_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_notes
    ADD CONSTRAINT user_notes_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vendor_users vendor_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_users
    ADD CONSTRAINT vendor_users_pkey PRIMARY KEY (id);


--
-- Name: version_config version_config_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.version_config
    ADD CONSTRAINT version_config_pkey PRIMARY KEY (id);


--
-- Name: wishlists wishlists_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wishlists
    ADD CONSTRAINT wishlists_pkey PRIMARY KEY (id);


--
-- Name: cart_items_user_updated_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_items_user_updated_idx ON public.cart_items USING btree (user_id, updated_at DESC);


--
-- Name: categories2_parent_menu_order_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX categories2_parent_menu_order_idx ON public.categories2 USING btree (parent, menu_order, id);


--
-- Name: coupons_vendor_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX coupons_vendor_id_index ON public.coupons USING btree (vendor_id);


--
-- Name: idempotency_keys_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idempotency_keys_created_at_index ON public.idempotency_keys USING btree (created_at);


--
-- Name: image_gallery_images_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX image_gallery_images_created_at_index ON public.image_gallery_images USING btree (created_at);


--
-- Name: order_sub_orders_parent_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX order_sub_orders_parent_idx ON public.order_sub_orders USING btree (parent_order_id, id);


--
-- Name: order_sub_orders_tracking_number_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX order_sub_orders_tracking_number_idx ON public.order_sub_orders USING btree (tracking_number) WHERE (tracking_number IS NOT NULL);


--
-- Name: orders_customer_created_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX orders_customer_created_idx ON public.orders USING btree (customer_id, created_at DESC);


--
-- Name: orders_status_created_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX orders_status_created_idx ON public.orders USING btree (status, created_at DESC);


--
-- Name: otp_verifications_phone_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX otp_verifications_phone_index ON public.otp_verifications USING btree (phone);


--
-- Name: payment_receipts_order_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payment_receipts_order_id_status_index ON public.payment_receipts USING btree (order_id, status);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: product_category_category_product_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_category_category_product_idx ON public.product_category USING btree (category_id, product_id);


--
-- Name: product_variations_main_product_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_variations_main_product_idx ON public.product_variations USING btree (product_id) WHERE (main_variation = true);


--
-- Name: product_variations_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_variations_product_id_index ON public.product_variations USING btree (product_id);


--
-- Name: product_variations_sellable_product_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_variations_sellable_product_idx ON public.product_variations USING btree (product_id) WHERE (regular_price > (0)::numeric);


--
-- Name: products_data_public_catalog_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_data_public_catalog_idx ON public.products_data USING btree (status, acceptance_status, id DESC);


--
-- Name: saved_addresses_user_id_is_default_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saved_addresses_user_id_is_default_index ON public.saved_addresses USING btree (user_id, is_default);


--
-- Name: wishlists_user_product_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wishlists_user_product_idx ON public.wishlists USING btree (user_id, product_id);


--
-- Name: image_gallery_images image_gallery_images_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: saved_addresses saved_addresses_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_addresses
    ADD CONSTRAINT saved_addresses_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict crqyXy1MgneeJnaJ9C5L5mg20dsr1RWxG7WAgLyiGYdKbiwcU31dVSPFlxThdVp

