# Production Deployment Checklist

Before deploying the QR Order Pager SaaS Platform to live production, complete the following verification steps:

- [x] **Database Migrations & Seed**: Run `php bin/console.php migrate` and `php bin/console.php seed`.
- [ ] **Existing Installations**: Run `php bin/console.php migrate-currency-iqd` to set currency labels to IQD without changing stored amounts. Set each product price to its intended IQD amount in the app; do not treat this as a currency conversion. If a prior exchange-rate migration was already applied, restore a verified pre-migration backup or manually correct affected amounts first.
- [x] **VAPID Keys**: Generate production VAPID keypair (`php bin/console.php generate-vapid`) and save in `.env`.
- [x] **HTTPS Enforcement**: Ensure SSL certificate (Let's Encrypt / Cloudflare) is active so Service Workers and Web Push are functional.
- [x] **Tenant Isolation**: Verify that queries enforce `business_id` and cross-tenant access is rejected.
- [x] **State Machine Validation**: Verify that orders cannot transition backwards or reopen once cancelled.
- [x] **Web Push Notification Queue**: Configure cron job to run `php bin/console.php run-notifications` every minute.
- [x] **Automated Database Backups**: Configure nightly cron job for `php bin/console.php backup` and verify retention pruning.
- [x] **Local Business Hub**: Verify edge server startup (`run_hub.bat`) and sync daemon (`hub_sync_worker.php`).
- [x] **Multilingual Rendering**: Verify English (LTR), Kurdish (RTL), and Arabic (RTL) views.
- [x] **Automated Test Suite**: Run `php tests/run_tests.php` and verify all 31 assertions pass.
