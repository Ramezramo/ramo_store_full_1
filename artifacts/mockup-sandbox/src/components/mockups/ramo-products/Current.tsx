import { LiveProductGrid } from "../_shared/LiveProductGrid";
import "./_base.css";

const language = new URLSearchParams(window.location.search).get("lang") === "en" ? "en" : "ar";
const title = language === "ar" ? "منتجات قد تعجبك" : "Products you may like";

export default function Current() {
  return (
    <main className="ramo-products-preview ramo-current-preview" dir={language === "ar" ? "rtl" : "ltr"} lang={language}>
      <section className="ramo-products-shell" aria-label={title}>
        <header className="ramo-products-heading">
          <h1>{title}</h1>
        </header>
        <LiveProductGrid />
      </section>
    </main>
  );
}
