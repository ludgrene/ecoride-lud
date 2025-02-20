<?php
require_once __DIR__ . '/vendor/autoload.php'; // Inclut l'autoload de Composer

use Dotenv\Dotenv;

// Charge les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/.env'); // Assure-toi que le chemin pointe vers ton `.env`
$dotenv->load();
