<?php

declare(strict_types=1);

return [
    'subject' => getenv('VAPID_SUBJECT') ?: 'mailto:admin@qrpager.io',
    'public_key' => getenv('VAPID_PUBLIC_KEY') ?: '',
    'private_key' => getenv('VAPID_PRIVATE_KEY') ?: '',
];
