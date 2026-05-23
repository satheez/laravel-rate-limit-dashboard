# Frequently Asked Questions

### Does this add overhead to my application?
The `RateLimitInstrumenter` middleware is designed to be as lightweight as possible. It merely fires an event to Laravel's Event bus. If you configure Laravel to queue listeners, the actual database writes happen asynchronously in your background workers, resulting in zero latency overhead for the HTTP request.

### Will my database grow infinitely?
No. The package includes an artisan command (`php artisan rate-limit:prune`) that deletes raw events older than the configured `retention_days` (default 30 days). You can schedule this command in your `Console/Kernel.php` to run daily.

### What storage drivers are supported?
Currently, relational databases (`database` - MySQL, PostgreSQL) are fully supported. Experimental support for `redis` is included. You can also implement your own driver by creating a class that implements the `RateLimitStorage` contract.

### Can I block IPs entirely using this?
While you can set the `max_attempts` override to `0` for an IP address in the dashboard to effectively block it from the rate-limited route, for serious DDoS mitigation or global IP bans, you should still use WAFs (like Cloudflare) or firewall-level IP bans. This package is meant for application-level rate limit logic.

### Does it work with third-party rate limiters?
This package relies on Laravel's built-in `Illuminate\Cache\RateLimiter`. If a third-party package bypasses the facade entirely and implements its own logic, our middleware will not be able to instrument it.

### Why do I see "unconfigured_routes" warnings?
The built-in health checks scan your route definitions. If it finds public routes that do not have the `throttle` middleware attached (and aren't explicitly ignored), it raises a warning to remind you to protect your endpoints. You can disable this check in the configuration.
