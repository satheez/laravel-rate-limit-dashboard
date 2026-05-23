# Comparison

There are existing tools in the Laravel ecosystem that monitor requests. Why build or use the **Laravel Rate-Limit Dashboard**?

## 1. Laravel Pulse
**Pulse** provides high-level health and performance monitoring for your application (slow queries, slow routes, exceptions).
- **What it lacks:** Pulse does not provide granular details on rate-limit violations, nor does it allow you to dynamically alter rate limits (max attempts, decay) without changing your source code.
- **How we differ:** We strictly focus on rate-limiting. We track specific limiters, expose runtime configuration, and can send mail alerts when saved limiter thresholds are reached.

## 2. Laravel Telescope
**Telescope** is an incredible local debugging assistant. It tracks requests, exceptions, logs, and more.
- **What it lacks:** Telescope is generally meant for local development and drops heavy data into your database, making it unsuitable for high-traffic production environments. It does not provide dynamic configuration for rate limits.
- **How we differ:** We aggregate data into time windows and provide package-managed limiter controls for routes using the instrumenter middleware.

## 3. Custom Log Parsing / DataDog / New Relic
You can parse HTTP 429 logs and send them to an external observability tool.
- **What it lacks:** You still lack a feedback loop to *control* the limits from your application. You have to redeploy code to change the limits after discovering abuse.
- **How we differ:** Metrics and runtime config live in the same dashboard, so teams can add package-managed overrides without redeploying code.

| Feature | Laravel Pulse | Laravel Telescope | Laravel Rate-Limit Dashboard |
|---|:---:|:---:|:---:|
| Monitor Requests | ✓ | ✓ | ✓ (Rate-limited only) |
| Production-oriented storage | ✓ | ❌ | ✓ |
| Granular Throttled Offender List | ❌ | ❌ | ✓ |
| Dynamic Config overrides via UI | ❌ | ❌ | ✓ |
| Alert on High Limit Utilisation | ❌ | ❌ | ✓ |
