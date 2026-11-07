{{--
    PROTOTYPE (throwaway) — Phase 11 public site visual directions.
    Two structurally different variants of landing + pricing, switchable
    via ?variant=A|B with a floating bottom bar. Local env only.
    Question: "Which visual direction should the public site take?"
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prototype — Public Site Direction {{ strtoupper(request('variant', 'A')) }}</title>
<style>
/* Same token values as resources/css/app.css — prototype-only copy */
:root, .variant-a {
    --primary: oklch(0.546 0.237 258);
    --primary-foreground: oklch(0.985 0 0);
    --background: oklch(0.965 0.012 258);
    --foreground: oklch(0.144 0.022 254);
    /* GLASS surfaces: translucent + blur */
    --glass: oklch(1 0 0 / 0.55);
    --glass-strong: oklch(1 0 0 / 0.72);
    --glass-border: oklch(1 0 0 / 0.65);
    --card: oklch(1 0 0 / 0.55);
    --card-foreground: oklch(0.144 0.022 254);
    --muted: oklch(0.962 0.008 258 / 0.6);
    --muted-foreground: oklch(0.42 0.025 254);
    --border: oklch(0.546 0.237 258 / 0.18);
    --success: oklch(0.627 0.194 149);
    --radius: 1rem;
}
.variant-b {
    --primary: oklch(0.7 0.18 258);
    --primary-foreground: oklch(0.13 0.02 254);
    --background: oklch(0.145 0.015 254);
    --foreground: oklch(0.97 0.005 254);
    --card: oklch(0.19 0.018 254);
    --card-foreground: oklch(0.97 0.005 254);
    --muted: oklch(0.24 0.018 254);
    --muted-foreground: oklch(0.68 0.02 254);
    --border: oklch(0.3 0.02 254);
    --success: oklch(0.72 0.17 162);
    --radius: 0.25rem;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
       background: var(--background); color: var(--foreground); }
/* GLASS base: gradient mesh backdrop that the frosted surfaces blur */
body::before {
    content: ""; position: fixed; inset: 0; z-index: -1; pointer-events: none;
    background:
        radial-gradient(600px 420px at 12% -6%, oklch(0.72 0.16 250 / 0.42), transparent 65%),
        radial-gradient(760px 520px at 96% 12%, oklch(0.78 0.14 300 / 0.34), transparent 62%),
        radial-gradient(680px 540px at 42% 108%, oklch(0.8 0.13 190 / 0.32), transparent 60%),
        var(--background);
}
.glass { background: var(--glass); backdrop-filter: blur(18px) saturate(1.5);
         -webkit-backdrop-filter: blur(18px) saturate(1.5);
         border: 1px solid var(--glass-border);
         box-shadow: 0 8px 32px -12px oklch(0.4 0.1 255 / 0.25), inset 0 1px 0 oklch(1 0 0 / 0.6); }
a { color: inherit; }
.wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
.btn { display: inline-block; padding: 12px 24px; border-radius: var(--radius);
       font-weight: 600; text-decoration: none; cursor: pointer; border: 0; font-size: 15px; }
.btn-primary { background: var(--primary); color: var(--primary-foreground); }
.btn-outline { border: 1px solid var(--border); color: var(--foreground); }
.eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--primary); }
.badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px;
         border-radius: 999px; background: var(--muted); color: var(--muted-foreground); }

/* ---------- Variant A: "Cobalt Classic SaaS" (glassmorphism) ---------- */
.a-nav { display: flex; align-items: center; justify-content: space-between; padding: 14px 24px;
         margin-top: 20px; border-radius: 999px; position: sticky; top: 16px; z-index: 50; }
.a-nav.glass { background: var(--glass-strong); }
.a-logo { font-size: 20px; font-weight: 800; }
.a-logo span { color: var(--primary); }
.a-nav-links { display: flex; gap: 24px; font-size: 15px; }
.a-nav-links a { text-decoration: none; color: var(--muted-foreground); }
.a-hero { text-align: center; padding: 96px 0 72px; }
.a-hero h1 { font-size: 56px; line-height: 1.1; letter-spacing: -0.02em; margin: 20px 0; }
.a-hero p { font-size: 19px; color: var(--muted-foreground); max-width: 640px; margin: 0 auto 32px; }
.a-cta { display: flex; gap: 12px; justify-content: center; }
.a-mockup { margin: 56px auto 0; max-width: 880px; border-radius: 20px; padding: 18px; }
.a-mockup-bar { display: flex; gap: 6px; margin-bottom: 14px; }
.a-mockup-bar i { width: 10px; height: 10px; border-radius: 50%; background: var(--border); }
.a-mockup-body { background: oklch(1 0 0 / 0.35); border-radius: 14px; padding: 32px; display: grid;
                 grid-template-columns: repeat(3, 1fr); gap: 14px; }
.a-stat { border-radius: 12px; padding: 18px; background: oklch(1 0 0 / 0.5);
          border: 1px solid var(--glass-border); }
.a-stat b { font-size: 24px; display: block; }
.a-stat small { color: var(--muted-foreground); }
.a-features { padding: 72px 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.a-feature { border-radius: 18px; padding: 28px; }
.a-feature h3 { margin: 12px 0 8px; font-size: 17px; }
.a-feature p { font-size: 14px; color: var(--muted-foreground); line-height: 1.6; }
.a-icon { width: 40px; height: 40px; border-radius: 10px; background: oklch(0.546 0.237 258 / 0.12);
          color: var(--primary); border: 1px solid oklch(0.546 0.237 258 / 0.2);
          display: flex; align-items: center; justify-content: center; font-size: 20px; }
.a-pricing { padding: 72px 0 96px; text-align: center; }
.a-pricing h2 { font-size: 36px; margin-bottom: 8px; }
.a-toggle { display: inline-flex; border: 1px solid var(--glass-border); border-radius: 999px; overflow: hidden;
            margin: 16px 0; background: oklch(1 0 0 / 0.5); backdrop-filter: blur(12px); }
.a-plans { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 40px;
           text-align: left; align-items: stretch; }
.a-plan { border-radius: 18px; padding: 28px; position: relative; display: flex; flex-direction: column; }
.a-plan .btn { margin-top: auto; }
.a-plan.featured { background: var(--glass-strong); border: 2px solid oklch(0.546 0.237 258 / 0.65);
                   box-shadow: 0 24px 48px -20px oklch(0.546 0.237 258 / 0.5); }
.a-plan .pop { position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
               background: var(--primary); color: var(--primary-foreground); font-size: 12px;
               font-weight: 700; padding: 4px 12px; border-radius: 999px; }
.a-plan h3 { font-size: 15px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted-foreground); }
.a-price { font-size: 40px; font-weight: 800; margin: 8px 0; }
.a-price small { font-size: 14px; font-weight: 400; color: var(--muted-foreground); }
.a-plan ul { list-style: none; margin: 16px 0 24px; font-size: 14px; color: var(--muted-foreground); }
.a-plan li { padding: 6px 0; }
.a-plan li::before { content: "✓ "; color: var(--success); font-weight: 700; }
.a-toggle { display: inline-flex; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; margin: 16px 0; }
.a-toggle button { padding: 8px 18px; background: transparent; border: 0; cursor: pointer; font-size: 14px; color: var(--muted-foreground); }
.a-toggle button.on { background: var(--primary); color: var(--primary-foreground); }

/* ---------- Variant B: "Bold Terminal / dev-tool editorial" ---------- */
.b-page { border-top: 6px solid var(--primary); }
.b-nav { display: flex; align-items: center; justify-content: space-between; padding: 24px 0; }
.b-logo { font-family: 'JetBrains Mono', 'Cascadia Code', Consolas, monospace; font-weight: 800; font-size: 18px; }
.b-logo::before { content: "▮ "; color: var(--primary); }
.b-nav-links { display: flex; gap: 20px; font-size: 14px; font-family: Consolas, monospace; }
.b-nav-links a { text-decoration: none; color: var(--muted-foreground); }
.b-hero { display: grid; grid-template-columns: 1.2fr 1fr; gap: 48px; padding: 88px 0; align-items: start; }
.b-hero h1 { font-size: 52px; line-height: 1.05; letter-spacing: -0.03em; margin: 16px 0 20px; }
.b-hero h1 em { font-style: normal; color: var(--primary); }
.b-hero p { font-size: 17px; color: var(--muted-foreground); line-height: 1.7; margin-bottom: 28px; }
.b-hero aside { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px;
                font-family: Consolas, monospace; font-size: 13px; line-height: 1.9; }
.b-hero aside .cm { color: var(--muted-foreground); }
.b-hero aside .k { color: var(--primary); }
.b-hero aside .ok { color: var(--success); }
.b-trust { display: flex; gap: 24px; font-family: Consolas, monospace; font-size: 12px; color: var(--muted-foreground); }
.b-trust b { color: var(--foreground); font-size: 20px; display: block; }
.b-features { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px; background: var(--border);
              border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.b-feature { background: var(--card); padding: 28px; }
.b-feature h3 { font-size: 16px; margin-bottom: 8px; }
.b-feature h3::before { content: "// "; color: var(--primary); font-family: Consolas, monospace; }
.b-feature p { font-size: 14px; color: var(--muted-foreground); line-height: 1.6; }
.b-pricing { padding: 80px 0; }
.b-pricing h2 { font-size: 34px; margin-bottom: 6px; }
.b-table { width: 100%; border-collapse: collapse; margin-top: 32px; font-size: 14px; }
.b-table th, .b-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--border); }
.b-table thead th { font-family: Consolas, monospace; font-size: 13px; text-transform: uppercase;
                    letter-spacing: 0.08em; color: var(--muted-foreground); }
.b-table td:first-child { color: var(--muted-foreground); }
.b-price-cell b { font-size: 22px; display: block; }
.b-col-pro { background: var(--muted); }
.b-foot-cta { padding: 0 0 96px; display: flex; align-items: center; justify-content: space-between;
              background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 32px; }
.b-foot-cta h3 { font-size: 22px; margin-bottom: 4px; }

/* ---------- Switcher (obviously not part of the design) ---------- */
#proto-bar { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
             background: #111; color: #fff; border-radius: 999px; display: flex; align-items: center;
             gap: 4px; padding: 6px 10px; font-size: 13px; z-index: 999; box-shadow: 0 8px 24px rgba(0,0,0,.4);
             font-family: system-ui, sans-serif; }
#proto-bar button { background: #333; color: #fff; border: 0; width: 28px; height: 28px; border-radius: 50%;
                    cursor: pointer; font-size: 15px; }
#proto-bar span { padding: 0 10px; min-width: 220px; text-align: center; }
.hidden { display: none !important; }
@media (max-width: 860px) {
    .a-hero h1 { font-size: 36px; }
    .a-features, .a-mockup-body { grid-template-columns: 1fr; }
    /* Pricing stays side-by-side on tablet/mobile, stacks only under 620px */
    .a-plans { gap: 10px; }
    .a-plan { padding: 16px; }
    .a-price { font-size: 30px; }
    .b-hero { grid-template-columns: 1fr; padding: 48px 0; }
    .b-hero h1 { font-size: 34px; }
    .b-features { grid-template-columns: 1fr; }
    .b-table .optional-col { display: none; }
    .a-nav-links, .b-nav-links { display: none; }
}
@media (max-width: 620px) { .a-plans { grid-template-columns: 1fr; } .a-plan { padding: 24px; } }
</style>
</head>
<body>

{{-- ============ VARIANT A ============ --}}
<div class="variant-a {{ request('variant', 'A') !== 'A' ? 'hidden' : '' }}">
    <div class="wrap">
        <nav class="a-nav glass">
            <div class="a-logo">{{ config('app.name') }}<span>.</span></div>
            <div class="a-nav-links">
                <a href="#">Product</a><a href="#">Pricing</a><a href="#">Docs</a><a href="#">Contact</a>
            </div>
            <a class="btn btn-primary" href="#" style="padding:9px 18px;font-size:14px;">Sign up free</a>
        </nav>

        <section class="a-hero">
            <span class="eyebrow">Multi-gateway billing boilerplate</span>
            <h1>Launch your SaaS<br>with billing that just works</h1>
            <p>Workspaces, subscriptions, invoices and entitlements — powered by Stripe, Paddle and SSLCommerz. Production-ready from day one.</p>
            <div class="a-cta">
                <a class="btn btn-primary" href="#">Start free trial</a>
                <a class="btn btn-outline" href="#">View pricing</a>
            </div>
            <div class="a-mockup glass">
                <div class="a-mockup-bar"><i></i><i></i><i></i></div>
                <div class="a-mockup-body">
                    <div class="a-stat"><b>$4,820</b><small>MRR this month</small></div>
                    <div class="a-stat"><b>1,204</b><small>Active workspaces</small></div>
                    <div class="a-stat"><b>98.2%</b><small>Trial conversion</small></div>
                </div>
            </div>
        </section>

        <section class="a-features">
            <div class="a-feature glass"><div class="a-icon">◆</div><h3>Multi-gateway billing</h3><p>Stripe, Paddle and SSLCommerz adapters behind one interface — verified webhooks, proration and refunds included.</p></div>
            <div class="a-feature glass"><div class="a-icon">◇</div><h3>Workspaces &amp; teams</h3><p>Every user gets a workspace. Invite teammates with owner, admin, member and billing roles.</p></div>
            <div class="a-feature glass"><div class="a-icon">○</div><h3>Entitlements engine</h3><p>Plans are data, not code. Feature flags, seat limits and metered usage enforced by one service.</p></div>
        </section>

        <section class="a-pricing">
            <h2>Simple, transparent pricing</h2>
            <div class="a-toggle"><button class="on" type="button">Monthly</button><button type="button">Annual −17%</button></div>
            <div class="a-plans">
                <div class="a-plan glass">
                    <h3>Free</h3><div class="a-price">$0<small>/mo</small></div>
                    <ul><li>1 workspace</li><li>1 seat</li><li>Community support</li></ul>
                    <a class="btn btn-outline" style="width:100%;text-align:center" href="#">Get started</a>
                </div>
                <div class="a-plan featured glass">
                    <span class="pop">Most popular</span>
                    <h3>Starter</h3><div class="a-price">$12<small>/mo</small></div>
                    <ul><li>3 seats</li><li>2,500 API calls / mo</li><li>Email support</li></ul>
                    <a class="btn btn-primary" style="width:100%;text-align:center" href="#">Start trial</a>
                </div>
                <div class="a-plan glass">
                    <h3>Pro</h3><div class="a-price">$29<small>/mo</small></div>
                    <ul><li>10 seats</li><li>25,000 API calls / mo</li><li>Priority support</li></ul>
                    <a class="btn btn-outline" style="width:100%;text-align:center" href="#">Start trial</a>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- ============ VARIANT B ============ --}}
<div class="variant-b {{ request('variant', 'A') !== 'B' ? 'hidden' : '' }}">
    <div class="b-page">
    <div class="wrap">
        <nav class="b-nav">
            <div class="b-logo">{{ strtolower(config('app.name')) }}</div>
            <div class="b-nav-links"><a href="#">docs</a><a href="#">pricing</a><a href="#">changelog</a><a href="#">contact</a></div>
            <a class="btn btn-primary" href="#" style="padding:8px 16px;font-size:13px;">sign_up →</a>
        </nav>

        <section class="b-hero">
            <div>
                <span class="badge">v1.0 · laravel 13 · PHP 8.3</span>
                <h1>The SaaS boilerplate for teams that <em>ship billing right</em>.</h1>
                <p>Own your billing stack. Three gateways, webhook idempotency, entitlements, invoices and a mobile API — all in one codebase you can read in an afternoon.</p>
                <a class="btn btn-primary" href="#">deploy in 5 minutes →</a>
                <a class="btn btn-outline" href="#" style="margin-left:8px;">read the docs</a>
                <div class="b-trust" style="margin-top:36px">
                    <div><b>3</b>gateways</div><div><b>124</b>tests</div><div><b>0</b>floats for money</div>
                </div>
            </div>
            <aside>
                <div><span class="cm">// webhook verified &amp; stored</span></div>
                <div><span class="k">POST</span> /webhooks/stripe <span class="ok">200 OK</span></div>
                <div><span class="cm">// entitlements — single source of truth</span></div>
                <div>Entitlements::for(<span class="k">$workspace</span>)->can(<span class="k">'api_access'</span>) <span class="ok">= true</span></div>
                <div><span class="cm">// money is integers, always</span></div>
                <div>amount: <span class="k">1200</span>, currency: <span class="k">'USD'</span></div>
            </aside>
        </section>

        <section class="b-features">
            <div class="b-feature"><h3>billing_core</h3><p>Custom billing layer over Stripe, Paddle and SSLCommerz. Store-then-process webhooks with UNIQUE(gateway, event_id) idempotency.</p></div>
            <div class="b-feature"><h3>entitlements</h3><p>One service decides what a workspace can do. No plan-name string checks anywhere in the codebase.</p></div>
            <div class="b-feature"><h3>workspaces</h3><p>Personal workspace per user on signup. Owner/admin/member/billing roles, invitations, ownership transfer.</p></div>
            <div class="b-feature"><h3>mobile_api</h4></h3><p>/api/v1 with Sanctum abilities, X-Workspace-Id isolation, cursor pagination and Idempotency-Key on payment POSTs.</p></div>
        </section>

        <section class="b-pricing">
            <h2>pricing</h2>
            <p style="color:var(--muted-foreground)">USD via Stripe/Paddle · ৳BDT via SSLCommerz · annual = 10× monthly</p>
            <table class="b-table">
                <thead><tr><th></th><th>Free</th><th class="b-col-pro">Starter</th><th>Pro</th></tr></thead>
                <tbody>
                    <tr><td>price</td>
                        <td class="b-price-cell"><b>$0</b>/mo</td>
                        <td class="b-price-cell b-col-pro"><b>$12</b>/mo</td>
                        <td class="b-price-cell"><b>$29</b>/mo</td></tr>
                    <tr><td>seats</td><td>1</td><td class="b-col-pro">3</td><td>10</td></tr>
                    <tr><td>api calls / mo</td><td>0</td><td class="b-col-pro">2,500</td><td>25,000</td></tr>
                    <tr><td class="optional-col">trial</td><td class="optional-col">—</td><td class="b-col-pro optional-col">14 days</td><td class="optional-col">14 days</td></tr>
                    <tr><td></td>
                        <td><a class="btn btn-outline" style="padding:8px 16px;font-size:13px" href="#">start</a></td>
                        <td class="b-col-pro"><a class="btn btn-primary" style="padding:8px 16px;font-size:13px" href="#">start trial</a></td>
                        <td><a class="btn btn-outline" style="padding:8px 16px;font-size:13px" href="#">start trial</a></td></tr>
                </tbody>
            </table>
        </section>

        <div class="b-foot-cta">
            <div><h3>Stop rebuilding billing.</h3><span style="color:var(--muted-foreground);font-size:14px">Clone, configure, ship.</span></div>
            <a class="btn btn-primary" href="#">get started →</a>
        </div>
    </div>
    </div>
</div>

{{-- Floating switcher — obviously not part of either design --}}
<div id="proto-bar">
    <button type="button" id="proto-prev" aria-label="Previous variant">←</button>
    <span id="proto-label"></span>
    <button type="button" id="proto-next" aria-label="Next variant">→</button>
</div>

<script>
(function () {
    var names = { A: 'A · Cobalt Classic SaaS (light, hero + plan cards)', B: 'B · Bold Terminal (dark, editorial + pricing table)' };
    var current = '{{ request('variant', 'A') }}' === 'B' ? 'B' : 'A';
    var order = ['A', 'B'];
    var label = document.getElementById('proto-label');

    function go(v) {
        var url = new URL(window.location);
        url.searchParams.set('variant', v);
        window.location.href = url.toString();
    }
    function cycle(dir) {
        var i = order.indexOf(current);
        go(order[(i + dir + order.length) % order.length]);
    }
    document.getElementById('proto-prev').onclick = function () { cycle(-1); };
    document.getElementById('proto-next').onclick = function () { cycle(1); };
    label.textContent = names[current] + '  (' + current + '/B)';
    document.addEventListener('keydown', function (e) {
        var t = e.target;
        if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
        if (e.key === 'ArrowLeft') cycle(-1);
        if (e.key === 'ArrowRight') cycle(1);
    });
})();
</script>
</body>
</html>
