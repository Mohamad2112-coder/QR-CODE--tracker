-- Multi-Tenant QR Order Pager SaaS Platform Schema
-- PostgreSQL compatible (Supabase SQL Editor)


-- 1. Businesses (Tenants)

CREATE TABLE IF NOT EXISTS "businesses" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "name" VARCHAR(150) NOT NULL,
    "slug" VARCHAR(100) NOT NULL UNIQUE,
    "email" VARCHAR(150) NOT NULL,
    "phone" VARCHAR(50) NULL,
    "default_language" VARCHAR(10) NOT NULL DEFAULT 'en',
    "status" VARCHAR(9) NOT NULL DEFAULT 'trial' CHECK ("status" IN ('trial', 'active', 'suspended', 'cancelled')),
    "trial_ends_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_biz_status" ON "businesses" ("status");

CREATE INDEX IF NOT EXISTS "idx_biz_slug" ON "businesses" ("slug");

-- 2. Business Settings

CREATE TABLE IF NOT EXISTS "business_settings" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL UNIQUE,
    "currency_symbol" VARCHAR(10) NOT NULL DEFAULT 'IQD',
    "order_number_prefix" VARCHAR(10) NOT NULL DEFAULT 'ORD-',
    "next_order_number" INT NOT NULL DEFAULT 1,
    "qr_expiration_grace_minutes" INT NOT NULL DEFAULT 60,
    "currency_iqd_rate" DECIMAL(12,4) NOT NULL DEFAULT 1580,
    "estimated_prep_time_minutes" INT NOT NULL DEFAULT 15,
    "enable_offline_hub" BOOLEAN NOT NULL DEFAULT TRUE,
    "hub_secret_key" VARCHAR(64) NOT NULL,
    "notification_ready_message_en" TEXT NULL,
    "notification_ready_message_ar" TEXT NULL,
    "notification_ready_message_ku" TEXT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_bs_business" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE
);

-- 3. Business Branding

CREATE TABLE IF NOT EXISTS "business_branding" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL UNIQUE,
    "primary_color" VARCHAR(20) NOT NULL DEFAULT '#2563eb',
    "accent_color" VARCHAR(20) NOT NULL DEFAULT '#f59e0b',
    "background_color" VARCHAR(20) NOT NULL DEFAULT '#f8fafc',
    "logo_url" VARCHAR(255) NULL,
    "tagline" VARCHAR(255) NULL,
    "custom_header_html" TEXT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_bb_business" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE
);

-- 4. Subscription Plans

CREATE TABLE IF NOT EXISTS "subscription_plans" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "code" VARCHAR(50) NOT NULL UNIQUE,
    "name" VARCHAR(100) NOT NULL,
    "monthly_price" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "yearly_price" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "max_orders_per_month" INT NOT NULL DEFAULT 500,
    "max_staff_members" INT NOT NULL DEFAULT 5,
    "max_devices" INT NOT NULL DEFAULT 3,
    "allow_offline_hub" BOOLEAN NOT NULL DEFAULT TRUE,
    "allow_advanced_branding" BOOLEAN NOT NULL DEFAULT FALSE,
    "allow_exports" BOOLEAN NOT NULL DEFAULT FALSE,
    "data_retention_days" INT NOT NULL DEFAULT 90,
    "is_active" BOOLEAN NOT NULL DEFAULT TRUE,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

-- 5. Subscriptions

CREATE TABLE IF NOT EXISTS "subscriptions" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "plan_id" VARCHAR(36) NOT NULL,
    "billing_cycle" VARCHAR(7) NOT NULL DEFAULT 'monthly' CHECK ("billing_cycle" IN ('monthly', 'yearly')),
    "status" VARCHAR(12) NOT NULL DEFAULT 'trial' CHECK ("status" IN ('trial', 'active', 'grace_period', 'suspended', 'cancelled')),
    "starts_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "ends_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "trial_ends_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_sub_business" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE,
    CONSTRAINT "fk_sub_plan" FOREIGN KEY ("plan_id") REFERENCES "subscription_plans" ("id")
);

CREATE INDEX IF NOT EXISTS "idx_sub_biz" ON "subscriptions" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_sub_status" ON "subscriptions" ("status");

-- 6. Subscription Payments (Manual Verification)

CREATE TABLE IF NOT EXISTS "subscription_payments" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "subscription_id" VARCHAR(36) NOT NULL,
    "plan_id" VARCHAR(36) NOT NULL,
    "amount" DECIMAL(10,2) NOT NULL,
    "currency" VARCHAR(10) NOT NULL DEFAULT 'IQD',
    "payment_method" VARCHAR(50) NOT NULL DEFAULT 'manual_bank_transfer',
    "reference_note" VARCHAR(255) NULL,
    "status" VARCHAR(8) NOT NULL DEFAULT 'pending' CHECK ("status" IN ('pending', 'verified', 'rejected')),
    "verified_by_user_id" VARCHAR(36) NULL,
    "verified_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_pay_biz" ON "subscription_payments" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_pay_status" ON "subscription_payments" ("status");

-- 7. Roles & Permissions

CREATE TABLE IF NOT EXISTS "roles" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "name" VARCHAR(50) NOT NULL UNIQUE,
    "description" VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS "permissions" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL UNIQUE,
    "description" VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS "role_permissions" (
    "role_id" VARCHAR(36) NOT NULL,
    "permission_id" VARCHAR(36) NOT NULL,
    PRIMARY KEY ("role_id", "permission_id"),
    CONSTRAINT "fk_rp_role" FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE,
    CONSTRAINT "fk_rp_perm" FOREIGN KEY ("permission_id") REFERENCES "permissions" ("id") ON DELETE CASCADE
);

-- 8. Users

CREATE TABLE IF NOT EXISTS "users" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NULL, -- NULL for platform super_admin
    "role_id" VARCHAR(36) NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "email" VARCHAR(150) NOT NULL UNIQUE,
    "password_hash" VARCHAR(255) NOT NULL,
    "is_active" BOOLEAN NOT NULL DEFAULT TRUE,
    "last_login_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_usr_biz" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE,
    CONSTRAINT "fk_usr_role" FOREIGN KEY ("role_id") REFERENCES "roles" ("id")
);

CREATE INDEX IF NOT EXISTS "idx_usr_biz" ON "users" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_usr_role" ON "users" ("role_id");

-- 9. Product Categories

CREATE TABLE IF NOT EXISTS "product_categories" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "display_order" INT NOT NULL DEFAULT 0,
    "is_active" BOOLEAN NOT NULL DEFAULT TRUE,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_cat_biz" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_cat_biz" ON "product_categories" ("business_id");

-- 10. Products

CREATE TABLE IF NOT EXISTS "products" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "category_id" VARCHAR(36) NULL,
    "name" VARCHAR(150) NOT NULL,
    "description" TEXT NULL,
    "price" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "image_url" VARCHAR(255) NULL,
    "is_available" BOOLEAN NOT NULL DEFAULT TRUE,
    "is_active" BOOLEAN NOT NULL DEFAULT TRUE,
    "display_order" INT NOT NULL DEFAULT 0,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_prd_biz" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE,
    CONSTRAINT "fk_prd_cat" FOREIGN KEY ("category_id") REFERENCES "product_categories" ("id") ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS "idx_prd_biz" ON "products" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_prd_cat" ON "products" ("category_id");

-- 11. Orders

CREATE TABLE IF NOT EXISTS "orders" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "order_number" VARCHAR(50) NOT NULL,
    "tracking_token" VARCHAR(64) NOT NULL UNIQUE,
    "status" VARCHAR(9) NOT NULL DEFAULT 'pending' CHECK ("status" IN ('pending', 'preparing', 'ready', 'collected', 'cancelled')),
    "total_amount" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "customer_notes" TEXT NULL,
    "staff_notes" TEXT NULL,
    "created_by_user_id" VARCHAR(36) NULL,
    "assigned_staff_id" VARCHAR(36) NULL,
    "is_offline_created" BOOLEAN NOT NULL DEFAULT FALSE,
    "sync_state" VARCHAR(12) NOT NULL DEFAULT 'synced' CHECK ("sync_state" IN ('synced', 'pending_sync', 'conflict')),
    "local_device_id" VARCHAR(64) NULL,
    "idempotency_key" VARCHAR(128) NULL UNIQUE,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "preparing_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "ready_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "collected_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "cancelled_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    CONSTRAINT "fk_ord_biz" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_ord_biz" ON "orders" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_ord_status" ON "orders" ("status");

CREATE INDEX IF NOT EXISTS "idx_ord_num" ON "orders" ("order_number");

CREATE INDEX IF NOT EXISTS "idx_ord_token" ON "orders" ("tracking_token");

CREATE INDEX IF NOT EXISTS "idx_ord_created" ON "orders" ("created_at");

-- 12. Order Items (Preserving snapshots of product name and price)

CREATE TABLE IF NOT EXISTS "order_items" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "order_id" VARCHAR(36) NOT NULL,
    "product_id" VARCHAR(36) NULL,
    "product_name_snapshot" VARCHAR(150) NOT NULL,
    "unit_price_snapshot" DECIMAL(10,2) NOT NULL,
    "quantity" INT NOT NULL DEFAULT 1,
    "item_notes" TEXT NULL,
    "subtotal" DECIMAL(10,2) NOT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_oi_order" FOREIGN KEY ("order_id") REFERENCES "orders" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_oi_order" ON "order_items" ("order_id");

-- 13. Order Status History (Audit trail of every transition)

CREATE TABLE IF NOT EXISTS "order_status_history" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "order_id" VARCHAR(36) NOT NULL,
    "previous_status" VARCHAR(30) NULL,
    "new_status" VARCHAR(30) NOT NULL,
    "changed_by_user_id" VARCHAR(36) NULL,
    "source" VARCHAR(16) NOT NULL DEFAULT 'cloud_web' CHECK ("source" IN ('cloud_web', 'local_hub', 'cashier_offline', 'kitchen_terminal', 'system')),
    "device_info" VARCHAR(255) NULL,
    "is_offline_transition" BOOLEAN NOT NULL DEFAULT FALSE,
    "transition_time" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_osh_order" FOREIGN KEY ("order_id") REFERENCES "orders" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_osh_order" ON "order_status_history" ("order_id");

-- 14. Push Subscriptions (Web Push API)

CREATE TABLE IF NOT EXISTS "push_subscriptions" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "order_id" VARCHAR(36) NOT NULL,
    "endpoint" VARCHAR(1000) NOT NULL,
    "p256dh_key" VARCHAR(255) NOT NULL,
    "auth_token" VARCHAR(255) NOT NULL,
    "user_agent" VARCHAR(255) NULL,
    "is_active" BOOLEAN NOT NULL DEFAULT TRUE,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_ps_order" FOREIGN KEY ("order_id") REFERENCES "orders" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_ps_order" ON "push_subscriptions" ("order_id");

CREATE INDEX IF NOT EXISTS "idx_ps_biz" ON "push_subscriptions" ("business_id");

-- 15. Notification Jobs (Idempotent Ready notification queue)

CREATE TABLE IF NOT EXISTS "notification_jobs" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "order_id" VARCHAR(36) NOT NULL,
    "subscription_id" VARCHAR(36) NOT NULL,
    "idempotency_key" VARCHAR(128) NOT NULL UNIQUE,
    "status" VARCHAR(10) NOT NULL DEFAULT 'pending' CHECK ("status" IN ('pending', 'processing', 'sent', 'failed', 'expired')),
    "attempts" INT NOT NULL DEFAULT 0,
    "payload_json" TEXT NOT NULL,
    "error_message" TEXT NULL,
    "sent_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_nj_status" ON "notification_jobs" ("status");

CREATE INDEX IF NOT EXISTS "idx_nj_order" ON "notification_jobs" ("order_id");

-- 16. Notification Logs

CREATE TABLE IF NOT EXISTS "notification_logs" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "order_id" VARCHAR(36) NOT NULL,
    "status" VARCHAR(30) NOT NULL,
    "status_code" INT NULL,
    "response_body" TEXT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_nl_biz" ON "notification_logs" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_nl_order" ON "notification_logs" ("order_id");

-- 17. Local Devices (Registered for Local Business Hub / LAN)

CREATE TABLE IF NOT EXISTS "local_devices" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "device_uuid" VARCHAR(64) NOT NULL UNIQUE,
    "device_name" VARCHAR(100) NOT NULL,
    "device_type" VARCHAR(15) NOT NULL CHECK ("device_type" IN ('hub_server', 'cashier_pos', 'kitchen_kds', 'backup_terminal')),
    "auth_token_hash" VARCHAR(255) NOT NULL,
    "last_ip_address" VARCHAR(50) NULL,
    "last_heartbeat_at" TIMESTAMP WITHOUT TIME ZONE NULL,
    "is_authorized" BOOLEAN NOT NULL DEFAULT TRUE,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT "fk_ld_biz" FOREIGN KEY ("business_id") REFERENCES "businesses" ("id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_ld_biz" ON "local_devices" ("business_id");

-- 18. Sync Batches (Edge to Cloud synchronization)

CREATE TABLE IF NOT EXISTS "sync_batches" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NOT NULL,
    "device_uuid" VARCHAR(64) NOT NULL,
    "batch_id" VARCHAR(64) NOT NULL UNIQUE,
    "orders_count" INT NOT NULL DEFAULT 0,
    "status_updates_count" INT NOT NULL DEFAULT 0,
    "conflicts_count" INT NOT NULL DEFAULT 0,
    "status" VARCHAR(16) NOT NULL DEFAULT 'completed' CHECK ("status" IN ('pending', 'completed', 'partially_failed', 'failed')),
    "details_json" TEXT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_sb_biz" ON "sync_batches" ("business_id");

-- 19. Sync Events (Itemized records of sync processing)

CREATE TABLE IF NOT EXISTS "sync_events" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "sync_batch_id" VARCHAR(36) NOT NULL,
    "business_id" VARCHAR(36) NOT NULL,
    "entity_type" VARCHAR(50) NOT NULL,
    "entity_id" VARCHAR(64) NOT NULL,
    "action" VARCHAR(50) NOT NULL,
    "resolution" VARCHAR(19) NOT NULL CHECK ("resolution" IN ('applied', 'skipped_duplicate', 'conflict_cloud_wins', 'error')),
    "message" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_se_batch" ON "sync_events" ("sync_batch_id");

CREATE INDEX IF NOT EXISTS "idx_se_biz" ON "sync_events" ("business_id");

-- 20. Audit Logs

CREATE TABLE IF NOT EXISTS "audit_logs" (
    "id" VARCHAR(36) NOT NULL PRIMARY KEY,
    "business_id" VARCHAR(36) NULL,
    "user_id" VARCHAR(36) NULL,
    "event_type" VARCHAR(100) NOT NULL,
    "description" TEXT NOT NULL,
    "ip_address" VARCHAR(50) NULL,
    "user_agent" VARCHAR(255) NULL,
    "context_json" TEXT NULL,
    "created_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX IF NOT EXISTS "idx_al_biz" ON "audit_logs" ("business_id");

CREATE INDEX IF NOT EXISTS "idx_al_event" ON "audit_logs" ("event_type");

CREATE INDEX IF NOT EXISTS "idx_al_created" ON "audit_logs" ("created_at");

-- 21. Platform Settings

CREATE TABLE IF NOT EXISTS "platform_settings" (
    "setting_key" VARCHAR(100) NOT NULL PRIMARY KEY,
    "setting_value" TEXT NOT NULL,
    "description" VARCHAR(255) NULL,
    "updated_at" TIMESTAMP WITHOUT TIME ZONE NOT NULL
);
