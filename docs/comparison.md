# Comparison

There are existing tools in the Laravel ecosystem that monitor requests. Why build or use the **Laravel Rate-Limit Dashboard**?

## 1. Laravel Pulse
**Pulse** provides high-level health and performance monitoring for your application (slow queries, slow routes, exceptions).
- **What it lacks:** Pulse does not provide granular details on rate-limit violations, nor does it allow you to dynamically alter rate limits (max attempts, decay) without changing your source code.
- **How we differ:** We strictly focus on rate-limiting. We track specific limiters, allow runtime overrides, and alert specifically when limits are nearing their thresholds.

## 2. Laravel Telescope
**Telescope** is an incredible local debugging assistant. It tracks requests, exceptions, logs, and more.
- **What it lacks:** Telescope is generally meant for local development and drops heavy data into your database, making it unsuitable for high-traffic production environments. It does not provide dynamic configuration for rate limits.
- **How we differ:** We are built for production. We aggregate data into time windows to minimize database overhead and provide a control panel to alter limits on the fly.

## 3. Custom Log Parsing / DataDog / New Relic
You can parse HTTP 429 logs and send them to an external observability tool.
- **What it lacks:** You still lack a feedback loop to *control* the limits from your application. You have to redeploy code to change the limits after discovering abuse.
- **How we differ:** We provide a closed loop. Monitor the metrics in the dashboard, and instantly apply an IP override to block or reduce limits for the offender directly from the same UI.

| Feature | Laravel Pulse | Laravel Telescope | Laravel Rate-Limit Dashboard |
|---|:---:|:---:|:---:|
| Monitor Requests | ✓ | ✓ | ✓ (Rate-limited only) |
| Production Ready | ✓ | ❌ | ✓ |
| Granular Throttled Offender List | ❌ | ❌ | ✓ |
| Dynamic Config overrides via UI | ❌ | ❌ | ✓ |
| Alert on High Limit Utilisation | ❌ | ❌ | ✓ |
