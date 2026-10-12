import { useEffect, useState } from "react";

type ProductsResponse = {
  html?: unknown;
};

export function LiveProductGrid({ className = "" }: { className?: string }) {
  const [markup, setMarkup] = useState<string | null>(null);
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    const controller = new AbortController();

    async function loadProducts() {
      try {
        const endpoint = new URL("/home/products", window.location.origin);
        endpoint.searchParams.set("all_products_page", "1");

        const response = await fetch(endpoint, {
          credentials: "same-origin",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          signal: controller.signal,
        });

        if (!response.ok) throw new Error("Product request failed");

        const payload = (await response.json()) as ProductsResponse;
        if (typeof payload.html !== "string") {
          throw new Error("Product response did not include rendered cards");
        }

        const template = document.createElement("template");
        template.innerHTML = payload.html.trim();
        template.content.querySelectorAll("style, script").forEach((node) => node.remove());
        template.content.querySelectorAll("*").forEach((element) => {
          Array.from(element.attributes)
            .filter((attribute) => attribute.name.toLowerCase().startsWith("on"))
            .forEach((attribute) => element.removeAttribute(attribute.name));
        });

        if (!template.content.querySelector(".product-card")) {
          throw new Error("Product response did not include any cards");
        }

        setMarkup(template.innerHTML);
      } catch {
        if (!controller.signal.aborted) setFailed(true);
      }
    }

    void loadProducts();
    return () => controller.abort();
  }, []);

  if (failed) {
    return (
      <p className="product-preview-state" role="alert">
        Unable to load the store’s products.
      </p>
    );
  }

  if (markup === null) {
    return (
      <p className="product-preview-state" role="status">
        Loading products…
      </p>
    );
  }

  return (
    <div
      className={`product-grid all-products-grid ${className}`}
      dangerouslySetInnerHTML={{ __html: markup }}
    />
  );
}
