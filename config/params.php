<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'bot_id' => '8461352654:AAGxgiJVcy2ScgSO6p5akN4gzzSEC25ZlQM',
    'bot_name' => 'building03_bot',
    'bsVersion' => '4.6.0',
    'notify_delay' => 1200,
    'maskMoneyOptions' => [
        'prefix' => '₽', // Ruble Symbol
        'suffix' => '',
        'affixesStay' => true, // Keeps the symbol in place
        'thousands' => ' ', // Space for thousands separator
        'decimal' => ',', // Comma for decimal
        'precision' => 2,
        'allowZero' => true,
        'allowNegative' => false,
    ],
];
