--
-- PostgreSQL database dump
--

\restrict GDHNwpREGVbP88UWRswqqsfM3NhpWviCd1ENTzhaRcOrwhdH6JSNH6swnJqDKjp

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


--
-- Name: prevent_referral_audit_mutation(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.prevent_referral_audit_mutation() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    RAISE EXCEPTION 'Referral financial audit events are immutable';
END;
$$;


--
-- Name: record_order_notification(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.record_order_notification() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.customer_id IS NULL OR NEW.customer_id <= 0 THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO order_notifications (customer_id, order_id, event)
        VALUES (NEW.customer_id, NEW.id, 'placed');
        RETURN NEW;
    END IF;

    IF NEW.status IS DISTINCT FROM OLD.status AND NULLIF(NEW.status, '') IS NOT NULL THEN
        INSERT INTO order_notifications (customer_id, order_id, event, status)
        VALUES (NEW.customer_id, NEW.id, 'order_status', NEW.status);
    END IF;

    IF NEW.payment_status IS DISTINCT FROM OLD.payment_status AND NULLIF(NEW.payment_status, '') IS NOT NULL THEN
        INSERT INTO order_notifications (customer_id, order_id, event, status)
        VALUES (NEW.customer_id, NEW.id, 'payment_status', NEW.payment_status);
    END IF;

    RETURN NEW;
END;
$$;


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
-- Name: email_verification_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.email_verification_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


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
-- Name: homepage_heroes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.homepage_heroes (
    id smallint NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    media_type character varying(5) DEFAULT 'image'::character varying NOT NULL,
    media_path character varying(255),
    poster_path character varying(255),
    headline character varying(255),
    subtext text,
    button_label character varying(255),
    button_link text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    headline_ar character varying(255),
    subtext_ar text,
    button_label_ar character varying(255)
);


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
-- Name: order_notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.order_notifications (
    id bigint NOT NULL,
    customer_id bigint NOT NULL,
    order_id bigint NOT NULL,
    event character varying(24) NOT NULL,
    status character varying(200),
    read_at timestamp(0) without time zone,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: order_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.order_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: order_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.order_notifications_id_seq OWNED BY public.order_notifications.id;


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
    general_order_status_override_at timestamp(0) without time zone,
    payment_due_at timestamp(0) without time zone,
    platform_payment_verified_at timestamp(0) without time zone,
    platform_payment_verified_by bigint
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
-- Name: referral_commission_audits; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.referral_commission_audits (
    id bigint NOT NULL,
    commission_id bigint NOT NULL,
    referral_id bigint NOT NULL,
    order_id bigint NOT NULL,
    actor_id bigint,
    action character varying(40) NOT NULL,
    old_status character varying(20),
    new_status character varying(20) NOT NULL,
    old_amount numeric(12,2),
    new_amount numeric(12,2) NOT NULL,
    reason character varying(255),
    metadata json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: referral_commission_audits_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.referral_commission_audits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: referral_commission_audits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.referral_commission_audits_id_seq OWNED BY public.referral_commission_audits.id;


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
-- Name: user_flags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_flags (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    created_by bigint,
    resolved_by bigint,
    application_id bigint,
    reason text NOT NULL,
    resolved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: user_flags_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_flags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_flags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_flags_id_seq OWNED BY public.user_flags.id;


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
-- Name: vendor_application_notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendor_application_notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    application_id bigint NOT NULL,
    status character varying(20) NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: vendor_application_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendor_application_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendor_application_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendor_application_notifications_id_seq OWNED BY public.vendor_application_notifications.id;


--
-- Name: vendor_application_reviews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendor_application_reviews (
    id bigint NOT NULL,
    application_id bigint NOT NULL,
    reviewed_by bigint,
    submission_number integer NOT NULL,
    decision character varying(20) NOT NULL,
    reason text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: vendor_application_reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendor_application_reviews_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendor_application_reviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendor_application_reviews_id_seq OWNED BY public.vendor_application_reviews.id;


--
-- Name: vendor_applications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendor_applications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    vendor_id bigint,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    personal_data text,
    marketplace_draft text,
    identity_document text,
    identity_mime character varying(50),
    rejection_reason text,
    submitted_at timestamp(0) without time zone,
    reviewed_at timestamp(0) without time zone,
    reviewed_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    submission_count integer DEFAULT 0 NOT NULL
);


--
-- Name: vendor_applications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendor_applications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendor_applications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendor_applications_id_seq OWNED BY public.vendor_applications.id;


--
-- Name: vendor_rejection_reasons; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendor_rejection_reasons (
    id bigint NOT NULL,
    label character varying(200) NOT NULL,
    active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: vendor_rejection_reasons_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendor_rejection_reasons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendor_rejection_reasons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendor_rejection_reasons_id_seq OWNED BY public.vendor_rejection_reasons.id;


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
    minimum_order_amount_by_seller smallint,
    store_category_id bigint
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
-- Name: order_notifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_notifications ALTER COLUMN id SET DEFAULT nextval('public.order_notifications_id_seq'::regclass);


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
-- Name: referral_commission_audits id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referral_commission_audits ALTER COLUMN id SET DEFAULT nextval('public.referral_commission_audits_id_seq'::regclass);


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
-- Name: user_flags id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags ALTER COLUMN id SET DEFAULT nextval('public.user_flags_id_seq'::regclass);


--
-- Name: user_notes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_notes ALTER COLUMN id SET DEFAULT nextval('public.user_notes_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vendor_application_notifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_notifications ALTER COLUMN id SET DEFAULT nextval('public.vendor_application_notifications_id_seq'::regclass);


--
-- Name: vendor_application_reviews id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_reviews ALTER COLUMN id SET DEFAULT nextval('public.vendor_application_reviews_id_seq'::regclass);


--
-- Name: vendor_applications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications ALTER COLUMN id SET DEFAULT nextval('public.vendor_applications_id_seq'::regclass);


--
-- Name: vendor_rejection_reasons id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_rejection_reasons ALTER COLUMN id SET DEFAULT nextval('public.vendor_rejection_reasons_id_seq'::regclass);


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
1	horizon_layout	general	en	[{"layout":"allProducts","headerText":"Demo products"}]	\N	\N	t	0	2026-11-24 03:22:38
2	horizon_layout	general	ar	[{"layout":"allProducts","headerText":"منتجات تجريبية"}]	\N	\N	t	0	2026-11-24 03:22:38
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
\.


--
-- Data for Name: cart_items; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cart_items (id, user_id, product_id, variation_id, qty, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: categories2; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categories2 (id, name, slug, parent, description, display, image, menu_order, count, has_children, _links) FROM stdin;
1	Demo Products	demo-products	0	\N	\N	\N	0	\N	\N	\N
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
-- Data for Name: email_verification_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.email_verification_tokens (email, token, created_at) FROM stdin;
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
-- Data for Name: homepage_heroes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.homepage_heroes (id, enabled, media_type, media_path, poster_path, headline, subtext, button_label, button_link, created_at, updated_at, headline_ar, subtext_ar, button_label_ar) FROM stdin;
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
30	2026_11_08_000001_create_order_notifications	13
31	2026_11_10_000001_add_payment_due_at_to_orders	14
32	2026_11_10_000002_create_vendor_applications	15
33	2026_11_10_000003_add_vendor_review_flags	16
34	2026_11_13_000001_create_referral_commission_audits	17
35	2026_11_13_000002_create_email_verification_tokens	17
36	2026_11_13_000003_unique_external_provider_identity	17
37	2026_11_13_000004_add_platform_payment_verification	17
38	2026_11_21_000001_create_homepage_heroes	18
39	2026_11_21_000002_add_arabic_copy_to_homepage_heroes	19
40	2026_11_21_000003_use_zoned_order_notification_timestamps	20
41	2026_11_24_000001_index_storefront_product_search	21
\.


--
-- Data for Name: order_messages; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.order_messages (id, order_id, customer_id, vendor_id, sender_type, message, is_vendor_response, created_at, updated_at, sub_order_id) FROM stdin;
\.


--
-- Data for Name: order_notifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.order_notifications (id, customer_id, order_id, event, status, read_at, created_at) FROM stdin;
\.


--
-- Data for Name: order_sub_orders; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.order_sub_orders (id, parent_order_id, vendor_id, customer_id, status, line_items, subtotal, discount_total, total, tracking_number, tracking_carrier, timeline, notes, created_at, updated_at, vendor_status) FROM stdin;
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.orders (id, parent_id, parent_vendors_ids, parent_vendors_data, status, currency, version, prices_include_tax, date_created, date_modified, discount_total, discount_tax, shipping_total, shipping_tax, cart_tax, coupon_code, final_total, original_total, coupon_applied, total_tax, customer_id, order_key, billing, shipping, payment_method, payment_method_title, transaction_id, customer_ip_address, customer_user_agent, created_via, customer_note, date_completed, date_paid, cart_hash, meta_data, line_items, tax_lines, shipping_lines, fee_lines, coupon_lines, refunds, payment_url, is_editable, needs_payment, needs_processing, bacs_info, currency_symbol, _links, date_created_gmt, date_modified_gmt, date_completed_gmt, date_paid_gmt, set_paid, number, timeline, updated_at, created_at, payment_status, payment_receipt_path, payment_receipt_name, payment_receipt_uploaded_at, payment_reviewed_at, payment_reviewed_by, payment_rejection_reason, general_order_status, general_order_status_override, general_order_status_override_reason, general_order_status_override_by, general_order_status_override_at, payment_due_at, platform_payment_verified_at, platform_payment_verified_by) FROM stdin;
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.otp_verifications (id, phone, otp_code, expires_at, attempts, resend_count, resend_window_start, verified, created_at, updated_at) FROM stdin;
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
1	1
2	1
3	1
4	1
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
1	1	t	{}	100.00	100.00	0.00	20	[]	\N	\N	instock	publish	\N
2	2	t	{}	150.00	150.00	0.00	20	[]	\N	\N	instock	publish	\N
3	3	t	{}	200.00	200.00	0.00	20	[]	\N	\N	instock	publish	\N
4	4	t	{}	250.00	250.00	0.00	20	[]	\N	\N	instock	publish	\N
\.


--
-- Data for Name: products_data; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.products_data (id, name, slug, search_text, permalink, date_created, date_created_gmt, date_modified, date_modified_gmt, type, status, featured, catalog_visibility, description, discount_percentage, short_description, sku, date_on_sale_from, date_on_sale_from_gmt, date_on_sale_to, date_on_sale_to_gmt, on_sale, purchasable, total_sales, virtual, downloadable, downloads, download_limit, download_expiry, external_url, button_text, manage_stock, stock_quantity, backorders, backorders_allowed, backordered, low_stock_amount, sold_individually, dimensions, shipping_required, shipping_taxable, shipping_class, shipping_class_id, reviews_allowed, average_rating, rating_count, upsell_ids, cross_sell_ids, parent_id, purchase_note, categories, tags, images, attributes, default_attributes, variations, grouped_products, menu_order, related_ids, meta_data, stock_status, has_options, has_variations, global_unique_id, better_featured_image, is_purchased, "attributesData", is_wallet_product, _links, lang, min_price, brand_id, max_price, created_at, updated_at, minimum_order_qty, max_orders_per_person, product_type, vendor_id, translations, acceptance_status, unit, whatsapp, button_mode) FROM stdin;
1	Demo Desk Lamp	demo-desk-lamp	Demo Desk Lamp sample catalog							publish	f		Sample product for local development and testing.		\N	DEMO-1	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		f	20		f	f	0	f	[]	f	f		0	f		0	[]	[]	0		[]	[]	[]	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		0		0	2026-11-24 03:22:38	2026-11-24 03:22:38	1	0	physical	1		approved			both
2	Demo Travel Bag	demo-travel-bag	Demo Travel Bag sample catalog							publish	f		Sample product for local development and testing.		\N	DEMO-2	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		f	20		f	f	0	f	[]	f	f		0	f		0	[]	[]	0		[]	[]	[]	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		0		0	2026-11-24 03:22:38	2026-11-24 03:22:38	1	0	physical	1		approved			both
3	Demo Cotton Shirt	demo-cotton-shirt	Demo Cotton Shirt sample catalog							publish	f		Sample product for local development and testing.		\N	DEMO-3	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		f	20		f	f	0	f	[]	f	f		0	f		0	[]	[]	0		[]	[]	[]	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		0		0	2026-11-24 03:22:38	2026-11-24 03:22:38	1	0	physical	1		approved			both
4	Demo Headphones	demo-headphones	Demo Headphones sample catalog							publish	f		Sample product for local development and testing.		\N	DEMO-4	\N	\N	\N	\N	f	t	0	f	f	[]	0	0	\N		f	20		f	f	0	f	[]	f	f		0	f		0	[]	[]	0		[]	[]	[]	[]	[]	[]	[]	0	[]	[]	instock	f	f		\N	f	[]	f	[]		0		0	2026-11-24 03:22:38	2026-11-24 03:22:38	1	0	physical	1		approved			both
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
-- Data for Name: referral_commission_audits; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.referral_commission_audits (id, commission_id, referral_id, order_id, actor_id, action, old_status, new_status, old_amount, new_amount, reason, metadata, created_at) FROM stdin;
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
-- Data for Name: user_flags; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_flags (id, user_id, created_by, resolved_by, application_id, reason, resolved_at, created_at, updated_at) FROM stdin;
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
1	Demo Customer	customer@example.test	\N	$2y$12$QklkGCWUV1PV/FsIk0tA2.GMliDSLPmHvdsk2tFlS4fagNJ8cz.5G	\N	2026-11-24 01:22:37	2026-11-24 01:22:37	\N	\N	\N	\N	Demo	Customer	\N	\N		normal_user							[]	\N	f	f	\N	\N	PQHUQ3JC	\N	127.0.0.1
\.


--
-- Data for Name: vendor_application_notifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_application_notifications (id, user_id, application_id, status, read_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: vendor_application_reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_application_reviews (id, application_id, reviewed_by, submission_number, decision, reason, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: vendor_applications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_applications (id, user_id, vendor_id, status, personal_data, marketplace_draft, identity_document, identity_mime, rejection_reason, submitted_at, reviewed_at, reviewed_by, created_at, updated_at, submission_count) FROM stdin;
\.


--
-- Data for Name: vendor_rejection_reasons; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_rejection_reasons (id, label, active, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: vendor_users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.vendor_users (id, profile_image, first_name, last_name, phone, email, password, email_verified_at, remember_token, created_at, updated_at, shop_name, shop_address, shop_logo, shop_banner, secondary_banner, bottom_banner, status, rating, rating_count, temporary_close, vacation_end_date, vacation_start_date, vacation_status, offer_banner, product_count, orders_count, minimum_order_amount, free_delivery_over_amount, free_delivery_status, sales_commission_percentage, auth_token, holder_name, account_no, bank_name, branch, free_delivery_features_status, free_delivery_responsibility, minimum_order_amount_by_seller, store_category_id) FROM stdin;
1	\N	Demo	Seller		seller@example.test	$2y$12$Q1ymyec7yiLwj4GPa9MACu7gWNrRyTG9dYlJADFP9ea/yaJq0.YHy	\N	\N	2026-11-24 01:22:38	2026-11-24 01:22:38	Demo Store	Demo address	\N	\N	\N		approved	0	0	0	empty	empty	0	empty	\N	\N	\N	\N	\N	\N			\N			\N	\N	\N	\N
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

SELECT pg_catalog.setval('public.app_configs_id_seq', 2, true);


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

SELECT pg_catalog.setval('public.brands_id_seq', 1, false);


--
-- Name: cart_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cart_items_id_seq', 1, false);


--
-- Name: categories2_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories2_id_seq', 1, true);


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

SELECT pg_catalog.setval('public.coupon_user_limits_id_seq', 1, false);


--
-- Name: coupons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.coupons_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.idempotency_keys_id_seq', 1, false);


--
-- Name: image_gallery_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.image_gallery_images_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 1, false);


--
-- Name: order_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_messages_id_seq', 1, false);


--
-- Name: order_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_notifications_id_seq', 1, false);


--
-- Name: order_sub_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_sub_orders_id_seq', 1, false);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orders_id_seq', 1, false);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 1, false);


--
-- Name: payment_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_receipts_id_seq', 1, false);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: product_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_reviews_id_seq', 1, false);


--
-- Name: product_variations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_variations_id_seq', 4, true);


--
-- Name: products_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_id_seq', 4, true);


--
-- Name: products_data_main_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_data_main_id_seq', 1, false);


--
-- Name: referral_commission_audits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referral_commission_audits_id_seq', 1, false);


--
-- Name: referral_commissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referral_commissions_id_seq', 1, false);


--
-- Name: referrals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referrals_id_seq', 1, false);


--
-- Name: refund_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.refund_requests_id_seq', 1, false);


--
-- Name: saved_addresses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.saved_addresses_id_seq', 1, false);


--
-- Name: shops_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.shops_id_seq', 1, false);


--
-- Name: storefront_price_ranges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.storefront_price_ranges_id_seq', 1, false);


--
-- Name: tags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tags_id_seq', 1, false);


--
-- Name: time_line_configs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.time_line_configs_id_seq', 1, false);


--
-- Name: user_flags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_flags_id_seq', 1, false);


--
-- Name: user_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_notes_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: vendor_application_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_application_notifications_id_seq', 1, false);


--
-- Name: vendor_application_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_application_reviews_id_seq', 1, false);


--
-- Name: vendor_applications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_applications_id_seq', 1, false);


--
-- Name: vendor_rejection_reasons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_rejection_reasons_id_seq', 1, false);


--
-- Name: vendor_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.vendor_users_id_seq', 1, true);


--
-- Name: version_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.version_config_id_seq', 1, false);


--
-- Name: wishlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.wishlists_id_seq', 1, false);


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
-- Name: email_verification_tokens email_verification_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.email_verification_tokens
    ADD CONSTRAINT email_verification_tokens_pkey PRIMARY KEY (email);


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
-- Name: homepage_heroes homepage_heroes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.homepage_heroes
    ADD CONSTRAINT homepage_heroes_pkey PRIMARY KEY (id);


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
-- Name: order_notifications order_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_notifications
    ADD CONSTRAINT order_notifications_pkey PRIMARY KEY (id);


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
-- Name: referral_commission_audits referral_commission_audits_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referral_commission_audits
    ADD CONSTRAINT referral_commission_audits_pkey PRIMARY KEY (id);


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
-- Name: user_flags user_flags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags
    ADD CONSTRAINT user_flags_pkey PRIMARY KEY (id);


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
-- Name: users users_provider_identity_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_provider_identity_unique UNIQUE (provider, provider_id);


--
-- Name: users users_referral_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_referral_code_unique UNIQUE (referral_code);


--
-- Name: vendor_application_notifications vendor_application_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_notifications
    ADD CONSTRAINT vendor_application_notifications_pkey PRIMARY KEY (id);


--
-- Name: vendor_application_reviews vendor_application_reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_reviews
    ADD CONSTRAINT vendor_application_reviews_pkey PRIMARY KEY (id);


--
-- Name: vendor_applications vendor_applications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_pkey PRIMARY KEY (id);


--
-- Name: vendor_applications vendor_applications_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_user_id_unique UNIQUE (user_id);


--
-- Name: vendor_applications vendor_applications_vendor_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_vendor_id_unique UNIQUE (vendor_id);


--
-- Name: vendor_rejection_reasons vendor_rejection_reasons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_rejection_reasons
    ADD CONSTRAINT vendor_rejection_reasons_pkey PRIMARY KEY (id);


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
-- Name: order_notifications_customer_id_created_at_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX order_notifications_customer_id_created_at_id_index ON public.order_notifications USING btree (customer_id, created_at, id);


--
-- Name: order_notifications_customer_id_read_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX order_notifications_customer_id_read_at_index ON public.order_notifications USING btree (customer_id, read_at);


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
-- Name: orders_payment_due_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX orders_payment_due_idx ON public.orders USING btree (payment_status, payment_due_at);


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
-- Name: products_data_search_text_gin; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_data_search_text_gin ON public.products_data USING gin (to_tsvector('simple'::regconfig, COALESCE(NULLIF(btrim(search_text), ''::text), (name)::text, ''::text)));


--
-- Name: referral_commission_audits_actor_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commission_audits_actor_id_index ON public.referral_commission_audits USING btree (actor_id);


--
-- Name: referral_commission_audits_commission_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commission_audits_commission_id_index ON public.referral_commission_audits USING btree (commission_id);


--
-- Name: referral_commission_audits_order_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commission_audits_order_id_index ON public.referral_commission_audits USING btree (order_id);


--
-- Name: referral_commission_audits_referral_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX referral_commission_audits_referral_id_index ON public.referral_commission_audits USING btree (referral_id);


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
-- Name: user_flags_user_id_resolved_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_flags_user_id_resolved_at_index ON public.user_flags USING btree (user_id, resolved_at);


--
-- Name: users_phone_unique_nonempty; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX users_phone_unique_nonempty ON public.users USING btree (phone) WHERE ((phone IS NOT NULL) AND (phone <> ''::text));


--
-- Name: users_referred_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_referred_by_index ON public.users USING btree (referred_by);


--
-- Name: vendor_application_notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vendor_application_notifications_user_id_read_at_index ON public.vendor_application_notifications USING btree (user_id, read_at);


--
-- Name: vendor_applications_status_submitted_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vendor_applications_status_submitted_at_index ON public.vendor_applications USING btree (status, submitted_at);


--
-- Name: wishlists_user_product_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wishlists_user_product_idx ON public.wishlists USING btree (user_id, product_id);


--
-- Name: orders order_notifications_on_change; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER order_notifications_on_change AFTER INSERT OR UPDATE OF status, payment_status ON public.orders FOR EACH ROW EXECUTE FUNCTION public.record_order_notification();


--
-- Name: referral_commission_audits referral_audit_immutable; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER referral_audit_immutable BEFORE DELETE OR UPDATE ON public.referral_commission_audits FOR EACH ROW EXECUTE FUNCTION public.prevent_referral_audit_mutation();


--
-- Name: image_gallery_images image_gallery_images_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.image_gallery_images
    ADD CONSTRAINT image_gallery_images_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: order_notifications order_notifications_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_notifications
    ADD CONSTRAINT order_notifications_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: saved_addresses saved_addresses_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saved_addresses
    ADD CONSTRAINT saved_addresses_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_flags user_flags_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags
    ADD CONSTRAINT user_flags_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.vendor_applications(id) ON DELETE SET NULL;


--
-- Name: user_flags user_flags_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags
    ADD CONSTRAINT user_flags_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_flags user_flags_resolved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags
    ADD CONSTRAINT user_flags_resolved_by_foreign FOREIGN KEY (resolved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_flags user_flags_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_flags
    ADD CONSTRAINT user_flags_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vendor_application_notifications vendor_application_notifications_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_notifications
    ADD CONSTRAINT vendor_application_notifications_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.vendor_applications(id) ON DELETE CASCADE;


--
-- Name: vendor_application_notifications vendor_application_notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_notifications
    ADD CONSTRAINT vendor_application_notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vendor_application_reviews vendor_application_reviews_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_reviews
    ADD CONSTRAINT vendor_application_reviews_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.vendor_applications(id) ON DELETE CASCADE;


--
-- Name: vendor_application_reviews vendor_application_reviews_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_application_reviews
    ADD CONSTRAINT vendor_application_reviews_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: vendor_applications vendor_applications_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: vendor_applications vendor_applications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vendor_applications vendor_applications_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_applications
    ADD CONSTRAINT vendor_applications_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendor_users(id) ON DELETE SET NULL;


--
-- Name: vendor_users vendor_users_store_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_users
    ADD CONSTRAINT vendor_users_store_category_id_foreign FOREIGN KEY (store_category_id) REFERENCES public.categories2(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict GDHNwpREGVbP88UWRswqqsfM3NhpWviCd1ENTzhaRcOrwhdH6JSNH6swnJqDKjp

