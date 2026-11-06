<?php

declare(strict_types=1);

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';
if (!is_file($envFile)) {
    return;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    throw new RuntimeException('Unable to read backend/.env.');
}

foreach ($lines as $index => $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    if (!preg_match('/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
        throw new RuntimeException('Invalid environment setting in backend/.env on line ' . ($index + 1) . '.');
    }

    $name = $matches[1];
    if (getenv($name) !== false) {
        continue;
    }

    $value = trim($matches[2]);
    if (strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"') {
        $value = stripcslashes(substr($value, 1, -1));
    } elseif (strlen($value) >= 2 && $value[0] === "'" && $value[-1] === "'") {
        $value = substr($value, 1, -1);
    } else {
        $value = preg_replace('/\s+#.*$/', '', $value);
        $value = trim($value);
    }

    putenv($name . '=' . $value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
