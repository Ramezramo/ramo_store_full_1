--
-- PostgreSQL database dump
--

\restrict DHdWQwMqwybigpRaZO7yGk1difuE9nmiF466RkEzkNTEH58IMhpGhoDkBOn6l3P

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
    status character varying(255) DEFAULT 'publish'::character varying NOT NULL,
    model_number character varying(120)
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
-- Name: referral_commissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.referral_commissions (
    id bigint NOT NULL,
    referral_id bigint NOT NULL,
    order_id integer NOT NULL,
    amount numeric(10,2) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    approved_at timestamp(0) without time zone,
    paid_at timestamp(0) without time zone,
    clawed_back_at timestamp(0) without time zone,
    clawback_reason character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: referral_commissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.referral_commissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: referral_commissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.referral_commissions_id_seq OWNED BY public.referral_commissions.id;


--
-- Name: referrals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.referrals (
    id bigint NOT NULL,
    referrer_id bigint NOT NULL,
    referred_id bigint NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    qualifying_order_id integer,
    rejection_reason character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: referrals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.referrals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: referrals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.referrals_id_seq OWNED BY public.referrals.id;


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
-- Name: storefront_price_ranges; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.storefront_price_ranges (
    id bigint NOT NULL,
    scope character varying(255) NOT NULL,
    min_price numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    max_price numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: storefront_price_ranges_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.storefront_price_ranges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: storefront_price_ranges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.storefront_price_ranges_id_seq OWNED BY public.storefront_price_ranges.id;


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
    provider_id character varying(255),
    referral_code character varying(20),
    referred_by bigint,
    referral_lock_ip character varying(45)
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
-- Name: referral_commissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referral_commissions ALTER COLUMN id SET DEFAULT nextval('public.referral_commissions_id_seq'::regclass);


--
-- Name: referrals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referrals ALTER COLUMN id SET DEFAULT nextval('public.referrals_id_seq'::regclass);


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
-- Name: storefront_price_ranges id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storefront_price_ranges ALTER COLUMN id SET DEFAULT nextval('public.storefront_price_ranges_id_seq'::regclass);


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
-- Data for Name: api_keys; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.api_keys (id, service_name, api_key, encrypted) FROM stdin;
\.


--
-- Data for Name: app_config; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.app_config (id, config_json, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: app_configs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.app_configs (id, config_key, config_group, lang, value, label, description, is_public, sort_order, updated_at) FROM stdin;
98	manual_payment_methods	payment	\N	{"cod_enabled":true,"cod_data":"Pay when your order arrives","vodafone_cash_enabled":true,"vodafone_cash_data":"Send to 01xxxxxxxxx","bank_transfer_enabled":true,"bank_transfer_data":"Transfer to our bank account","fawry_enabled":true,"fawry_data":"Pay at any Fawry outlet","credit_card_enabled":true,"credit_card_data":"Visa \\/ Mastercard","wallet_enabled":false,"wallet_number":"","instapay_enabled":false,"instapay_number":"","instapay_link":""}	Manual Payment Methods	Wallet and InstaPay transfer instructions for website orders	f	0	2026-10-12 21:23:06
97	shipping_settings	shipping	\N	{"free_shipping_enabled":true,"free_shipping_threshold":1000,"standard_shipping_fee":0,"cod_fee":40}	Shipping Settings	Free shipping threshold and standard shipping fee	f	0	2026-10-12 21:23:14
161	auth_settings	auth	\N	{"email_login":false,"google_login":false,"phone_otp_login":true,"guest_checkout":false,"auto_register_google":true,"auto_register_otp":true,"require_name_on_register":true,"require_email_on_register":false,"require_email_verification":false,"otp_length":6,"otp_expiry_minutes":5,"max_otp_attempts":3,"resend_cooldown_seconds":60,"max_resends_per_hour":3,"max_login_attempts":5,"lockout_duration_minutes":15,"session_expiry_hours":24}	Auth Settings	Login methods and security configuration	f	0	2026-11-06 10:53:21
95	horizon_layout	layout	en	[{"layout":"announcement","message":"Explore tech, home, style, sport and more at Ramo Store.","speed":"static","barColor":"dark","dismissableByUser":true,"showOnAllPages":true,"showOnlyToGuests":false},{"layout":"promoBlock","headline":"More categories. More ways to shop.","subtext":"Build a PC, refresh your home, upgrade your phone or find something new for everyday life.","btnText":"Explore all products","btnLink":"/shop","bgColor":"#151515","textColor":"#ffffff","btnColor":"#e85d26","align":"left","image":"/storage/products/demo-catalog/pc.jpg"},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":843,"label":"PC Parts","image":"/storage/products/demo-catalog/gpu.jpg","colors":["#2d3748","#111827"]},{"category":844,"label":"Computers","image":"/storage/products/demo-catalog/laptop.jpg","colors":["#475569","#0f172a"]},{"category":845,"label":"Gaming","image":"/storage/products/demo-catalog/keyboard.jpg","colors":["#6d28d9","#312e81"]},{"category":846,"label":"Phones","image":"/storage/products/demo-catalog/phone.jpg","colors":["#2563eb","#0f766e"]},{"category":847,"label":"Audio & Cameras","image":"/storage/products/demo-catalog/camera.jpg","colors":["#334155","#111827"]},{"category":848,"label":"Home & Kitchen","image":"/storage/products/demo-catalog/coffee.jpg","colors":["#92400e","#78350f"]},{"category":850,"label":"Sports","image":"/storage/products/demo-catalog/dumbbells.jpg","colors":["#166534","#14532d"]},{"category":851,"label":"Beauty","image":"/storage/products/demo-catalog/perfume.jpg","colors":["#be185d","#831843"]}]},{"layout":"saleImages","headerText":"Build & Upgrade Your PC","maxItemsToShow":8,"category":843},{"layout":"saleImages","headerText":"Gaming Setup Essentials","maxItemsToShow":8,"category":845},{"layout":"saleImages","headerText":"Home & Kitchen Picks","maxItemsToShow":8,"category":848},{"layout":"trending","headerText":"Trending Across Ramo","count":10,"algo":"sold7d","refreshInterval":24,"showRankBadge":true,"showSoldToday":true,"autoScroll":false},{"layout":"arrivals","headerText":"New Across Every Category","count":8,"speed":"normal","tag":"Just Added","pauseOnHover":true,"showCategoryChip":true,"loopInfinitely":true},{"layout":"allProducts","headerText":"Explore All Products","productsPerPage":12}]	Homepage Timeline	\N	t	0	2026-11-06 12:06:38
96	horizon_layout	layout	ar	[{"layout":"announcement","message":"اكتشف التكنولوجيا والمنزل والأزياء والرياضة والمزيد في رامو ستور.","speed":"static","barColor":"dark","dismissableByUser":true,"showOnAllPages":true,"showOnlyToGuests":false},{"layout":"promoBlock","headline":"فئات أكثر وخيارات أكثر","subtext":"كوّن جهازك، جدّد منزلك، طوّر هاتفك أو اكتشف شيئًا جديدًا لحياتك اليومية.","btnText":"استكشف كل المنتجات","btnLink":"/shop","bgColor":"#151515","textColor":"#ffffff","btnColor":"#e85d26","align":"right","image":"/storage/products/demo-catalog/pc.jpg"},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":843,"label":"قطع الكمبيوتر","image":"/storage/products/demo-catalog/gpu.jpg","colors":["#2d3748","#111827"]},{"category":844,"label":"الكمبيوتر","image":"/storage/products/demo-catalog/laptop.jpg","colors":["#475569","#0f172a"]},{"category":845,"label":"الألعاب","image":"/storage/products/demo-catalog/keyboard.jpg","colors":["#6d28d9","#312e81"]},{"category":846,"label":"الهواتف","image":"/storage/products/demo-catalog/phone.jpg","colors":["#2563eb","#0f766e"]},{"category":847,"label":"الصوت والكاميرات","image":"/storage/products/demo-catalog/camera.jpg","colors":["#334155","#111827"]},{"category":848,"label":"المنزل والمطبخ","image":"/storage/products/demo-catalog/coffee.jpg","colors":["#92400e","#78350f"]},{"category":850,"label":"الرياضة","image":"/storage/products/demo-catalog/dumbbells.jpg","colors":["#166534","#14532d"]},{"category":851,"label":"الجمال","image":"/storage/products/demo-catalog/perfume.jpg","colors":["#be185d","#831843"]}]},{"layout":"saleImages","headerText":"ابنِ وطوّر جهازك","maxItemsToShow":8,"category":843},{"layout":"saleImages","headerText":"أساسيات الألعاب","maxItemsToShow":8,"category":845},{"layout":"saleImages","headerText":"مختارات المنزل والمطبخ","maxItemsToShow":8,"category":848},{"layout":"trending","headerText":"الأكثر رواجًا في رامو","count":10,"algo":"sold7d","refreshInterval":24,"showRankBadge":true,"showSoldToday":true,"autoScroll":false},{"layout":"arrivals","headerText":"جديد كل الفئات","count":8,"speed":"normal","tag":"وصل حديثًا","pauseOnHover":true,"showCategoryChip":true,"loopInfinitely":true},{"layout":"allProducts","headerText":"استكشف كل المنتجات","productsPerPage":12}]	Homepage Timeline	\N	t	0	2026-11-06 12:07:22
\.


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
-- Data for Name: cart_items; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cart_items (id, user_id, product_id, variation_id, qty, created_at, updated_at) FROM stdin;
684	2897	1543	1545	12	2026-11-06 11:43:11	2026-11-06 11:43:11
685	2897	15	53	1	2026-11-06 11:43:11	2026-11-06 11:43:11
686	2897	1542	1544	1	2026-11-06 11:43:11	2026-11-06 11:43:11
687	2897	1	3	1	2026-11-06 11:43:11	2026-11-06 11:43:11
688	2897	21	81	2	2026-11-06 11:43:11	2026-11-06 11:43:11
709	2800	1657	1957	3	2026-11-06 15:11:02	2026-11-06 15:11:02
710	2800	1657	1959	3	2026-11-06 15:11:02	2026-11-06 15:11:02
711	2800	1620	1822	1	2026-11-06 15:11:02	2026-11-06 15:11:02
712	2800	1620	1820	9	2026-11-06 15:11:02	2026-11-06 15:11:02
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
845	Gaming Accessories	gaming-accessories	0	Keyboards, mice, headsets, controllers and streaming gear.	default	products/demo-catalog/keyboard.jpg	0	5	0	{}
846	Phones & Tablets	phones-tablets	0	Smartphones, tablets, watches and mobile charging.	default	products/demo-catalog/phone.jpg	0	5	0	{}
847	Audio & Cameras	audio-cameras	0	Personal audio, speakers, cameras and creator equipment.	default	products/demo-catalog/camera.jpg	0	5	0	{}
848	Home & Kitchen	home-kitchen	0	Useful appliances and cookware for everyday living.	default	products/demo-catalog/coffee.jpg	0	5	0	{}
849	Furniture & Decor	furniture-decor	0	Furniture, lighting and decorative home accents.	default	products/demo-catalog/sofa.jpg	0	4	0	{}
850	Sports & Outdoors	sports-outdoors	0	Training, cycling, football and outdoor essentials.	default	products/demo-catalog/dumbbells.jpg	0	5	0	{}
851	Beauty & Personal Care	beauty-personal-care	0	Fragrance, skincare, makeup and grooming tools.	default	products/demo-catalog/perfume.jpg	0	5	0	{}
852	Books & Toys	books-toys	0	Books, building sets, games and family entertainment.	default	products/demo-catalog/books.jpg	0	4	0	{}
843	Computer Components	computer-components	0	PC parts, storage, cooling, power and upgrade components.	default	products/demo-catalog/gpu.jpg	0	8	0	{}
844	Laptops & Computers	laptops-computers	0	Portable computers, desktops and displays.	default	products/demo-catalog/laptop.jpg	0	5	0	{}
\.


--
-- Data for Name: category_brand_requests; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.category_brand_requests (id, type, name, description, status, admin_note, vendor_user_id, vendor_name, created_at, updated_at, parent_category_id, parent_category_name) FROM stdin;
\.


--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.countries (id, code, name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: coupon_user_limits; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.coupon_user_limits (id, coupon_id, user_id, use_count, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: coupons; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.coupons (id, code, amount, status, discount_type, date_created, date_created_gmt, date_modified, date_modified_gmt, date_expires, date_expires_gmt, usage_count, individual_use, usage_limit, usage_limit_per_user, limit_usage_to_x_items, product_ids, excluded_product_ids, product_categories, excluded_product_categories, free_shipping, exclude_sale_items, minimum_amount, maximum_amount, email_restrictions, used_by, description, meta_data, vendor_id) FROM stdin;
\.


--
-- Data for Name: device_access_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.device_access_tokens (id, device_id, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at, key_pass, identifier, blocked, about_device) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: getposttest; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.getposttest (id, title, content, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: idempotency_keys; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.idempotency_keys (id, key, user_id, order_id, created_at) FROM stdin;
\.


--
-- Data for Name: image_gallery_images; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.image_gallery_images (id, path, original_name, mime_type, file_size, width, height, uploaded_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: koto; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.koto (id, key_in, identfier) FROM stdin;
\.


--
-- Data for Name: link_access_logs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.link_access_logs (id, link_name, usage_times, user_call_id) FROM stdin;
\.


--
-- Data for Name: links; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links (id, link, data, created_at, updated_at, post_data) FROM stdin;
\.


--
-- Data for Name: links_json_res; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links_json_res (id, link, data, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: links_logs_two; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links_logs_two (id, link, data, post_data, created_at, updated_at) FROM stdin;
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
25	2026_08_23_000001_create_referral_program_tables	10
26	2026_08_23_000002_reclassify_ip_only_referrals	10
27	2026_08_26_000001_normalize_and_unique_user_phones	10
28	2026_11_06_000001_create_storefront_price_ranges_table	11
29	2026_11_06_000002_add_model_number_to_product_variations	12
\.


--
-- Data for Name: order_messages; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.order_messages (id, order_id, customer_id, vendor_id, sender_type, message, is_vendor_response, created_at, updated_at, sub_order_id) FROM stdin;
\.


--
-- Data for Name: order_sub_orders; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.order_sub_orders (id, parent_order_id, vendor_id, customer_id, status, line_items, subtotal, discount_total, total, tracking_number, tracking_carrier, timeline, notes, created_at, updated_at, vendor_status) FROM stdin;
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.orders (id, parent_id, parent_vendors_ids, parent_vendors_data, status, currency, version, prices_include_tax, date_created, date_modified, discount_total, discount_tax, shipping_total, shipping_tax, cart_tax, coupon_code, final_total, original_total, coupon_applied, total_tax, customer_id, order_key, billing, shipping, payment_method, payment_method_title, transaction_id, customer_ip_address, customer_user_agent, created_via, customer_note, date_completed, date_paid, cart_hash, meta_data, line_items, tax_lines, shipping_lines, fee_lines, coupon_lines, refunds, payment_url, is_editable, needs_payment, needs_processing, bacs_info, currency_symbol, _links, date_created_gmt, date_modified_gmt, date_completed_gmt, date_paid_gmt, set_paid, number, timeline, updated_at, created_at, payment_status, payment_receipt_path, payment_receipt_name, payment_receipt_uploaded_at, payment_reviewed_at, payment_reviewed_by, payment_rejection_reason, general_order_status, general_order_status_override, general_order_status_override_reason, general_order_status_override_by, general_order_status_override_at) FROM stdin;
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.otp_verifications (id, phone, otp_code, expires_at, attempts, resend_count, resend_window_start, verified, created_at, updated_at) FROM stdin;
331	+201211313312	844005	2026-11-06 10:58:46	0	0	\N	t	2026-11-06 10:53:46	2026-11-06 10:53:58
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: payment_receipts; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.payment_receipts (id, order_id, payment_method, file_path, original_name, status, rejection_reason, uploaded_by, reviewed_by, uploaded_at, reviewed_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
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
1542	23
1543	28
1544	26
1619	843
1620	843
1621	843
1622	843
1623	843
1624	843
1625	843
1626	843
1627	844
1628	844
1629	844
1630	844
1631	844
1632	845
1633	845
1634	845
1635	845
1636	845
1637	846
1638	846
1639	846
1640	846
1641	846
1642	847
1643	847
1644	847
1645	847
1646	847
1647	848
1648	848
1649	848
1650	848
1651	848
1652	849
1653	849
1654	849
1655	849
1656	850
1657	850
1658	850
1659	850
1660	850
1661	851
1662	851
1663	851
1664	851
1665	851
1666	852
1667	852
1668	852
1669	852
\.


--
-- Data for Name: product_reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.product_reviews (id, product_id, user_id, rating, title, body, created_at, updated_at, approved, is_verified_purchase, helpful_count) FROM stdin;
\.


--
-- Data for Name: product_variations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.product_variations (id, product_id, main_variation, attributes, price, regular_price, sale_price, stock_quantity, images, created_at, updated_at, stock_status, status, model_number) FROM stdin;
7	3	t	{"Color":"Black"}	2200.00	2200.00	\N	11	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish	\N
8	3	f	{"Color":"Cream"}	2200.00	2200.00	\N	6	[]	2025-08-16 17:10:14	2025-08-16 17:10:14	instock	publish	\N
5	2	f	{"Color":"Black"}	637.50	750.00	637.50	35	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish	\N
6	2	f	{"Color":"Red"}	637.50	750.00	637.50	20	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish	\N
9	4	t	{"Color":"Navy"}	760.00	950.00	760.00	60	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish	\N
10	4	f	{"Color":"Khaki"}	760.00	950.00	760.00	45	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish	\N
11	4	f	{"Color":"Black"}	760.00	950.00	760.00	55	[]	2025-07-21 17:10:14	2025-07-21 17:10:14	instock	publish	\N
12	5	t	{"Size":"S"}	699.00	699.00	\N	30	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish	\N
13	5	f	{"Size":"M"}	699.00	699.00	\N	45	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish	\N
14	5	f	{"Size":"L"}	699.00	699.00	\N	35	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish	\N
15	5	f	{"Size":"XL"}	699.00	699.00	\N	20	[]	2025-01-29 17:10:14	2025-01-29 17:10:14	instock	publish	\N
16	6	t	{"Size":"XS"}	674.10	749.00	674.10	25	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish	\N
17	6	f	{"Size":"S"}	674.10	749.00	674.10	40	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish	\N
18	6	f	{"Size":"M"}	674.10	749.00	674.10	50	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish	\N
19	6	f	{"Size":"L"}	674.10	749.00	674.10	30	[]	2024-12-05 17:10:14	2024-12-05 17:10:14	instock	publish	\N
20	7	t	{"Size":"S"}	615.00	820.00	615.00	22	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish	\N
21	7	f	{"Size":"M"}	615.00	820.00	615.00	38	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish	\N
22	7	f	{"Size":"L"}	615.00	820.00	615.00	28	[]	2024-11-17 17:10:14	2024-11-17 17:10:14	instock	publish	\N
2	1	f	{"Color":"Tan"}	1850.00	1850.00	\N	0	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish	\N
25	8	f	{"Size":"L"}	450.00	450.00	\N	40	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish	\N
26	8	f	{"Size":"XL"}	450.00	450.00	\N	25	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish	\N
27	8	f	{"Size":"XXL"}	450.00	450.00	\N	15	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish	\N
28	9	t	{"Color":"White","Size":"M"}	520.00	520.00	\N	30	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish	\N
29	9	f	{"Color":"Blue","Size":"M"}	520.00	520.00	\N	28	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish	\N
30	9	f	{"Color":"White","Size":"L"}	520.00	520.00	\N	25	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish	\N
31	9	f	{"Color":"Blue","Size":"L"}	520.00	520.00	\N	22	[]	2024-06-06 17:10:14	2024-06-06 17:10:14	instock	publish	\N
32	10	t	{"Color":"Navy","Size":"S"}	323.00	380.00	323.00	40	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish	\N
33	10	f	{"Color":"Navy","Size":"M"}	323.00	380.00	323.00	55	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish	\N
34	10	f	{"Color":"Red","Size":"M"}	323.00	380.00	323.00	35	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish	\N
35	10	f	{"Color":"White","Size":"L"}	323.00	380.00	323.00	30	[]	2024-06-03 17:10:14	2024-06-03 17:10:14	instock	publish	\N
3	1	f	{"Color":"Brown"}	1850.00	1850.00	\N	1	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish	\N
37	11	f	{"Color":"Black","Size":"M"}	1250.00	1250.00	\N	22	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish	\N
38	11	f	{"Color":"Black","Size":"L"}	1250.00	1250.00	\N	15	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish	\N
39	11	f	{"Color":"Camel","Size":"M"}	1250.00	1250.00	\N	12	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish	\N
41	12	f	{"Color":"Navy","Size":"L"}	1512.00	1890.00	1512.00	12	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish	\N
42	12	f	{"Color":"Grey","Size":"M"}	1512.00	1890.00	1512.00	8	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish	\N
43	12	f	{"Color":"Grey","Size":"L"}	1512.00	1890.00	1512.00	9	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish	\N
44	13	t	{"Color":"White","Size":"40"}	1150.00	1150.00	\N	20	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish	\N
45	13	f	{"Color":"White","Size":"41"}	1150.00	1150.00	\N	30	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish	\N
46	13	f	{"Color":"White","Size":"42"}	1150.00	1150.00	\N	28	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish	\N
47	13	f	{"Color":"Black","Size":"41"}	1150.00	1150.00	\N	25	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish	\N
48	13	f	{"Color":"Black","Size":"42"}	1150.00	1150.00	\N	22	[]	2023-11-07 17:10:14	2023-11-07 17:10:14	instock	publish	\N
49	14	t	{"Color":"Black","Size":"37"}	1332.00	1480.00	1332.00	15	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish	\N
50	14	f	{"Color":"Black","Size":"38"}	1332.00	1480.00	1332.00	20	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish	\N
51	14	f	{"Color":"Black","Size":"39"}	1332.00	1480.00	1332.00	18	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish	\N
52	14	f	{"Color":"Brown","Size":"38"}	1332.00	1480.00	1332.00	12	[]	2023-11-06 17:10:14	2023-11-06 17:10:14	instock	publish	\N
53	15	t	{"Color":"Black","Size":"41"}	2100.00	2100.00	\N	10	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish	\N
54	15	f	{"Color":"Black","Size":"42"}	2100.00	2100.00	\N	12	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish	\N
55	15	f	{"Color":"Brown","Size":"41"}	2100.00	2100.00	\N	8	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish	\N
56	15	f	{"Color":"Brown","Size":"42"}	2100.00	2100.00	\N	10	[]	2023-08-07 17:10:14	2023-08-07 17:10:14	instock	publish	\N
57	16	t	{"Color":"White","Size":"S"}	280.00	280.00	\N	60	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish	\N
58	16	f	{"Color":"White","Size":"M"}	280.00	280.00	\N	80	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish	\N
59	16	f	{"Color":"Black","Size":"M"}	280.00	280.00	\N	75	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish	\N
60	16	f	{"Color":"Black","Size":"L"}	280.00	280.00	\N	65	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish	\N
61	16	f	{"Color":"Grey","Size":"L"}	280.00	280.00	\N	50	[]	2023-06-18 17:10:14	2023-06-18 17:10:14	instock	publish	\N
62	17	t	{"Color":"Sand","Size":"S"}	455.00	650.00	455.00	30	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish	\N
63	17	f	{"Color":"Sand","Size":"M"}	455.00	650.00	455.00	45	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish	\N
1225	1224	t	{"Color":"Gron","Size":"XL"}	32.00	40.00	32.00	10	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish	\N
1226	1224	f	{"Color":"Gron","Size":"36"}	36.00	45.00	36.00	15	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish	\N
40	12	t	{"Color":"Navy","Size":"M"}	1512.00	1890.00	1512.00	9	[]	2023-12-10 17:10:14	2023-12-10 17:10:14	instock	publish	\N
24	8	f	{"Size":"M"}	450.00	450.00	\N	49	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish	\N
1227	1224	f	{"Color":"Groner","Size":"S"}	48.00	60.00	48.00	15	[]	2026-08-17 17:41:28	2026-08-17 17:41:28	instock	publish	\N
64	17	f	{"Color":"Black","Size":"M"}	455.00	650.00	455.00	50	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish	\N
65	17	f	{"Color":"Black","Size":"L"}	455.00	650.00	455.00	40	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish	\N
66	17	f	{"Color":"Grey","Size":"XL"}	455.00	650.00	455.00	25	[]	2023-03-09 17:10:14	2023-03-09 17:10:14	instock	publish	\N
67	18	t	{"Color":"Navy\\/White","Size":"S"}	320.00	320.00	\N	35	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish	\N
68	18	f	{"Color":"Navy\\/White","Size":"M"}	320.00	320.00	\N	50	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish	\N
69	18	f	{"Color":"Red\\/White","Size":"M"}	320.00	320.00	\N	40	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish	\N
70	18	f	{"Color":"Red\\/White","Size":"L"}	320.00	320.00	\N	30	[]	2022-10-24 17:10:14	2022-10-24 17:10:14	instock	publish	\N
71	19	t	{"Color":"Khaki","Size":"S"}	550.00	550.00	\N	30	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish	\N
72	19	f	{"Color":"Khaki","Size":"M"}	550.00	550.00	\N	45	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish	\N
73	19	f	{"Color":"Khaki","Size":"L"}	550.00	550.00	\N	35	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish	\N
74	19	f	{"Color":"Navy","Size":"M"}	550.00	550.00	\N	40	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish	\N
75	19	f	{"Color":"Navy","Size":"L"}	550.00	550.00	\N	30	[]	2022-10-07 17:10:14	2022-10-07 17:10:14	instock	publish	\N
77	20	f	{"Color":"Multi","Size":"S"}	890.00	890.00	\N	35	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish	\N
78	20	f	{"Color":"Multi","Size":"M"}	890.00	890.00	\N	40	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish	\N
79	20	f	{"Color":"Multi","Size":"L"}	890.00	890.00	\N	25	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish	\N
81	21	f	{"Color":"Black","Size":"S"}	880.00	1100.00	880.00	22	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish	\N
82	21	f	{"Color":"Black","Size":"M"}	880.00	1100.00	880.00	28	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish	\N
84	21	f	{"Color":"Nude","Size":"M"}	880.00	1100.00	880.00	20	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish	\N
1	1	t	{"Color":"Black"}	1850.00	1850.00	\N	0	[]	2026-02-12 17:10:14	2026-02-12 17:10:14	instock	publish	\N
85	22	t	{}	2526.00	2526.00	2526.00	140	[]	2026-08-09 14:39:05	2026-08-09 14:39:05	instock	publish	\N
83	21	f	{"Color":"Nude","Size":"S"}	880.00	1100.00	880.00	17	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish	\N
76	20	t	{"Color":"Multi","Size":"XS"}	890.00	890.00	\N	0	[]	2022-08-23 17:10:14	2022-08-23 17:10:14	instock	publish	\N
4	2	t	{"Color":"Beige"}	637.50	750.00	637.50	0	[]	2025-12-18 17:10:14	2025-12-18 17:10:14	instock	publish	\N
36	11	t	{"Color":"Black","Size":"S"}	1250.00	1250.00	\N	16	[]	2024-02-14 17:10:14	2024-02-14 17:10:14	instock	publish	\N
23	8	t	{"Size":"S"}	450.00	450.00	\N	0	[]	2024-09-12 17:10:14	2024-09-12 17:10:14	instock	publish	\N
80	21	t	{"Color":"Black","Size":"XS"}	880.00	1100.00	880.00	0	[]	2022-05-22 17:10:14	2022-05-22 17:10:14	instock	publish	\N
1275	1272	t	{"RAM":"6 gb ram","Size":"256"}	400.00	500.00	400.00	27	["products\\/variations\\/6-gb-ram\\/XxQNtTnaonKbgKt8Rb5gSAHFSOGF4uJsIhI8Blga.jpg"]	2026-08-18 15:43:17	2026-08-18 15:43:17	instock	publish	\N
1276	1272	f	{"RAM":"6 gb ram","Size":"512"}	560.00	700.00	560.00	34	["products\\/variations\\/6-gb-ram\\/XxQNtTnaonKbgKt8Rb5gSAHFSOGF4uJsIhI8Blga.jpg"]	2026-08-18 15:43:17	2026-08-18 15:43:17	instock	publish	\N
1544	1542	t	[]	1450.00	1450.00	\N	18	[]	2026-10-12 20:06:42	2026-10-12 20:06:42	instock	publish	\N
1545	1543	t	[]	1850.00	1850.00	\N	12	[]	2026-10-12 20:06:42	2026-10-12 20:06:42	instock	publish	\N
1546	1544	t	[]	1250.00	1250.00	\N	20	[]	2026-10-12 20:06:42	2026-10-12 20:06:42	instock	publish	\N
1816	1619	t	{"Color":"Black","Size":"8GB"}	28999.00	28999.00	26679.08	6	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1817	1619	f	{"Color":"Black","Size":"12GB"}	30013.97	30013.97	27612.85	13	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1818	1619	f	{"Color":"Silver","Size":"8GB"}	31028.93	31028.93	28546.62	20	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1819	1619	f	{"Color":"Silver","Size":"12GB"}	32043.90	32043.90	29480.39	27	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1820	1620	t	{"Color":"Black","Size":"12GB"}	22499.00	22499.00	21374.05	17	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1821	1620	f	{"Color":"Black","Size":"16GB"}	23286.47	23286.47	22122.15	24	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1822	1620	f	{"Color":"Red","Size":"12GB"}	24073.93	24073.93	22870.23	31	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1823	1620	f	{"Color":"Red","Size":"16GB"}	24861.40	24861.40	23618.33	38	["products/demo-catalog/gpu.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1824	1621	t	{"Size":"Standard"}	11999.00	11999.00	\N	28	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1825	1621	f	{"Size":"Unlocked"}	12418.97	12418.97	\N	35	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1826	1622	t	{"Size":"ATX"}	8499.00	8499.00	7649.10	39	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1827	1622	f	{"Size":"Micro-ATX"}	8796.47	8796.47	7916.82	11	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1828	1623	t	{"Color":"Black","Size":"16GB"}	3999.00	3999.00	\N	15	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1829	1623	f	{"Color":"Black","Size":"32GB"}	4138.97	4138.97	\N	22	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1830	1623	f	{"Color":"White","Size":"16GB"}	4278.93	4278.93	\N	29	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1831	1623	f	{"Color":"White","Size":"32GB"}	4418.90	4418.90	\N	36	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1832	1624	t	{"Size":"500GB"}	2499.00	2499.00	2199.12	26	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1833	1624	f	{"Size":"1TB"}	2586.47	2586.47	2276.09	33	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1834	1624	f	{"Size":"2TB"}	2673.93	2673.93	2353.06	40	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1835	1625	t	{"Color":"Black","Size":"120mm"}	2199.00	2199.00	\N	37	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1836	1625	f	{"Color":"Black","Size":"140mm"}	2275.97	2275.97	\N	9	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1837	1625	f	{"Color":"White","Size":"120mm"}	2352.93	2352.93	\N	16	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1838	1625	f	{"Color":"White","Size":"140mm"}	2429.90	2429.90	\N	23	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1839	1626	t	{"Size":"650W"}	4599.00	4599.00	4277.07	13	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1840	1626	f	{"Size":"750W"}	4759.97	4759.97	4426.77	20	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1841	1626	f	{"Size":"850W"}	4920.93	4920.93	4576.46	27	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1842	1627	t	{"Color":"Silver","Size":"8GB / 512GB"}	32999.00	32999.00	31019.06	24	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1843	1627	f	{"Color":"Silver","Size":"16GB / 1TB"}	34153.97	34153.97	32104.73	31	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1844	1627	f	{"Color":"Midnight","Size":"8GB / 512GB"}	35308.93	35308.93	33190.39	38	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1845	1627	f	{"Color":"Midnight","Size":"16GB / 1TB"}	36463.90	36463.90	34276.07	10	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1846	1628	t	{"Color":"Graphite","Size":"16GB / 1TB"}	58999.00	58999.00	\N	35	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1847	1628	f	{"Color":"Graphite","Size":"32GB / 1TB"}	61063.97	61063.97	\N	7	["products/demo-catalog/laptop.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1848	1629	t	{"Color":"Black","Size":"8GB / 256GB"}	17999.00	17999.00	16379.09	11	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1849	1629	f	{"Color":"Black","Size":"16GB / 512GB"}	18628.97	18628.97	16952.36	18	["products/demo-catalog/pc.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1850	1630	t	{"Color":"Black","Size":"27 inch"}	12499.00	12499.00	\N	22	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1851	1630	f	{"Color":"Black","Size":"32 inch"}	12936.47	12936.47	\N	29	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1852	1631	t	{"Color":"Black","Size":"27 inch"}	15499.00	15499.00	14724.05	33	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1853	1631	f	{"Color":"Black","Size":"32 inch"}	16041.47	16041.47	15239.40	40	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1854	1631	f	{"Color":"Silver","Size":"27 inch"}	16583.93	16583.93	15754.73	12	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1855	1631	f	{"Color":"Silver","Size":"32 inch"}	17126.40	17126.40	16270.08	19	["products/demo-catalog/monitor.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1856	1632	t	{"Color":"Black","Size":"TKL"}	2299.00	2299.00	1954.15	9	["products/demo-catalog/keyboard.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1857	1632	f	{"Color":"Black","Size":"Full Size"}	2379.47	2379.47	2022.55	16	["products/demo-catalog/keyboard.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1858	1632	f	{"Color":"White","Size":"TKL"}	2459.93	2459.93	2090.94	23	["products/demo-catalog/keyboard.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1859	1632	f	{"Color":"White","Size":"Full Size"}	2540.40	2540.40	2159.34	30	["products/demo-catalog/keyboard.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1860	1633	t	{"Color":"Black","Size":"Compact"}	1399.00	1399.00	\N	20	["products/demo-catalog/mouse.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1861	1633	f	{"Color":"Black","Size":"Standard"}	1447.97	1447.97	\N	27	["products/demo-catalog/mouse.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1862	1633	f	{"Color":"White","Size":"Compact"}	1496.93	1496.93	\N	34	["products/demo-catalog/mouse.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1863	1633	f	{"Color":"White","Size":"Standard"}	1545.90	1545.90	\N	6	["products/demo-catalog/mouse.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1864	1634	t	{"Color":"Black","Size":"Wired"}	1899.00	1899.00	1709.10	31	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1865	1634	f	{"Color":"Black","Size":"Wireless"}	1965.47	1965.47	1768.92	38	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1866	1634	f	{"Color":"White","Size":"Wired"}	2031.93	2031.93	1828.74	10	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1867	1634	f	{"Color":"White","Size":"Wireless"}	2098.40	2098.40	1888.56	17	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1868	1635	t	{"Color":"Black","Size":"Desk Stand"}	2799.00	2799.00	\N	7	["products/demo-catalog/microphone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1869	1635	f	{"Color":"Black","Size":"Boom Arm Kit"}	2896.97	2896.97	\N	14	["products/demo-catalog/microphone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1870	1635	f	{"Color":"Silver","Size":"Desk Stand"}	2994.93	2994.93	\N	21	["products/demo-catalog/microphone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1871	1635	f	{"Color":"Silver","Size":"Boom Arm Kit"}	3092.90	3092.90	\N	28	["products/demo-catalog/microphone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1872	1636	t	{"Color":"Black","Size":"Standard"}	1699.00	1699.00	1563.08	18	["products/demo-catalog/controller.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1873	1636	f	{"Color":"Blue","Size":"Standard"}	1758.47	1758.47	1617.79	25	["products/demo-catalog/controller.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1874	1636	f	{"Color":"White","Size":"Standard"}	1817.93	1817.93	1672.50	32	["products/demo-catalog/controller.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1875	1637	t	{"Color":"Black","Size":"128GB"}	21999.00	21999.00	20459.07	29	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1876	1637	f	{"Color":"Black","Size":"256GB"}	22768.97	22768.97	21175.14	36	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1877	1637	f	{"Color":"Blue","Size":"128GB"}	23538.93	23538.93	21891.20	8	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1878	1637	f	{"Color":"Blue","Size":"256GB"}	24308.90	24308.90	22607.28	15	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1879	1638	t	{"Color":"Black","Size":"64GB"}	10999.00	10999.00	\N	40	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1880	1638	f	{"Color":"Black","Size":"128GB"}	11383.97	11383.97	\N	12	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1881	1638	f	{"Color":"Mint","Size":"64GB"}	11768.93	11768.93	\N	19	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1882	1638	f	{"Color":"Mint","Size":"128GB"}	12153.90	12153.90	\N	26	["products/demo-catalog/phone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1883	1639	t	{"Color":"Gray","Size":"128GB Wi-Fi"}	18499.00	18499.00	17574.05	16	["products/demo-catalog/tablet.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1884	1639	f	{"Color":"Gray","Size":"256GB 5G"}	19146.47	19146.47	18189.15	23	["products/demo-catalog/tablet.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1885	1639	f	{"Color":"Silver","Size":"128GB Wi-Fi"}	19793.93	19793.93	18804.23	30	["products/demo-catalog/tablet.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1886	1639	f	{"Color":"Silver","Size":"256GB 5G"}	20441.40	20441.40	19419.33	37	["products/demo-catalog/tablet.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1887	1640	t	{"Color":"Black","Size":"41mm"}	4999.00	4999.00	\N	27	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1888	1640	f	{"Color":"Black","Size":"45mm"}	5173.97	5173.97	\N	34	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1889	1640	f	{"Color":"Rose","Size":"41mm"}	5348.93	5348.93	\N	6	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1890	1640	f	{"Color":"Rose","Size":"45mm"}	5523.90	5523.90	\N	13	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1891	1640	f	{"Color":"Silver","Size":"41mm"}	5698.86	5698.86	\N	20	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1892	1640	f	{"Color":"Silver","Size":"45mm"}	5873.83	5873.83	\N	27	["products/demo-catalog/watch.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1893	1641	t	{"Color":"Black","Size":"2 Port"}	1299.00	1299.00	1143.12	38	["products/demo-catalog/charger.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1894	1641	f	{"Color":"Black","Size":"3 Port"}	1344.47	1344.47	1183.13	10	["products/demo-catalog/charger.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1895	1641	f	{"Color":"White","Size":"2 Port"}	1389.93	1389.93	1223.14	17	["products/demo-catalog/charger.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1896	1641	f	{"Color":"White","Size":"3 Port"}	1435.40	1435.40	1263.15	24	["products/demo-catalog/charger.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1897	1642	t	{"Color":"Black","Size":"Standard"}	4599.00	4599.00	4139.10	14	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1898	1642	f	{"Color":"Cream","Size":"Standard"}	4759.97	4759.97	4283.97	21	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1899	1642	f	{"Color":"Blue","Size":"Standard"}	4920.93	4920.93	4428.84	28	["products/demo-catalog/headset.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1900	1643	t	{"Color":"Black","Size":"Mini"}	2199.00	2199.00	\N	25	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1901	1643	f	{"Color":"Black","Size":"Max"}	2275.97	2275.97	\N	32	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1902	1643	f	{"Color":"Orange","Size":"Mini"}	2352.93	2352.93	\N	39	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1903	1643	f	{"Color":"Orange","Size":"Max"}	2429.90	2429.90	\N	11	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1904	1643	f	{"Color":"Teal","Size":"Mini"}	2506.86	2506.86	\N	18	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1905	1643	f	{"Color":"Teal","Size":"Max"}	2583.83	2583.83	\N	25	["products/demo-catalog/speaker.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1906	1644	t	{"Color":"Black","Size":"Body Only"}	34999.00	34999.00	32899.06	36	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1907	1644	f	{"Color":"Black","Size":"Kit Lens"}	36223.97	36223.97	34050.53	8	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1908	1644	f	{"Color":"Silver","Size":"Body Only"}	37448.93	37448.93	35201.99	15	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1909	1644	f	{"Color":"Silver","Size":"Kit Lens"}	38673.90	38673.90	36353.47	22	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1910	1645	t	{"Color":"Black","Size":"Camera"}	6999.00	6999.00	\N	12	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1911	1645	f	{"Color":"Black","Size":"Adventure Kit"}	7243.97	7243.97	\N	19	["products/demo-catalog/camera.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1912	1646	t	{"Color":"Gray","Size":"Standard"}	18999.00	18999.00	18049.05	23	["products/demo-catalog/drone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1913	1646	f	{"Color":"Gray","Size":"Fly More Kit"}	19663.97	19663.97	18680.77	30	["products/demo-catalog/drone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1914	1646	f	{"Color":"White","Size":"Standard"}	20328.93	20328.93	19312.48	37	["products/demo-catalog/drone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1915	1646	f	{"Color":"White","Size":"Fly More Kit"}	20993.90	20993.90	19944.21	9	["products/demo-catalog/drone.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1916	1647	t	{"Color":"Black","Size":"1.2L"}	6499.00	6499.00	5914.09	34	["products/demo-catalog/coffee.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1917	1647	f	{"Color":"Black","Size":"1.8L"}	6726.47	6726.47	6121.09	6	["products/demo-catalog/coffee.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1918	1647	f	{"Color":"Silver","Size":"1.2L"}	6953.93	6953.93	6328.08	13	["products/demo-catalog/coffee.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1919	1647	f	{"Color":"Silver","Size":"1.8L"}	7181.40	7181.40	6535.07	20	["products/demo-catalog/coffee.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1920	1648	t	{"Color":"Black","Size":"1.5L"}	2799.00	2799.00	\N	10	["products/demo-catalog/blender.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1921	1648	f	{"Color":"Black","Size":"2L"}	2896.97	2896.97	\N	17	["products/demo-catalog/blender.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1922	1648	f	{"Color":"White","Size":"1.5L"}	2994.93	2994.93	\N	24	["products/demo-catalog/blender.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1923	1648	f	{"Color":"White","Size":"2L"}	3092.90	3092.90	\N	31	["products/demo-catalog/blender.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1924	1649	t	{"Color":"Black","Size":"4L"}	4499.00	4499.00	4004.11	21	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1925	1649	f	{"Color":"Black","Size":"7L"}	4656.47	4656.47	4144.26	28	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1926	1649	f	{"Color":"Cream","Size":"4L"}	4813.93	4813.93	4284.40	35	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1927	1649	f	{"Color":"Cream","Size":"7L"}	4971.40	4971.40	4424.55	7	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1928	1650	t	{"Color":"Black","Size":"7 Piece"}	3899.00	3899.00	\N	32	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1929	1650	f	{"Color":"Black","Size":"11 Piece"}	4035.47	4035.47	\N	39	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1930	1650	f	{"Color":"Sage","Size":"7 Piece"}	4171.93	4171.93	\N	11	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1931	1650	f	{"Color":"Sage","Size":"11 Piece"}	4308.40	4308.40	\N	18	["products/demo-catalog/cookware.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1932	1651	t	{"Color":"Graphite","Size":"Standard"}	7299.00	7299.00	6715.08	8	["products/demo-catalog/vacuum.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1933	1651	f	{"Color":"Graphite","Size":"Pet Kit"}	7554.47	7554.47	6950.11	15	["products/demo-catalog/vacuum.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1934	1651	f	{"Color":"White","Size":"Standard"}	7809.93	7809.93	7185.14	22	["products/demo-catalog/vacuum.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1935	1651	f	{"Color":"White","Size":"Pet Kit"}	8065.40	8065.40	7420.17	29	["products/demo-catalog/vacuum.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1936	1652	t	{"Color":"Beige","Size":"Standard"}	5999.00	5999.00	\N	19	["products/demo-catalog/chair.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1937	1652	f	{"Color":"Gray","Size":"Standard"}	6208.97	6208.97	\N	26	["products/demo-catalog/chair.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1938	1652	f	{"Color":"Green","Size":"Standard"}	6418.93	6418.93	\N	33	["products/demo-catalog/chair.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1939	1653	t	{"Color":"Beige","Size":"2 Seat"}	18999.00	18999.00	18049.05	30	["products/demo-catalog/sofa.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1940	1653	f	{"Color":"Beige","Size":"3 Seat"}	19663.97	19663.97	18680.77	37	["products/demo-catalog/sofa.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1941	1653	f	{"Color":"Charcoal","Size":"2 Seat"}	20328.93	20328.93	19312.48	9	["products/demo-catalog/sofa.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1942	1653	f	{"Color":"Charcoal","Size":"3 Seat"}	20993.90	20993.90	19944.21	16	["products/demo-catalog/sofa.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1943	1654	t	{"Color":"Black","Size":"Desk"}	1199.00	1199.00	\N	6	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1944	1654	f	{"Color":"Black","Size":"Floor"}	1240.97	1240.97	\N	13	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1945	1654	f	{"Color":"White","Size":"Desk"}	1282.93	1282.93	\N	20	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1946	1654	f	{"Color":"White","Size":"Floor"}	1324.90	1324.90	\N	27	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1947	1654	f	{"Color":"Gold","Size":"Desk"}	1366.86	1366.86	\N	34	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1948	1654	f	{"Color":"Gold","Size":"Floor"}	1408.83	1408.83	\N	6	["products/demo-catalog/lamp.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1949	1655	t	{"Color":"Black","Size":"Small"}	899.00	899.00	809.10	17	["products/demo-catalog/plant.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1950	1655	f	{"Color":"Black","Size":"Large"}	930.47	930.47	837.42	24	["products/demo-catalog/plant.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1951	1655	f	{"Color":"Natural","Size":"Small"}	961.93	961.93	865.74	31	["products/demo-catalog/plant.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1952	1655	f	{"Color":"Natural","Size":"Large"}	993.40	993.40	894.06	38	["products/demo-catalog/plant.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1953	1656	t	{"Color":"Black","Size":"20kg"}	7999.00	7999.00	7359.08	28	["products/demo-catalog/dumbbells.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1954	1656	f	{"Color":"Black","Size":"32kg"}	8278.97	8278.97	7616.65	35	["products/demo-catalog/dumbbells.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1955	1657	t	{"Color":"Purple","Size":"6mm"}	999.00	999.00	\N	39	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1956	1657	f	{"Color":"Purple","Size":"10mm"}	1033.97	1033.97	\N	11	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1957	1657	f	{"Color":"Teal","Size":"6mm"}	1068.93	1068.93	\N	18	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1958	1657	f	{"Color":"Teal","Size":"10mm"}	1103.90	1103.90	\N	25	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1959	1657	f	{"Color":"Black","Size":"6mm"}	1138.86	1138.86	\N	32	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1960	1657	f	{"Color":"Black","Size":"10mm"}	1173.83	1173.83	\N	39	["products/demo-catalog/yoga.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1961	1658	t	{"Color":"Black","Size":"Medium"}	13499.00	13499.00	12689.06	15	["products/demo-catalog/bike.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1962	1658	f	{"Color":"Black","Size":"Large"}	13971.47	13971.47	13133.18	22	["products/demo-catalog/bike.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1963	1658	f	{"Color":"Blue","Size":"Medium"}	14443.93	14443.93	13577.29	29	["products/demo-catalog/bike.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1964	1658	f	{"Color":"Blue","Size":"Large"}	14916.40	14916.40	14021.42	36	["products/demo-catalog/bike.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1965	1659	t	{"Color":"White","Size":"4"}	749.00	749.00	\N	26	["products/demo-catalog/football.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1966	1659	f	{"Color":"White","Size":"5"}	775.22	775.22	\N	33	["products/demo-catalog/football.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1967	1659	f	{"Color":"Orange","Size":"4"}	801.43	801.43	\N	40	["products/demo-catalog/football.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1968	1659	f	{"Color":"Orange","Size":"5"}	827.65	827.65	\N	12	["products/demo-catalog/football.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1969	1660	t	{"Color":"Black","Size":"30L"}	1899.00	1899.00	1671.12	37	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1970	1660	f	{"Color":"Black","Size":"50L"}	1965.47	1965.47	1729.61	9	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1971	1660	f	{"Color":"Olive","Size":"30L"}	2031.93	2031.93	1788.10	16	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1972	1660	f	{"Color":"Olive","Size":"50L"}	2098.40	2098.40	1846.59	23	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1973	1660	f	{"Color":"Red","Size":"30L"}	2164.86	2164.86	1905.08	30	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1974	1660	f	{"Color":"Red","Size":"50L"}	2231.33	2231.33	1963.57	37	["products/demo-catalog/backpack.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1975	1661	t	{"Size":"50ml"}	2199.00	2199.00	1979.10	13	["products/demo-catalog/perfume.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1976	1661	f	{"Size":"100ml"}	2275.97	2275.97	2048.37	20	["products/demo-catalog/perfume.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1977	1662	t	{"Size":"30ml"}	849.00	849.00	\N	24	["products/demo-catalog/skincare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1978	1662	f	{"Size":"60ml"}	878.72	878.72	\N	31	["products/demo-catalog/skincare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1979	1663	t	{"Color":"Warm","Size":"12 Shade"}	1299.00	1299.00	1104.15	35	["products/demo-catalog/makeup.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1980	1663	f	{"Color":"Warm","Size":"24 Shade"}	1344.47	1344.47	1142.80	7	["products/demo-catalog/makeup.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1981	1663	f	{"Color":"Cool","Size":"12 Shade"}	1389.93	1389.93	1181.44	14	["products/demo-catalog/makeup.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1982	1663	f	{"Color":"Cool","Size":"24 Shade"}	1435.40	1435.40	1220.09	21	["products/demo-catalog/makeup.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1983	1664	t	{"Color":"Black","Size":"1800W"}	1799.00	1799.00	\N	11	["products/demo-catalog/haircare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1984	1664	f	{"Color":"Black","Size":"2200W"}	1861.97	1861.97	\N	18	["products/demo-catalog/haircare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1985	1664	f	{"Color":"Rose","Size":"1800W"}	1924.93	1924.93	\N	25	["products/demo-catalog/haircare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1986	1664	f	{"Color":"Rose","Size":"2200W"}	1987.90	1987.90	\N	32	["products/demo-catalog/haircare.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1987	1665	t	{"Color":"Black","Size":"6 Piece"}	1499.00	1499.00	1379.08	22	["products/demo-catalog/grooming.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1988	1665	f	{"Color":"Black","Size":"10 Piece"}	1551.47	1551.47	1427.35	29	["products/demo-catalog/grooming.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1989	1665	f	{"Color":"Silver","Size":"6 Piece"}	1603.93	1603.93	1475.62	36	["products/demo-catalog/grooming.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1990	1665	f	{"Color":"Silver","Size":"10 Piece"}	1656.40	1656.40	1523.89	8	["products/demo-catalog/grooming.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1991	1666	t	{"Size":"3 Books"}	1199.00	1199.00	\N	33	["products/demo-catalog/books.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1992	1666	f	{"Size":"5 Books"}	1240.97	1240.97	\N	40	["products/demo-catalog/books.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1993	1667	t	{"Color":"Classic","Size":"300 Pieces"}	999.00	999.00	899.10	9	["products/demo-catalog/toys.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1994	1667	f	{"Color":"Classic","Size":"600 Pieces"}	1033.97	1033.97	930.57	16	["products/demo-catalog/toys.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1995	1667	f	{"Color":"Pastel","Size":"300 Pieces"}	1068.93	1068.93	962.04	23	["products/demo-catalog/toys.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1996	1667	f	{"Color":"Pastel","Size":"600 Pieces"}	1103.90	1103.90	993.51	30	["products/demo-catalog/toys.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1997	1668	t	{"Color":"Red","Size":"1:24"}	1399.00	1399.00	\N	20	["products/demo-catalog/toycar.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1998	1668	f	{"Color":"Red","Size":"1:18"}	1447.97	1447.97	\N	27	["products/demo-catalog/toycar.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
1999	1668	f	{"Color":"Blue","Size":"1:24"}	1496.93	1496.93	\N	34	["products/demo-catalog/toycar.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
2000	1668	f	{"Color":"Blue","Size":"1:18"}	1545.90	1545.90	\N	6	["products/demo-catalog/toycar.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
2001	1669	t	{"Size":"Classic"}	749.00	749.00	711.55	31	["products/demo-catalog/boardgame.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
2002	1669	f	{"Size":"Deluxe"}	775.22	775.22	736.46	38	["products/demo-catalog/boardgame.jpg"]	2026-11-06 12:12:53	2026-11-06 12:12:53	instock	publish	\N
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
1542	Everyday Leather Tote	demo-everyday-leather-tote	everyday leather tote a structured everyday tote with room for work and weekend essentials.		2026-10-12 20:06:42		2026-10-12 20:06:42		simple	publish	f	visible	A structured everyday tote with room for work and weekend essentials.	0	A structured everyday tote with room for work and weekend essentials.	\N	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		t	18		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":23}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1590874103328-eac38a683ce7?w=800&h=900&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		1450		1450	2026-10-12 20:06:42	2026-10-12 20:06:42	0	0	physical	436		approved			both
1543	Classic Denim Jacket	demo-classic-denim-jacket	classic denim jacket a versatile mid-weight denim jacket with a clean, classic fit.		2026-10-12 20:06:42		2026-10-12 20:06:42		simple	publish	f	visible	A versatile mid-weight denim jacket with a clean, classic fit.	0	A versatile mid-weight denim jacket with a clean, classic fit.	\N	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		t	12		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":28}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1543076447-215ad9ba6923?w=800&h=900&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		1850		1850	2026-10-12 20:06:42	2026-10-12 20:06:42	0	0	physical	436		approved			both
1544	Soft Cotton Day Dress	demo-soft-cotton-day-dress	soft cotton day dress an easy, comfortable cotton dress for everyday wear.		2026-10-12 20:06:42		2026-10-12 20:06:42		simple	publish	f	visible	An easy, comfortable cotton dress for everyday wear.	0	An easy, comfortable cotton dress for everyday wear.	\N	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		t	20		f	f	0	f	[]	f	f		0	t		0	[]	[]	0		[{"id":26}]	[]	{"thumbnail":"https:\\/\\/images.unsplash.com\\/photo-1595777457583-95e059d581b8?w=800&h=900&fit=crop","other_images":[],"natural_images":[]}	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		1250		1250	2026-10-12 20:06:42	2026-10-12 20:06:42	0	0	physical	436		approved			both
1620	Vector Radeon Graphics Card	demo-vector-radeon-graphics-card	vector radeon graphics card efficient graphics card with quiet cooling and strong 1440p performance. computer components		2026-11-06 11:59:53		2026-11-06 12:12:53		variable	publish	f	visible	Efficient graphics card with quiet cooling and strong 1440p performance.	5	Efficient graphics card with quiet cooling and strong 1440p performance.	DEMO-VECTOR-RADEON-GRAPHICS-CARD	\N	\N	\N	\N	t	t	57	f	f	[]	0	0	\N		t	110		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/gpu.jpg","other_images":["products/demo-catalog/gpu.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	22499		24861.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1621	Atlas 8-Core Processor	demo-atlas-8-core-processor	atlas 8-core processor eight-core desktop processor designed for gaming, streaming and daily productivity. computer components		2026-11-06 11:46:53		2026-11-06 12:12:53		variable	publish	f	visible	Eight-core desktop processor designed for gaming, streaming and daily productivity.	0	Eight-core desktop processor designed for gaming, streaming and daily productivity.	DEMO-ATLAS-8-CORE-PROCESSOR	\N	\N	\N	\N	f	t	94	f	f	[]	0	0	\N		t	63		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	11999		12418.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1626	CoreMax Modular Power Supply	demo-coremax-modular-power-supply	coremax modular power supply fully modular high-efficiency power supply with a silent fan profile. computer components		2026-11-06 10:41:53		2026-11-06 12:12:53		variable	publish	t	visible	Fully modular high-efficiency power supply with a silent fan profile.	7	Fully modular high-efficiency power supply with a silent fan profile.	DEMO-COREMAX-MODULAR-POWER-SUPPLY	\N	\N	\N	\N	t	t	279	f	f	[]	0	0	\N		t	60		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	4599		4920.93	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1627	ProBook 14 Laptop	demo-probook-14-laptop	probook 14 laptop slim everyday laptop with a bright 14-inch display and all-day battery. laptops & computers		2026-11-06 10:28:53		2026-11-06 12:12:53		variable	publish	f	visible	Slim everyday laptop with a bright 14-inch display and all-day battery.	6	Slim everyday laptop with a bright 14-inch display and all-day battery.	DEMO-PROBOOK-14-LAPTOP	\N	\N	\N	\N	t	t	316	f	f	[]	0	0	\N		t	103		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":844,"name":"Laptops & Computers"}]	["demo catalog","Laptops & Computers"]	{"thumbnail":"products/demo-catalog/laptop.jpg","other_images":["products/demo-catalog/laptop.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	32999		36463.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1628	Creator 16 Laptop	demo-creator-16-laptop	creator 16 laptop large-screen performance laptop for design, editing and development. laptops & computers		2026-11-06 10:15:53		2026-11-06 12:12:53		variable	publish	f	visible	Large-screen performance laptop for design, editing and development.	0	Large-screen performance laptop for design, editing and development.	DEMO-CREATOR-16-LAPTOP	\N	\N	\N	\N	f	t	353	f	f	[]	0	0	\N		t	42		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":844,"name":"Laptops & Computers"}]	["demo catalog","Laptops & Computers"]	{"thumbnail":"products/demo-catalog/laptop.jpg","other_images":["products/demo-catalog/laptop.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	58999		61063.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1629	Mini Office PC	demo-mini-office-pc	mini office pc compact desktop computer with quiet cooling and flexible connectivity. laptops & computers		2026-11-06 10:02:53		2026-11-06 12:12:53		variable	publish	f	visible	Compact desktop computer with quiet cooling and flexible connectivity.	9	Compact desktop computer with quiet cooling and flexible connectivity.	DEMO-MINI-OFFICE-PC	\N	\N	\N	\N	t	t	390	f	f	[]	0	0	\N		t	29		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":844,"name":"Laptops & Computers"}]	["demo catalog","Laptops & Computers"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	17999		18628.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1630	Curved Gaming Monitor	demo-curved-gaming-monitor	curved gaming monitor immersive curved monitor with fast refresh and adaptive synchronization. laptops & computers		2026-11-06 09:49:53		2026-11-06 12:12:53		variable	publish	f	visible	Immersive curved monitor with fast refresh and adaptive synchronization.	0	Immersive curved monitor with fast refresh and adaptive synchronization.	DEMO-CURVED-GAMING-MONITOR	\N	\N	\N	\N	f	t	427	f	f	[]	0	0	\N		t	51		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":844,"name":"Laptops & Computers"}]	["demo catalog","Laptops & Computers"]	{"thumbnail":"products/demo-catalog/monitor.jpg","other_images":["products/demo-catalog/monitor.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	12499		12936.47	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1631	4K Creator Monitor	demo-4k-creator-monitor	4k creator monitor color-accurate 4k display with usb-c connectivity for creative work. laptops & computers		2026-11-06 09:36:53		2026-11-06 12:12:53		variable	publish	f	visible	Color-accurate 4K display with USB-C connectivity for creative work.	5	Color-accurate 4K display with USB-C connectivity for creative work.	DEMO-4K-CREATOR-MONITOR	\N	\N	\N	\N	t	t	464	f	f	[]	0	0	\N		t	104		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":844,"name":"Laptops & Computers"}]	["demo catalog","Laptops & Computers"]	{"thumbnail":"products/demo-catalog/monitor.jpg","other_images":["products/demo-catalog/monitor.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	15499		17126.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1632	Mechanical Gaming Keyboard	demo-mechanical-gaming-keyboard	mechanical gaming keyboard hot-swappable mechanical keyboard with per-key lighting and media controls. gaming accessories		2026-11-06 09:23:53		2026-11-06 12:12:53		variable	publish	f	visible	Hot-swappable mechanical keyboard with per-key lighting and media controls.	15	Hot-swappable mechanical keyboard with per-key lighting and media controls.	DEMO-MECHANICAL-GAMING-KEYBOARD	\N	\N	\N	\N	t	t	41	f	f	[]	0	0	\N		t	78		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":845,"name":"Gaming Accessories"}]	["demo catalog","Gaming Accessories"]	{"thumbnail":"products/demo-catalog/keyboard.jpg","other_images":["products/demo-catalog/keyboard.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2299		2540.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1633	Precision Wireless Mouse	demo-precision-wireless-mouse	precision wireless mouse lightweight wireless mouse with adjustable sensitivity and long battery life. gaming accessories		2026-11-06 09:10:53		2026-11-06 12:12:53		variable	publish	t	visible	Lightweight wireless mouse with adjustable sensitivity and long battery life.	0	Lightweight wireless mouse with adjustable sensitivity and long battery life.	DEMO-PRECISION-WIRELESS-MOUSE	\N	\N	\N	\N	f	t	78	f	f	[]	0	0	\N		t	87		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":845,"name":"Gaming Accessories"}]	["demo catalog","Gaming Accessories"]	{"thumbnail":"products/demo-catalog/mouse.jpg","other_images":["products/demo-catalog/mouse.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1399		1545.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1635	Pro USB Microphone	demo-pro-usb-microphone	pro usb microphone usb condenser microphone for streaming, meetings and podcast recording. gaming accessories		2026-11-06 08:44:53		2026-11-06 12:12:53		variable	publish	f	visible	USB condenser microphone for streaming, meetings and podcast recording.	0	USB condenser microphone for streaming, meetings and podcast recording.	DEMO-PRO-USB-MICROPHONE	\N	\N	\N	\N	f	t	152	f	f	[]	0	0	\N		t	70		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":845,"name":"Gaming Accessories"}]	["demo catalog","Gaming Accessories"]	{"thumbnail":"products/demo-catalog/microphone.jpg","other_images":["products/demo-catalog/microphone.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2799		3092.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1636	Wireless Game Controller	demo-wireless-game-controller	wireless game controller low-latency controller with responsive triggers and textured grips. gaming accessories		2026-11-06 08:31:53		2026-11-06 12:12:53		variable	publish	f	visible	Low-latency controller with responsive triggers and textured grips.	8	Low-latency controller with responsive triggers and textured grips.	DEMO-WIRELESS-GAME-CONTROLLER	\N	\N	\N	\N	t	t	189	f	f	[]	0	0	\N		t	75		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":845,"name":"Gaming Accessories"}]	["demo catalog","Gaming Accessories"]	{"thumbnail":"products/demo-catalog/controller.jpg","other_images":["products/demo-catalog/controller.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1699		1817.93	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1637	Nova 5G Smartphone	demo-nova-5g-smartphone	nova 5g smartphone fast 5g smartphone with a bright oled display and versatile cameras. phones & tablets		2026-11-06 08:18:53		2026-11-06 12:12:53		variable	publish	f	visible	Fast 5G smartphone with a bright OLED display and versatile cameras.	7	Fast 5G smartphone with a bright OLED display and versatile cameras.	DEMO-NOVA-5G-SMARTPHONE	\N	\N	\N	\N	t	t	226	f	f	[]	0	0	\N		t	88		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":846,"name":"Phones & Tablets"}]	["demo catalog","Phones & Tablets"]	{"thumbnail":"products/demo-catalog/phone.jpg","other_images":["products/demo-catalog/phone.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	21999		24308.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1638	Compact Android Phone	demo-compact-android-phone	compact android phone pocket-friendly smartphone with reliable battery life and dual sim support. phones & tablets		2026-11-06 08:05:53		2026-11-06 12:12:53		variable	publish	f	visible	Pocket-friendly smartphone with reliable battery life and dual SIM support.	0	Pocket-friendly smartphone with reliable battery life and dual SIM support.	DEMO-COMPACT-ANDROID-PHONE	\N	\N	\N	\N	f	t	263	f	f	[]	0	0	\N		t	97		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":846,"name":"Phones & Tablets"}]	["demo catalog","Phones & Tablets"]	{"thumbnail":"products/demo-catalog/phone.jpg","other_images":["products/demo-catalog/phone.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	10999		12153.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1639	AirTab 11 Tablet	demo-airtab-11-tablet	airtab 11 tablet thin 11-inch tablet for work, entertainment and sketching. phones & tablets		2026-11-06 07:52:53		2026-11-06 12:12:53		variable	publish	f	visible	Thin 11-inch tablet for work, entertainment and sketching.	5	Thin 11-inch tablet for work, entertainment and sketching.	DEMO-AIRTAB-11-TABLET	\N	\N	\N	\N	t	t	300	f	f	[]	0	0	\N		t	106		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":846,"name":"Phones & Tablets"}]	["demo catalog","Phones & Tablets"]	{"thumbnail":"products/demo-catalog/tablet.jpg","other_images":["products/demo-catalog/tablet.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	18499		20441.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1640	Active Smartwatch	demo-active-smartwatch	active smartwatch fitness-focused smartwatch with heart-rate tracking and notifications. phones & tablets		2026-11-06 07:39:53		2026-11-06 12:12:53		variable	publish	t	visible	Fitness-focused smartwatch with heart-rate tracking and notifications.	0	Fitness-focused smartwatch with heart-rate tracking and notifications.	DEMO-ACTIVE-SMARTWATCH	\N	\N	\N	\N	f	t	337	f	f	[]	0	0	\N		t	127		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":846,"name":"Phones & Tablets"}]	["demo catalog","Phones & Tablets"]	{"thumbnail":"products/demo-catalog/watch.jpg","other_images":["products/demo-catalog/watch.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	4999		5873.83	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1641	65W GaN Fast Charger	demo-65w-gan-fast-charger	65w gan fast charger compact multi-port fast charger for phones, tablets and laptops. phones & tablets		2026-11-06 07:26:53		2026-11-06 12:12:53		variable	publish	f	visible	Compact multi-port fast charger for phones, tablets and laptops.	12	Compact multi-port fast charger for phones, tablets and laptops.	DEMO-65W-GAN-FAST-CHARGER	\N	\N	\N	\N	t	t	374	f	f	[]	0	0	\N		t	89		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":846,"name":"Phones & Tablets"}]	["demo catalog","Phones & Tablets"]	{"thumbnail":"products/demo-catalog/charger.jpg","other_images":["products/demo-catalog/charger.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1299		1435.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1642	Studio ANC Headphones	demo-studio-anc-headphones	studio anc headphones wireless headphones with active noise cancellation and detailed sound. audio & cameras		2026-11-06 07:13:53		2026-11-06 12:12:53		variable	publish	f	visible	Wireless headphones with active noise cancellation and detailed sound.	10	Wireless headphones with active noise cancellation and detailed sound.	DEMO-STUDIO-ANC-HEADPHONES	\N	\N	\N	\N	t	t	411	f	f	[]	0	0	\N		t	63		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":847,"name":"Audio & Cameras"}]	["demo catalog","Audio & Cameras"]	{"thumbnail":"products/demo-catalog/headset.jpg","other_images":["products/demo-catalog/headset.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	4599		4920.93	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1644	Mirrorless Travel Camera	demo-mirrorless-travel-camera	mirrorless travel camera compact interchangeable-lens camera for sharp travel photos and video. audio & cameras		2026-11-06 06:47:53		2026-11-06 12:12:53		variable	publish	f	visible	Compact interchangeable-lens camera for sharp travel photos and video.	6	Compact interchangeable-lens camera for sharp travel photos and video.	DEMO-MIRRORLESS-TRAVEL-CAMERA	\N	\N	\N	\N	t	t	25	f	f	[]	0	0	\N		t	81		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":847,"name":"Audio & Cameras"}]	["demo catalog","Audio & Cameras"]	{"thumbnail":"products/demo-catalog/camera.jpg","other_images":["products/demo-catalog/camera.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	34999		38673.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1645	4K Action Camera	demo-4k-action-camera	4k action camera rugged action camera with stabilization and waterproof housing. audio & cameras		2026-11-06 06:34:53		2026-11-06 12:12:53		variable	publish	f	visible	Rugged action camera with stabilization and waterproof housing.	0	Rugged action camera with stabilization and waterproof housing.	DEMO-4K-ACTION-CAMERA	\N	\N	\N	\N	f	t	62	f	f	[]	0	0	\N		t	31		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":847,"name":"Audio & Cameras"}]	["demo catalog","Audio & Cameras"]	{"thumbnail":"products/demo-catalog/camera.jpg","other_images":["products/demo-catalog/camera.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	6999		7243.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1646	Pocket Camera Drone	demo-pocket-camera-drone	pocket camera drone foldable drone with stabilized video and intelligent flight modes. audio & cameras		2026-11-06 06:21:53		2026-11-06 12:12:53		variable	publish	f	visible	Foldable drone with stabilized video and intelligent flight modes.	5	Foldable drone with stabilized video and intelligent flight modes.	DEMO-POCKET-CAMERA-DRONE	\N	\N	\N	\N	t	t	99	f	f	[]	0	0	\N		t	99		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":847,"name":"Audio & Cameras"}]	["demo catalog","Audio & Cameras"]	{"thumbnail":"products/demo-catalog/drone.jpg","other_images":["products/demo-catalog/drone.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	18999		20993.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1647	Barista Coffee Maker	demo-barista-coffee-maker	barista coffee maker countertop coffee maker with programmable brewing and milk frothing. home & kitchen		2026-11-06 06:08:53		2026-11-06 12:12:53		variable	publish	t	visible	Countertop coffee maker with programmable brewing and milk frothing.	9	Countertop coffee maker with programmable brewing and milk frothing.	DEMO-BARISTA-COFFEE-MAKER	\N	\N	\N	\N	t	t	136	f	f	[]	0	0	\N		t	73		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":848,"name":"Home & Kitchen"}]	["demo catalog","Home & Kitchen"]	{"thumbnail":"products/demo-catalog/coffee.jpg","other_images":["products/demo-catalog/coffee.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	6499		7181.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1648	PowerBlend Blender	demo-powerblend-blender	powerblend blender high-speed blender for smoothies, soups and everyday preparation. home & kitchen		2026-11-06 05:55:53		2026-11-06 12:12:53		variable	publish	f	visible	High-speed blender for smoothies, soups and everyday preparation.	0	High-speed blender for smoothies, soups and everyday preparation.	DEMO-POWERBLEND-BLENDER	\N	\N	\N	\N	f	t	173	f	f	[]	0	0	\N		t	82		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":848,"name":"Home & Kitchen"}]	["demo catalog","Home & Kitchen"]	{"thumbnail":"products/demo-catalog/blender.jpg","other_images":["products/demo-catalog/blender.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2799		3092.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1649	Digital Air Fryer	demo-digital-air-fryer	digital air fryer digital air fryer with preset programs and easy-clean basket. home & kitchen		2026-11-06 05:42:53		2026-11-06 12:12:53		variable	publish	f	visible	Digital air fryer with preset programs and easy-clean basket.	11	Digital air fryer with preset programs and easy-clean basket.	DEMO-DIGITAL-AIR-FRYER	\N	\N	\N	\N	t	t	210	f	f	[]	0	0	\N		t	91		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":848,"name":"Home & Kitchen"}]	["demo catalog","Home & Kitchen"]	{"thumbnail":"products/demo-catalog/cookware.jpg","other_images":["products/demo-catalog/cookware.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	4499		4971.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1650	Ceramic Cookware Set	demo-ceramic-cookware-set	ceramic cookware set non-stick ceramic cookware set for efficient everyday meals. home & kitchen		2026-11-06 05:29:53		2026-11-06 12:12:53		variable	publish	f	visible	Non-stick ceramic cookware set for efficient everyday meals.	0	Non-stick ceramic cookware set for efficient everyday meals.	DEMO-CERAMIC-COOKWARE-SET	\N	\N	\N	\N	f	t	247	f	f	[]	0	0	\N		t	100		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":848,"name":"Home & Kitchen"}]	["demo catalog","Home & Kitchen"]	{"thumbnail":"products/demo-catalog/cookware.jpg","other_images":["products/demo-catalog/cookware.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	3899		4308.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1651	Cordless Stick Vacuum	demo-cordless-stick-vacuum	cordless stick vacuum light cordless vacuum with multiple tools and washable filtration. home & kitchen		2026-11-06 05:16:53		2026-11-06 12:12:53		variable	publish	f	visible	Light cordless vacuum with multiple tools and washable filtration.	8	Light cordless vacuum with multiple tools and washable filtration.	DEMO-CORDLESS-STICK-VACUUM	\N	\N	\N	\N	t	t	284	f	f	[]	0	0	\N		t	74		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":848,"name":"Home & Kitchen"}]	["demo catalog","Home & Kitchen"]	{"thumbnail":"products/demo-catalog/vacuum.jpg","other_images":["products/demo-catalog/vacuum.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	7299		8065.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1653	Modular Living Room Sofa	demo-modular-living-room-sofa	modular living room sofa flexible modular sofa with deep cushions and durable fabric. furniture & decor		2026-11-06 04:50:53		2026-11-06 12:12:53		variable	publish	f	visible	Flexible modular sofa with deep cushions and durable fabric.	5	Flexible modular sofa with deep cushions and durable fabric.	DEMO-MODULAR-LIVING-ROOM-SOFA	\N	\N	\N	\N	t	t	358	f	f	[]	0	0	\N		t	92		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":849,"name":"Furniture & Decor"}]	["demo catalog","Furniture & Decor"]	{"thumbnail":"products/demo-catalog/sofa.jpg","other_images":["products/demo-catalog/sofa.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	18999		20993.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1654	Minimal Desk Lamp	demo-minimal-desk-lamp	minimal desk lamp adjustable led desk lamp with touch dimming and warm light modes. furniture & decor		2026-11-06 04:37:53		2026-11-06 12:12:53		variable	publish	t	visible	Adjustable LED desk lamp with touch dimming and warm light modes.	0	Adjustable LED desk lamp with touch dimming and warm light modes.	DEMO-MINIMAL-DESK-LAMP	\N	\N	\N	\N	f	t	395	f	f	[]	0	0	\N		t	106		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":849,"name":"Furniture & Decor"}]	["demo catalog","Furniture & Decor"]	{"thumbnail":"products/demo-catalog/lamp.jpg","other_images":["products/demo-catalog/lamp.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1199		1408.83	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1655	Indoor Plant Stand	demo-indoor-plant-stand	indoor plant stand modern plant stand that adds height and texture to indoor greenery. furniture & decor		2026-11-06 04:24:53		2026-11-06 12:12:53		variable	publish	f	visible	Modern plant stand that adds height and texture to indoor greenery.	10	Modern plant stand that adds height and texture to indoor greenery.	DEMO-INDOOR-PLANT-STAND	\N	\N	\N	\N	t	t	432	f	f	[]	0	0	\N		t	110		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":849,"name":"Furniture & Decor"}]	["demo catalog","Furniture & Decor"]	{"thumbnail":"products/demo-catalog/plant.jpg","other_images":["products/demo-catalog/plant.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	899		993.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1656	Adjustable Dumbbell Set	demo-adjustable-dumbbell-set	adjustable dumbbell set space-saving adjustable dumbbells for strength training at home. sports & outdoors		2026-11-06 04:11:53		2026-11-06 12:12:53		variable	publish	f	visible	Space-saving adjustable dumbbells for strength training at home.	8	Space-saving adjustable dumbbells for strength training at home.	DEMO-ADJUSTABLE-DUMBBELL-SET	\N	\N	\N	\N	t	t	469	f	f	[]	0	0	\N		t	63		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":850,"name":"Sports & Outdoors"}]	["demo catalog","Sports & Outdoors"]	{"thumbnail":"products/demo-catalog/dumbbells.jpg","other_images":["products/demo-catalog/dumbbells.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	7999		8278.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1657	Premium Yoga Mat	demo-premium-yoga-mat	premium yoga mat cushioned non-slip yoga mat with carrying strap. sports & outdoors		2026-11-06 03:58:53		2026-11-06 12:12:53		variable	publish	f	visible	Cushioned non-slip yoga mat with carrying strap.	0	Cushioned non-slip yoga mat with carrying strap.	DEMO-PREMIUM-YOGA-MAT	\N	\N	\N	\N	f	t	46	f	f	[]	0	0	\N		t	164		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":850,"name":"Sports & Outdoors"}]	["demo catalog","Sports & Outdoors"]	{"thumbnail":"products/demo-catalog/yoga.jpg","other_images":["products/demo-catalog/yoga.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	999		1173.83	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1658	City Commuter Bicycle	demo-city-commuter-bicycle	city commuter bicycle comfortable city bicycle with practical gearing and reliable brakes. sports & outdoors		2026-11-06 03:45:53		2026-11-06 12:12:53		variable	publish	f	visible	Comfortable city bicycle with practical gearing and reliable brakes.	6	Comfortable city bicycle with practical gearing and reliable brakes.	DEMO-CITY-COMMUTER-BICYCLE	\N	\N	\N	\N	t	t	83	f	f	[]	0	0	\N		t	102		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":850,"name":"Sports & Outdoors"}]	["demo catalog","Sports & Outdoors"]	{"thumbnail":"products/demo-catalog/bike.jpg","other_images":["products/demo-catalog/bike.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	13499		14916.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1659	Match Football	demo-match-football	match football durable match-style football with consistent flight and touch. sports & outdoors		2026-11-06 03:32:53		2026-11-06 12:12:53		variable	publish	f	visible	Durable match-style football with consistent flight and touch.	0	Durable match-style football with consistent flight and touch.	DEMO-MATCH-FOOTBALL	\N	\N	\N	\N	f	t	120	f	f	[]	0	0	\N		t	111		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":850,"name":"Sports & Outdoors"}]	["demo catalog","Sports & Outdoors"]	{"thumbnail":"products/demo-catalog/football.jpg","other_images":["products/demo-catalog/football.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	749		827.65	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1660	Trail Hiking Backpack	demo-trail-hiking-backpack	trail hiking backpack supportive hiking backpack with ventilated back panel and rain cover. sports & outdoors		2026-11-06 03:19:53		2026-11-06 12:12:53		variable	publish	f	visible	Supportive hiking backpack with ventilated back panel and rain cover.	12	Supportive hiking backpack with ventilated back panel and rain cover.	DEMO-TRAIL-HIKING-BACKPACK	\N	\N	\N	\N	t	t	157	f	f	[]	0	0	\N		t	152		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":850,"name":"Sports & Outdoors"}]	["demo catalog","Sports & Outdoors"]	{"thumbnail":"products/demo-catalog/backpack.jpg","other_images":["products/demo-catalog/backpack.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1899		2231.33	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1662	Vitamin C Skin Serum	demo-vitamin-c-skin-serum	vitamin c skin serum lightweight brightening serum with vitamin c and hydrating ingredients. beauty & personal care		2026-11-06 02:53:53		2026-11-06 12:12:53		variable	publish	f	visible	Lightweight brightening serum with vitamin C and hydrating ingredients.	0	Lightweight brightening serum with vitamin C and hydrating ingredients.	DEMO-VITAMIN-C-SKIN-SERUM	\N	\N	\N	\N	f	t	231	f	f	[]	0	0	\N		t	55		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":851,"name":"Beauty & Personal Care"}]	["demo catalog","Beauty & Personal Care"]	{"thumbnail":"products/demo-catalog/skincare.jpg","other_images":["products/demo-catalog/skincare.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	849		878.72	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1663	Professional Makeup Palette	demo-professional-makeup-palette	professional makeup palette versatile makeup palette with blendable everyday and evening shades. beauty & personal care		2026-11-06 02:40:53		2026-11-06 12:12:53		variable	publish	f	visible	Versatile makeup palette with blendable everyday and evening shades.	15	Versatile makeup palette with blendable everyday and evening shades.	DEMO-PROFESSIONAL-MAKEUP-PALETTE	\N	\N	\N	\N	t	t	268	f	f	[]	0	0	\N		t	77		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":851,"name":"Beauty & Personal Care"}]	["demo catalog","Beauty & Personal Care"]	{"thumbnail":"products/demo-catalog/makeup.jpg","other_images":["products/demo-catalog/makeup.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1299		1435.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1664	Ionic Hair Dryer	demo-ionic-hair-dryer	ionic hair dryer fast-drying hair dryer with ionic care and multiple heat settings. beauty & personal care		2026-11-06 02:27:53		2026-11-06 12:12:53		variable	publish	f	visible	Fast-drying hair dryer with ionic care and multiple heat settings.	0	Fast-drying hair dryer with ionic care and multiple heat settings.	DEMO-IONIC-HAIR-DRYER	\N	\N	\N	\N	f	t	305	f	f	[]	0	0	\N		t	86		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":851,"name":"Beauty & Personal Care"}]	["demo catalog","Beauty & Personal Care"]	{"thumbnail":"products/demo-catalog/haircare.jpg","other_images":["products/demo-catalog/haircare.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1799		1987.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1665	Electric Grooming Kit	demo-electric-grooming-kit	electric grooming kit rechargeable grooming kit with precision attachments and travel case. beauty & personal care		2026-11-06 02:14:53		2026-11-06 12:12:53		variable	publish	f	visible	Rechargeable grooming kit with precision attachments and travel case.	8	Rechargeable grooming kit with precision attachments and travel case.	DEMO-ELECTRIC-GROOMING-KIT	\N	\N	\N	\N	t	t	342	f	f	[]	0	0	\N		t	95		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":851,"name":"Beauty & Personal Care"}]	["demo catalog","Beauty & Personal Care"]	{"thumbnail":"products/demo-catalog/grooming.jpg","other_images":["products/demo-catalog/grooming.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1499		1656.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1666	Design Thinking Book Set	demo-design-thinking-book-set	design thinking book set curated set of practical books for design, business and creative problem solving. books & toys		2026-11-06 02:01:53		2026-11-06 12:12:53		variable	publish	f	visible	Curated set of practical books for design, business and creative problem solving.	0	Curated set of practical books for design, business and creative problem solving.	DEMO-DESIGN-THINKING-BOOK-SET	\N	\N	\N	\N	f	t	379	f	f	[]	0	0	\N		t	73		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":852,"name":"Books & Toys"}]	["demo catalog","Books & Toys"]	{"thumbnail":"products/demo-catalog/books.jpg","other_images":["products/demo-catalog/books.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1199		1240.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1667	STEM Building Blocks	demo-stem-building-blocks	stem building blocks creative building set that introduces mechanisms, structures and problem solving. books & toys		2026-11-06 01:48:53		2026-11-06 12:12:53		variable	publish	f	visible	Creative building set that introduces mechanisms, structures and problem solving.	10	Creative building set that introduces mechanisms, structures and problem solving.	DEMO-STEM-BUILDING-BLOCKS	\N	\N	\N	\N	t	t	416	f	f	[]	0	0	\N		t	78		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":852,"name":"Books & Toys"}]	["demo catalog","Books & Toys"]	{"thumbnail":"products/demo-catalog/toys.jpg","other_images":["products/demo-catalog/toys.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	999		1103.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1668	Remote Control Rally Car	demo-remote-control-rally-car	remote control rally car responsive remote-control car with rechargeable battery and rugged tires. books & toys		2026-11-06 01:35:53		2026-11-06 12:12:53		variable	publish	t	visible	Responsive remote-control car with rechargeable battery and rugged tires.	0	Responsive remote-control car with rechargeable battery and rugged tires.	DEMO-REMOTE-CONTROL-RALLY-CAR	\N	\N	\N	\N	f	t	453	f	f	[]	0	0	\N		t	87		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":852,"name":"Books & Toys"}]	["demo catalog","Books & Toys"]	{"thumbnail":"products/demo-catalog/toycar.jpg","other_images":["products/demo-catalog/toycar.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1399		1545.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1669	Family Strategy Board Game	demo-family-strategy-board-game	family strategy board game easy-to-learn strategy game designed for lively family game nights. books & toys		2026-11-06 01:22:53		2026-11-06 12:12:53		variable	publish	f	visible	Easy-to-learn strategy game designed for lively family game nights.	5	Easy-to-learn strategy game designed for lively family game nights.	DEMO-FAMILY-STRATEGY-BOARD-GAME	\N	\N	\N	\N	t	t	30	f	f	[]	0	0	\N		t	69		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":852,"name":"Books & Toys"}]	["demo catalog","Books & Toys"]	{"thumbnail":"products/demo-catalog/boardgame.jpg","other_images":["products/demo-catalog/boardgame.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	749		775.22	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1619	Nebula RTX Graphics Card	demo-nebula-rtx-graphics-card	nebula rtx graphics card triple-fan graphics card for high-refresh gaming and creative workloads. computer components		2026-11-06 12:12:53		2026-11-06 12:12:53		variable	publish	t	visible	Triple-fan graphics card for high-refresh gaming and creative workloads.	8	Triple-fan graphics card for high-refresh gaming and creative workloads.	DEMO-NEBULA-RTX-GRAPHICS-CARD	\N	\N	\N	\N	t	t	20	f	f	[]	0	0	\N		t	66		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/gpu.jpg","other_images":["products/demo-catalog/gpu.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	28999		32043.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1622	Pulse Gaming Motherboard	demo-pulse-gaming-motherboard	pulse gaming motherboard feature-rich motherboard with fast networking, multiple m.2 slots and rgb headers. computer components		2026-11-06 11:33:53		2026-11-06 12:12:53		variable	publish	f	visible	Feature-rich motherboard with fast networking, multiple M.2 slots and RGB headers.	10	Feature-rich motherboard with fast networking, multiple M.2 slots and RGB headers.	DEMO-PULSE-GAMING-MOTHERBOARD	\N	\N	\N	\N	t	t	131	f	f	[]	0	0	\N		t	50		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	8499		8796.47	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1623	Titan DDR5 Memory Kit	demo-titan-ddr5-memory-kit	titan ddr5 memory kit low-latency ddr5 memory kit with aluminum heat spreaders. computer components		2026-11-06 11:20:53		2026-11-06 12:12:53		variable	publish	f	visible	Low-latency DDR5 memory kit with aluminum heat spreaders.	0	Low-latency DDR5 memory kit with aluminum heat spreaders.	DEMO-TITAN-DDR5-MEMORY-KIT	\N	\N	\N	\N	f	t	168	f	f	[]	0	0	\N		t	102		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	3999		4418.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1624	Swift NVMe SSD	demo-swift-nvme-ssd	swift nvme ssd fast nvme solid-state drive for quick boot, game and application loading. computer components		2026-11-06 11:07:53		2026-11-06 12:12:53		variable	publish	f	visible	Fast NVMe solid-state drive for quick boot, game and application loading.	12	Fast NVMe solid-state drive for quick boot, game and application loading.	DEMO-SWIFT-NVME-SSD	\N	\N	\N	\N	t	t	205	f	f	[]	0	0	\N		t	99		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2499		2673.93	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1625	Frost RGB CPU Cooler	demo-frost-rgb-cpu-cooler	frost rgb cpu cooler quiet tower cooler with addressable lighting and broad socket support. computer components		2026-11-06 10:54:53		2026-11-06 12:12:53		variable	publish	f	visible	Quiet tower cooler with addressable lighting and broad socket support.	0	Quiet tower cooler with addressable lighting and broad socket support.	DEMO-FROST-RGB-CPU-COOLER	\N	\N	\N	\N	f	t	242	f	f	[]	0	0	\N		t	85		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":843,"name":"Computer Components"}]	["demo catalog","Computer Components"]	{"thumbnail":"products/demo-catalog/pc.jpg","other_images":["products/demo-catalog/pc.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2199		2429.9	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1634	Surround Gaming Headset	demo-surround-gaming-headset	surround gaming headset comfortable over-ear headset with clear voice chat and spatial sound. gaming accessories		2026-11-06 08:57:53		2026-11-06 12:12:53		variable	publish	f	visible	Comfortable over-ear headset with clear voice chat and spatial sound.	10	Comfortable over-ear headset with clear voice chat and spatial sound.	DEMO-SURROUND-GAMING-HEADSET	\N	\N	\N	\N	t	t	115	f	f	[]	0	0	\N		t	96		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":845,"name":"Gaming Accessories"}]	["demo catalog","Gaming Accessories"]	{"thumbnail":"products/demo-catalog/headset.jpg","other_images":["products/demo-catalog/headset.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	1899		2098.4	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1643	Portable Bluetooth Speaker	demo-portable-bluetooth-speaker	portable bluetooth speaker water-resistant portable speaker with rich sound and strong battery life. audio & cameras		2026-11-06 07:00:53		2026-11-06 12:12:53		variable	publish	f	visible	Water-resistant portable speaker with rich sound and strong battery life.	0	Water-resistant portable speaker with rich sound and strong battery life.	DEMO-PORTABLE-BLUETOOTH-SPEAKER	\N	\N	\N	\N	f	t	448	f	f	[]	0	0	\N		t	150		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":847,"name":"Audio & Cameras"}]	["demo catalog","Audio & Cameras"]	{"thumbnail":"products/demo-catalog/speaker.jpg","other_images":["products/demo-catalog/speaker.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2199		2583.83	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1652	Nordic Lounge Chair	demo-nordic-lounge-chair	nordic lounge chair comfortable lounge chair with a clean scandinavian silhouette. furniture & decor		2026-11-06 05:03:53		2026-11-06 12:12:53		variable	publish	f	visible	Comfortable lounge chair with a clean Scandinavian silhouette.	0	Comfortable lounge chair with a clean Scandinavian silhouette.	DEMO-NORDIC-LOUNGE-CHAIR	\N	\N	\N	\N	f	t	321	f	f	[]	0	0	\N		t	78		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":849,"name":"Furniture & Decor"}]	["demo catalog","Furniture & Decor"]	{"thumbnail":"products/demo-catalog/chair.jpg","other_images":["products/demo-catalog/chair.jpg"],"natural_images":[]}	["Color","Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	5999		6418.93	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
1661	Velvet Eau de Parfum	demo-velvet-eau-de-parfum	velvet eau de parfum warm modern fragrance with floral, amber and soft woody notes. beauty & personal care		2026-11-06 03:06:53		2026-11-06 12:12:53		variable	publish	t	visible	Warm modern fragrance with floral, amber and soft woody notes.	10	Warm modern fragrance with floral, amber and soft woody notes.	DEMO-VELVET-EAU-DE-PARFUM	\N	\N	\N	\N	t	t	194	f	f	[]	0	0	\N		t	33		f	f	0	f	[]	t	t		0	t		0	[]	[]	0		[{"id":851,"name":"Beauty & Personal Care"}]	["demo catalog","Beauty & Personal Care"]	{"thumbnail":"products/demo-catalog/perfume.jpg","other_images":["products/demo-catalog/perfume.jpg"],"natural_images":[]}	["Size"]	[]	[]	[]	0	[]	[]	instock	t	t		\N	f	[]	f	[]	en	2199		2275.97	2026-11-06 11:57:06	2026-11-06 12:12:53	1	10	physical	\N		approved	piece		both
\.


--
-- Data for Name: products_data_main; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.products_data_main (id, name, slug, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount, short_description, sku, price, regular_price, sale_price, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, price_html, related_ids, meta_data, stock_status, has_options, post_password, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, button_mode) FROM stdin;
\.


--
-- Data for Name: rate_limits; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.rate_limits (consumer_key, request_count, last_request_time) FROM stdin;
\.


--
-- Data for Name: referral_commissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.referral_commissions (id, referral_id, order_id, amount, status, approved_at, paid_at, clawed_back_at, clawback_reason, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: referrals; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.referrals (id, referrer_id, referred_id, status, qualifying_order_id, rejection_reason, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: refund_requests; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.refund_requests (id, order_id, customer_id, vendor_id, type, reason, description, status, admin_note, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: saved_addresses; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.saved_addresses (id, user_id, label, recipient_name, phone, governorate, city, street, additional_details, is_default, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: shops; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.shops (id, user_id, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, status, created_at, updated_at, remember_token) FROM stdin;
\.


--
-- Data for Name: storefront_price_ranges; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.storefront_price_ranges (id, scope, min_price, max_price, created_at, updated_at) FROM stdin;
1	global	32.00	58999.00	2026-11-06 14:18:21	2026-11-06 14:25:34
26	category:843	2199.00	28999.00	2026-11-06 14:25:34	2026-11-06 14:25:34
27	category:26	32.00	1250.00	2026-11-06 14:25:34	2026-11-06 14:25:34
28	category:23	637.50	2200.00	2026-11-06 14:25:34	2026-11-06 14:25:34
29	category:846	1299.00	21999.00	2026-11-06 14:25:34	2026-11-06 14:25:34
30	category:208	32.00	400.00	2026-11-06 14:25:34	2026-11-06 14:25:34
31	category:311	32.00	400.00	2026-11-06 14:25:34	2026-11-06 14:25:34
32	category:24	32.00	400.00	2026-11-06 14:25:34	2026-11-06 14:25:34
33	category:19	32.00	520.00	2026-11-06 14:25:34	2026-11-06 14:25:34
34	category:25	32.00	1512.00	2026-11-06 14:25:34	2026-11-06 14:25:34
35	category:850	749.00	13499.00	2026-11-06 14:25:34	2026-11-06 14:25:34
36	category:848	2799.00	7299.00	2026-11-06 14:25:34	2026-11-06 14:25:34
37	category:29	32.00	699.00	2026-11-06 14:25:34	2026-11-06 14:25:34
38	category:849	899.00	18999.00	2026-11-06 14:25:34	2026-11-06 14:25:34
39	category:852	749.00	1399.00	2026-11-06 14:25:34	2026-11-06 14:25:34
40	category:30	32.00	550.00	2026-11-06 14:25:34	2026-11-06 14:25:34
41	category:21	32.00	455.00	2026-11-06 14:25:34	2026-11-06 14:25:34
42	category:20	32.00	400.00	2026-11-06 14:25:34	2026-11-06 14:25:34
43	category:22	32.00	400.00	2026-11-06 14:25:34	2026-11-06 14:25:34
44	category:28	32.00	2100.00	2026-11-06 14:25:34	2026-11-06 14:25:34
45	category:847	2199.00	34999.00	2026-11-06 14:25:34	2026-11-06 14:25:34
46	category:845	1399.00	2799.00	2026-11-06 14:25:34	2026-11-06 14:25:34
47	category:844	12499.00	58999.00	2026-11-06 14:25:34	2026-11-06 14:25:34
48	category:18	32.00	2526.00	2026-11-06 14:25:34	2026-11-06 14:25:34
49	category:851	849.00	2199.00	2026-11-06 14:25:34	2026-11-06 14:25:34
\.


--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tags (id, name, slug, description, count, is_visible, _links, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: time_line_configs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.time_line_configs (id, lang_code, config_json) FROM stdin;
\.


--
-- Data for Name: user_notes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_notes (id, user_id, date_created, note, customer_note, created_at, updated_at, date_created_gmt, order_id) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, user_login, username, user_nicename, display_name, first_name, last_name, url, avatar, phone, role, nicename, registered, firstname, lastname, description, capabilities, shipping, registration_method, is_phone_verified, is_blocked, provider, provider_id, referral_code, referred_by, referral_lock_ip) FROM stdin;
2702	Ramo Demo Admin	admin@ramostore.test	2026-10-12 20:06:42	$2y$12$Vpb73nOx8wwQOGZ/wurogO6xvmWAL4U8Itn8nM0kOXdV8xOTjkS82	\N	2026-10-12 20:06:42	2026-10-12 20:06:42	\N	\N	\N	\N	Ramo Demo	Admin	\N	\N		admin	ramo-demo-admin	2026-10-12 20:06:42	Ramo Demo	Admin		{"admin":true}	[]	email_password	f	f	\N	\N	W7JYG2RG	\N	127.0.0.1
2703	Demo Customer	customer@ramostore.test	2026-10-12 20:06:42	$2y$12$BF7cFKdmddF9Oobcw9NpjexyNI0.RIrCmyHkDmSshTYwuE2qWvLKq	\N	2026-10-12 20:06:42	2026-10-12 20:06:42	\N	\N	\N	\N	Demo	Customer	\N	\N	+20100000002	normal_user	demo-customer	2026-10-12 20:06:42	Demo	Customer		{"customer":true}	[]	email_password	f	f	\N	\N	RMICVYP5	\N	127.0.0.1
2800	Admin	adminramoui@gmail.com	2026-10-12 20:43:03	$2y$12$bEMw5rB4ugNTJOF1iAr7reqS0LVT9LO2Sg30LGf6tFhtKfQTAcWhW	\N	2026-10-12 20:43:03	2026-10-12 20:43:03	\N	\N	\N	\N	\N	\N	\N	\N		["admin"]								\N	f	f	\N	\N	\N	\N	\N
2897	Ramez Malak	\N	\N	$2y$12$frRClgnX4Mm20dPuSCjSj.EbcQPQu1d0dcE39gUuqUZ3VA89dst6i	\N	2026-11-06 10:54:30	2026-11-06 10:54:30	\N	\N	\N	\N	Ramez	Malak	\N	\N	+201211313312	["customer"]	ramez-malak-3312	2026-11-06 10:54:30	Ramez	Malak		{"customer":true}	[]	phone_otp	t	f	\N	\N	GMBVBXZI	\N	127.0.0.1
\.


--
-- Data for Name: vendor_users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_users (id, profile_image, first_name, last_name, phone, email, password, email_verified_at, remember_token, created_at, updated_at, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, bottom_banner, status, rating, rating_count, temporary_close, vacation_end_date, vacation_start_date, vacation_status, offer_banner, product_count, orders_count, minimum_order_amount, free_delivery_over_amount, free_delivery_status, sales_commission_percentage, auth_token, holder_name, account_no, bank_name, branch, free_delivery_features_status, free_delivery_responsibility, minimum_order_amount_by_seller) FROM stdin;
436	\N	Demo	Vendor	+20100000001	vendor@ramostore.test	$2y$12$08TDdqnVYnBU6EifJY3gT.plgd7JwrfAzAi4xrf/Sqps.Zm0hgnnG	2026-10-12 20:06:42	\N	2026-10-12 20:06:42	2026-10-12 20:06:42	Ramo Demo Boutique	Demo shop address	\N	\N	\N		approved	0	0	0	empty	empty	0	empty	3	0	0	0	0	0	0	not set	0	not set	not set	1	1	0
\.


--
-- Data for Name: version_config; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.version_config (id, supported_ver_from, supported_ver_to) FROM stdin;
\.


--
-- Data for Name: wishlists; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.wishlists (id, user_id, product_id, created_at) FROM stdin;
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

SELECT pg_catalog.setval('public.app_configs_id_seq', 161, true);


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

SELECT pg_catalog.setval('public.cart_items_id_seq', 712, true);


--
-- Name: categories2_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories2_id_seq', 852, true);


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

SELECT pg_catalog.setval('public.coupon_user_limits_id_seq', 122, true);


--
-- Name: coupons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.coupons_id_seq', 407, true);


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

SELECT pg_catalog.setval('public.idempotency_keys_id_seq', 183, true);


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.image_gallery_images_id_seq', 191, true);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 29, true);


--
-- Name: order_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_messages_id_seq', 3, true);


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_sub_orders_id_seq', 214, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orders_id_seq', 1199, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 331, true);


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_receipts_id_seq', 391, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: product_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_reviews_id_seq', 162, true);


--
-- Name: product_variations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_variations_id_seq', 2002, true);


--
-- Name: products_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_id_seq', 1669, true);


--
-- Name: products_data_main_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_main_id_seq', 1, false);


--
-- Name: referral_commissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referral_commissions_id_seq', 16, true);


--
-- Name: referrals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referrals_id_seq', 28, true);


--
-- Name: refund_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.refund_requests_id_seq', 145, true);


--
-- Name: saved_addresses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.saved_addresses_id_seq', 2, true);


--
-- Name: shops_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.shops_id_seq', 1, false);


--
-- Name: storefront_price_ranges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.storefront_price_ranges_id_seq', 49, true);


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

SELECT pg_catalog.setval('public.user_notes_id_seq', 114, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2897, true);


--
-- Name: vendor_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_users_id_seq', 454, true);


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
-- Name: referral_commissions referral_commissions_order_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referral_commissions
    ADD CONSTRAINT referral_commissions_order_id_unique UNIQUE (order_id);


--
-- Name: referral_commissions referral_commissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referral_commissions
    ADD CONSTRAINT referral_commissions_pkey PRIMARY KEY (id);


--
-- Name: referrals referrals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referrals
    ADD CONSTRAINT referrals_pkey PRIMARY KEY (id);


--
-- Name: referrals referrals_referred_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referrals
    ADD CONSTRAINT referrals_referred_id_unique UNIQUE (referred_id);


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
-- Name: storefront_price_ranges storefront_price_ranges_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storefront_price_ranges
    ADD CONSTRAINT storefront_price_ranges_pkey PRIMARY KEY (id);


--
-- Name: storefront_price_ranges storefront_price_ranges_scope_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.storefront_price_ranges
    ADD CONSTRAINT storefront_price_ranges_scope_unique UNIQUE (scope);


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
-- Name: users users_referral_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_referral_code_unique UNIQUE (referral_code);


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
-- Name: referral_commissions_referral_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commissions_referral_id_index ON public.referral_commissions USING btree (referral_id);


--
-- Name: referral_commissions_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commissions_status_index ON public.referral_commissions USING btree (status);


--
-- Name: referrals_referrer_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referrals_referrer_id_index ON public.referrals USING btree (referrer_id);


--
-- Name: referrals_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referrals_status_index ON public.referrals USING btree (status);


--
-- Name: saved_addresses_user_id_is_default_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saved_addresses_user_id_is_default_index ON public.saved_addresses USING btree (user_id, is_default);


--
-- Name: users_phone_unique_nonempty; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX users_phone_unique_nonempty ON public.users USING btree (phone) WHERE ((phone IS NOT NULL) AND (phone <> ''::text));


--
-- Name: users_referred_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_referred_by_index ON public.users USING btree (referred_by);


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

\unrestrict DHdWQwMqwybigpRaZO7yGk1difuE9nmiF466RkEzkNTEH58IMhpGhoDkBOn6l3P

