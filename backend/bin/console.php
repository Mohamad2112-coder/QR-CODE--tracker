<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Services\BackupService;
use App\Services\NotificationService;
use Minishlink\WebPush\VAPID;

$command = $argv[1] ?? 'help';

echo "=========================================================\n";
echo "   QR Order Pager SaaS Platform CLI Console              \n";
echo "=========================================================\n";

switch ($command) {
    case 'migrate':
        echo "Executing database migrations...\n";
        $pdo = Database::getConnection();
        $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
        if ($sql === false) {
            throw new RuntimeException('Unable to read the MySQL database schema.');
        }
        $pdo->exec($sql);
        echo "Database schema migrated successfully!\n";
        break;

    case 'migrate-currency-iqd':
        $db = Database::getConnection();
        $migrationKey = 'currency_migration_iqd_direct';
        $stmtMigration = $db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ?");
        $stmtMigration->execute([$migrationKey]);
        if ($stmtMigration->fetchColumn() !== false) {
            echo "IQD currency settings are already configured. No prices were changed.\n";
            break;
        }

        $stmtLegacyMigration = $db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ?");
        $stmtLegacyMigration->execute(['currency_migration_usd_to_iqd_1580']);
        if ($stmtLegacyMigration->fetchColumn() !== false) {
            fwrite(STDERR, "A previous exchange-rate migration is recorded. This command will not change prices. Restore a verified pre-migration backup or manually correct affected amounts before continuing.\n");
            exit(1);
        }

        $stmtCurrency = $db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = 'default_currency'");
        $stmtCurrency->execute();
        $currentCurrency = $stmtCurrency->fetchColumn();
        if ($currentCurrency !== false && !in_array($currentCurrency, ['USD', 'IQD'], true)) {
            fwrite(STDERR, "Currency setup stopped: the platform has an unexpected currency. Verify the database before proceeding.\n");
            exit(1);
        }

        $db->beginTransaction();
        try {
            $db->exec("UPDATE business_settings SET currency_symbol = 'IQD'");
            $db->exec("
                INSERT INTO platform_settings (setting_key, setting_value, description, updated_at)
                VALUES ('default_currency', 'IQD', 'Default billing currency', NOW())
                ON DUPLICATE KEY UPDATE setting_value = 'IQD', updated_at = NOW()
            ");
            $db->prepare("
                INSERT INTO platform_settings (setting_key, setting_value, description, updated_at)
                VALUES (?, 'applied', 'Set currency labels to IQD without converting stored amounts', NOW())
            ")->execute([$migrationKey]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }

        echo "Currency labels set to IQD. Product prices, orders, and subscription amounts were not changed.\n";
        break;

    case 'seed':
        echo "Seeding database with initial platform & tenant data...\n";
        require_once __DIR__ . '/../database/seed.php';
        seedDatabase(Database::getConnection());
        break;

    case 'generate-vapid':
        echo "Generating new VAPID cryptographic keypair for Web Push...\n";
        try {
            $keys = VAPID::createVapidKeys();
            echo "VAPID Public Key:\n" . $keys['publicKey'] . "\n\n";
            echo "VAPID Private Key:\n" . $keys['privateKey'] . "\n\n";
            echo "Add these to your .env or config/vapid.php\n";
        } catch (\Throwable $e) {
            echo "Error generating VAPID keys: " . $e->getMessage() . "\n";
        }
        break;

    case 'run-notifications':
        echo "Dispatching pending push notification jobs...\n";
        $result = NotificationService::processPendingJobs(50);
        echo "Jobs Processed: {$result['processed']} | Successful: {$result['successful']} | Failed: {$result['failed']}\n";
        break;

    case 'backup':
        echo "Creating automated database backup...\n";
        $res = BackupService::createBackup();
        echo "Backup created successfully: {$res['filename']} ({$res['size_bytes']} bytes)\n";
        break;

    case 'restore':
        $filepath = $argv[2] ?? null;
        if (!$filepath) {
            echo "Error: Please specify the backup SQL file to restore.\n";
            echo "Usage: php bin/console.php restore storage/backups/backup_xxxx.sql\n";
            exit(1);
        }
        echo "Restoring database from: {$filepath}...\n";
        BackupService::restoreBackup($filepath);
        echo "Database restored successfully!\n";
        break;

    case 'list-backups':
        echo "Listing available database backups:\n";
        $list = BackupService::listBackups();
        if (empty($list)) {
            echo "No backups found.\n";
        } else {
            foreach ($list as $b) {
                echo "• {$b['filename']} (" . round($b['size_bytes'] / 1024, 1) . " KB) - {$b['modified_at']}\n";
            }
        }
        break;

    default:
        echo "Available Commands:\n";
        echo "  php bin/console.php migrate             Run schema migrations\n";
        echo "  php bin/console.php migrate-currency-iqd Set IQD labels without converting amounts\n";
        echo "  php bin/console.php seed                Seed roles, plans, demo tenant & orders\n";
        echo "  php bin/console.php generate-vapid      Generate Web Push VAPID keypair\n";
        echo "  php bin/console.php run-notifications   Dispatch queued Ready notifications\n";
        echo "  php bin/console.php backup              Create a full database backup\n";
        echo "  php bin/console.php restore <file>      Restore database from SQL dump\n";
        echo "  php bin/console.php list-backups        List all existing backup files\n";
        break;
}
