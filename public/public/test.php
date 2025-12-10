<?php
try {
    new PDO("mysql:host=tapresr689.mysql.db;dbname=tapresr689;charset=utf8mb4", "tapresr689", "Najim09112022");
    echo "Connexion OK";
} catch (Exception $e) {
    echo $e->getMessage();
}
