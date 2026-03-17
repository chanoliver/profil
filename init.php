<?php
// Připojení k databázi SQLite
$db = new PDO("sqlite:profile.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vytvoření tabulky
$sql = "CREATE TABLE interests (
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    name TEXT NOT NULL UNIQUE
)";

try {
    $db->exec($sql);
    echo "<h1>Inicializace úspěšná</h1>";
    echo "<p>Databáze profile.db a tabulka 'interests' byly úspěšně vytvořeny.</p>";
    echo "<a href='index.php'>Přejít do aplikace</a>";
} catch (PDOException $e) {
    echo "<h1>Chyba inicializace</h1>";
    echo "<p>Tabulka možná již existuje nebo nastala jiná chyba: " . $e->getMessage() . "</p>";
}
?>
