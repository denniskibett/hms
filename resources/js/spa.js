/*
 * Background page changes for the staff screens (the same pattern as scharge's spa.js).
 * - Links inside the staff screens load in the background and swap only <main id="app-content">;
 *   the sidebar and header stay put. Back/forward work. Pages are prefetched on hover.
 * - Forms post in the background too. The page Laravel sends back after saving (with its flash
 *   message, or with validation errors and the slide-over open again) replaces the content.
 * - window.hmsRefresh() reloads the current page's content quietly (used after saves made by fetch).
 * - Refresh events: after every swap "hms:refresh" (window) and "spa:load" (document) fire, so
 *   charts, calendars and widgets set themselves up again; pages may listen for them.
 * Anything that doesn't fit (other layouts, downloads, print pages, errors) falls back to a normal
 * load, and the reason is logged as "[spa] full load: ...".
 */
const CONTENT = "#app-content";
const CACHE_MS = 15000;
const cache = new Map();

const NORMAL_LOAD = /^\/(logout|login|register|forgot-password|reset-password|verify-email|webhooks|stay|dining|events-and-conferences|contact|about|book|thanks|storage)(\/|$)/;
const FILE_LIKE = /\.(pdf|csv|xlsx?|zip|png|jpe?g|webp)$/i;
const DOWNLOADS = /(export|download|print|pdf|receipt|statement|payslip)/i;

function appUrl(href) {
  if (!href) return null;
  const url = new URL(href, location.href);
  if (url.origin !== location.origin) return null;
  if (url.pathname === "/" || NORMAL_LOAD.test(url.pathname)) return null;
  if (FILE_LIKE.test(url.pathname) || DOWNLOADS.test(url.pathname)) return null;
  return url;
}

function sameAppLink(a, event) {
  if (!a || (event && (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey))) return null;
  if (a.target && a.target !== "_self") return null;
  if (a.hasAttribute("download") || a.closest("[data-no-spa]")) return null;
  const href = a.getAttribute("href");
  if (!href || href.startsWith("#") || /^(javascript|mailto|tel|sms|https?:\/\/wa\.me)/.test(href)) return null;
  const url = appUrl(a.href);
  if (!url) return null;
  if (url.pathname === location.pathname && url.search === location.search && url.hash) return null;
  return url;
}

function request(href, init = {}) {
  return fetch(href, { credentials: "same-origin", ...init, headers: { "X-Requested-With": "spa", Accept: "text/html", ...(init.headers || {}) } })
    .then(async (res) => {
      const type = res.headers.get("content-type") || "";
      const out = { ok: res.ok, status: res.status, url: res.url || href, redirected: res.redirected, type, disposition: res.headers.get("content-disposition") || "" };
      if (type.includes("text/html")) out.html = await res.text(); else out.blob = await res.blob();
      return out;
    })
    .catch(() => null);
}

function load(href) {
  const hit = cache.get(href);
  if (hit && Date.now() - hit.at < CACHE_MS) return hit.promise;
  const promise = request(href);
  cache.set(href, { at: Date.now(), promise });
  return promise;
}

function prefetch(a) {
  const url = sameAppLink(a);
  if (!url || url.href === location.href) return;
  const c = navigator.connection;
  if (c && (c.saveData || /2g/.test(c.effectiveType || ""))) return;
  load(url.href);
}

/** Runs the swapped page's scripts; re-declared top-level const/let/class are retried as var. */
function runScripts(container) {
  let failed = null;
  let lastError = null;
  const onError = (e) => { lastError = e.message || String(e.error || ""); };
  window.addEventListener("error", onError);
  const prepare = (text) => text
    .replace(/document\.addEventListener\(\s*(['"])DOMContentLoaded\1\s*,/g, "window.__spaReady(")
    .replace(/window\.addEventListener\(\s*(['"])load\1\s*,/g, "window.__spaReady(");
  const asVar = (text) => text
    .replace(/^([ \t]{0,8})(?:const|let)\s+(?=[A-Za-z_$])/gm, "$1var ")
    .replace(/^([ \t]{0,8})class\s+([A-Za-z_$][\w$]*)/gm, "$1var $2 = class $2");
  const make = (old, text) => {
    const s = document.createElement("script");
    [...old.attributes].forEach((attr) => s.setAttribute(attr.name, attr.value));
    s.textContent = text;
    return s;
  };
  container.querySelectorAll("script").forEach((old) => {
    if (old.type && !/javascript|module/.test(old.type)) return;
    if (old.src) { try { old.replaceWith(make(old, "")); } catch (e) { failed = e.message; } return; }
    const text = prepare(old.textContent);
    lastError = null;
    const s = make(old, text);
    try { old.replaceWith(s); } catch (e) { lastError = e.message; }
    if (lastError && /already been declared/.test(lastError)) {
      lastError = null;
      const retry = make(old, asVar(text));
      try { s.replaceWith(retry); } catch (e) { lastError = e.message; }
      if (lastError && /already been declared|SyntaxError|Unexpected/.test(lastError)) failed = lastError;
    } else if (lastError && /SyntaxError|Unexpected token/.test(lastError)) {
      failed = lastError;
    }
  });
  window.removeEventListener("error", onError);
  return failed;
}

window.__spaReady = (fn) => { if (typeof fn === "function") setTimeout(() => fn(new Event("DOMContentLoaded")), 0); };

const root = document.documentElement;
let showTimer;
function loadingStart() {
  clearTimeout(showTimer);
  root.classList.remove("spa-done");
  showTimer = setTimeout(() => root.classList.add("spa-loading"), 120);
}
function loadingEnd() {
  clearTimeout(showTimer);
  if (!root.classList.contains("spa-loading")) return;
  root.classList.remove("spa-loading");
  root.classList.add("spa-done");
  setTimeout(() => root.classList.remove("spa-done"), 450);
}

function fullLoad(url, reason) {
  console.info("[spa] full load:", reason, url);
  location.href = url;
}

let current = 0;

/** Puts a fetched page into #app-content. Returns false when it can't (caller loads normally). */
function swap(res, { push, keepScroll }) {
  const target = document.querySelector(CONTENT);
  const doc = new DOMParser().parseFromString(res.html, "text/html");
  const next = doc.querySelector(CONTENT);
  if (!target || !next) return "different layout";

  const scrollBox = target.closest(".overflow-y-auto");
  const y = keepScroll && scrollBox ? scrollBox.scrollTop : 0;
  const A = window.Alpine;
  if (A && A.mutateDom && A.initTree) {
    A.mutateDom(() => {
      [...target.children].forEach((el) => A.destroyTree && A.destroyTree(el));
      target.innerHTML = next.innerHTML;
    });
    const failed = runScripts(target);
    if (failed) return "page script: " + failed;
    document.dispatchEvent(new CustomEvent("alpine:init"));
    [...target.children].forEach((el) => A.initTree(el));
  } else {
    target.innerHTML = next.innerHTML;
    runScripts(target);
  }
  document.title = doc.title || document.title;
  const url = res.url;
  if (push === "push") history.pushState({ spa: true }, "", url);
  else if (push === "replace") history.replaceState({ spa: true }, "", url);
  if (scrollBox) scrollBox.scrollTop = y;

  // The sidebar highlights the section from the first part of the address.
  try {
    const body = A && A.$data ? A.$data(document.body) : null;
    if (body) body.page = new URL(url, location.href).pathname.split("/")[1] || "dashboard";
  } catch (e) { /* sidebar keeps its last highlight */ }

  window.dispatchEvent(new CustomEvent("spa:navigated", { detail: { url } }));
  window.dispatchEvent(new CustomEvent("hms:refresh", { detail: { url } }));
  document.dispatchEvent(new Event("spa:load"));
  return null;
}

async function visit(url, { push = "push", keepScroll = false, fresh = false } = {}) {
  if (!document.querySelector(CONTENT)) return (location.href = url);
  const ticket = ++current;
  if (fresh) cache.delete(url);
  if (!keepScroll) loadingStart();
  const res = await load(url);
  if (ticket !== current) return;
  cache.delete(url);
  loadingEnd();
  if (!res) return fullLoad(url, "network error");
  if (!res.ok || !res.type.includes("text/html")) return fullLoad(url, res.ok ? "not html: " + res.type : "error " + res.status);
  if (res.redirected && /^\/login/.test(new URL(res.url).pathname)) return fullLoad(res.url, "signed out");
  const failed = swap(res, { push, keepScroll });
  if (failed) fullLoad(res.url || url, failed);
}

/** Forms post in the background; whatever page Laravel lands on (after its redirect) is shown. */
async function submitForm(form, submitter) {
  const method = (submitter?.getAttribute("formmethod") || form.getAttribute("method") || "GET").toUpperCase();
  const action = submitter?.getAttribute("formaction") || form.getAttribute("action") || location.href;
  const url = appUrl(new URL(action, location.href).href);
  const data = new FormData(form);
  if (submitter && submitter.name) data.append(submitter.name, submitter.value);

  if (method === "GET") {
    const u = new URL(action, location.href);
    u.search = new URLSearchParams([...data].filter(([, v]) => typeof v === "string")).toString();
    return visit(u.href);
  }

  const ticket = ++current;
  const buttons = [...form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]')];
  buttons.forEach((b) => (b.disabled = true));
  loadingStart();
  const res = await request(url.href, { method: "POST", body: data });
  buttons.forEach((b) => (b.disabled = false));
  loadingEnd();
  if (ticket !== current) return;
  // Nothing reached the server: send the form the normal way. Anything that did reach it is never sent twice.
  if (!res) { console.info("[spa] normal submit: network error"); form.dataset.spaSubmitted = "1"; return HTMLFormElement.prototype.submit.call(form); }
  if (res.status === 419) return location.reload(); // session expired: get a fresh form token
  if (res.blob) { // a file (export, PDF): save it
    const name = (/filename\*?=(?:UTF-8'')?"?([^";]+)/i.exec(res.disposition) || [])[1] || "download";
    const link = Object.assign(document.createElement("a"), { href: URL.createObjectURL(res.blob), download: decodeURIComponent(name) });
    document.body.appendChild(link); link.click(); link.remove();
    return;
  }
  if (!res.ok) { document.open(); document.write(res.html); document.close(); return; } // show the server's error page
  if (/^\/login/.test(new URL(res.url).pathname)) return fullLoad(res.url, "signed out");
  const landed = new URL(res.url, location.href);
  const samePage = landed.pathname === location.pathname && landed.search === location.search;
  cache.clear(); // saved data: prefetched pages are stale
  const failed = swap(res, { push: samePage ? "replace" : "push", keepScroll: samePage });
  if (failed) fullLoad(res.url, failed);
}

window.spaVisit = (url) => visit(new URL(url, location.href).href);
window.hmsRefresh = () => { cache.clear(); return visit(location.href, { push: "none", keepScroll: true, fresh: true }); };

if (document.querySelector(CONTENT)) {
  history.replaceState({ spa: true }, "", location.href);

  document.addEventListener("click", (event) => {
    const a = event.target.closest("a");
    const url = sameAppLink(a, event);
    if (!url) return;
    event.preventDefault();
    visit(url.href);
  });

  // Runs after Alpine's own @submit handlers: forms they already handle (fetch + JSON) are left alone.
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement)) return;
    if (form.dataset.spaSubmitted || form.closest("[data-no-spa]") || (form.target && form.target !== "_self")) return;
    const action = event.submitter?.getAttribute("formaction") || form.getAttribute("action") || location.href;
    if (!appUrl(new URL(action, location.href).href)) return;
    event.preventDefault();
    submitForm(form, event.submitter);
  });

  let timer;
  document.addEventListener("mouseover", (e) => {
    const a = e.target.closest?.("a");
    clearTimeout(timer);
    if (a) timer = setTimeout(() => prefetch(a), 65);
  }, { passive: true });
  document.addEventListener("touchstart", (e) => prefetch(e.target.closest?.("a")), { passive: true });

  let shown = location.pathname + location.search;
  window.addEventListener("spa:navigated", () => (shown = location.pathname + location.search));
  window.addEventListener("popstate", () => {
    if (location.pathname + location.search === shown) return;
    visit(location.href, { push: "none" });
  });
}
