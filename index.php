<?php
session_start();

// Připojení k databázi
try {
    $db = new PDO("sqlite:profile.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení k databázi: " . $e->getMessage());
}

// Zpracování formulářů - Post -> Redirect -> Get (PRG Pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Přidání
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $_SESSION['message'] = 'Pole nesmí být prázdné.';
            $_SESSION['msg_type'] = 'error';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO interests (name) VALUES (?)");
                $stmt->execute([$name]);
                $_SESSION['message'] = 'Zájem byl přídán.';
                $_SESSION['msg_type'] = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // UNIQUE constraint violation
                    $_SESSION['message'] = 'Tento zájem už existuje.';
                    $_SESSION['msg_type'] = 'error';
                } else {
                    $_SESSION['message'] = 'Chyba: ' . $e->getMessage();
                    $_SESSION['msg_type'] = 'error';
                }
            }
        }
    } 
    // Úprava
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $_SESSION['message'] = 'Pole nesmí být prázdné.';
            $_SESSION['msg_type'] = 'error';
        } else {
            try {
                $stmt = $db->prepare("UPDATE interests SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                $_SESSION['message'] = 'Zájem byl upraven.';
                $_SESSION['msg_type'] = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $_SESSION['message'] = 'Tento zájem už existuje.';
                    $_SESSION['msg_type'] = 'error';
                } else {
                    $_SESSION['message'] = 'Chyba: ' . $e->getMessage();
                    $_SESSION['msg_type'] = 'error';
                }
            }
        }
    } 
    // Smazání
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $db->prepare("DELETE FROM interests WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Zájem byl odstraněn.';
            $_SESSION['msg_type'] = 'success';
        }
    }

    // Redirect (PRG)
    header("Location: index.php");
    exit;
}

// Vytažení dat pro zobrazení (Read)
try {
    $stmt = $db->prepare("SELECT * FROM interests");
    $stmt->execute();
    $interests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Pro případ, že by databáze ještě nebyla inicializována
    $interests = [];
    $db_error = "Tabulka interests neexistuje. Nezapomeňte spustit <a href='init.php'>init.php</a>!";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Profil 6.0</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>IT Profil 6.0 - Správa zájmů</h1>

    <?php if (isset($db_error)): ?>
        <div class="message error"><?= $db_error ?></div>
    <?php endif; ?>

    <!-- Flash messages ze session -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= htmlspecialchars($_SESSION['msg_type']) ?>">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php 
        // Odstranění zprávy po zobrazení
        unset($_SESSION['message']); 
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <!-- PŘIDÁNÍ -->
    <div class="card add-card">
        <h2>Přidat nový zájem</h2>
        <form method="post" class="add-form">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Např. Programování">
            <button type="submit" class="btn btn-add">Přidat</button>
        </form>
    </div>

    <!-- ZOBRAZENÍ / ÚPRAVA / SMAZÁNÍ -->
    <div class="card list-card">
        <h2>Seznam zájmů</h2>
        <?php if (!empty($interests)): ?>
            <div class="interests-list">
                <?php foreach ($interests as $interest): ?>
                    <div class="interest-item">
                        <div class="interest-info">
                            <span class="interest-id">#<?= htmlspecialchars($interest['id']) ?></span>
                            <!-- Formulář pro ÚPRAVU prvku hned vedle textu -->
                            <form method="post" class="inline-form edit-form">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($interest['id']) ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($interest['name']) ?>">
                                <button type="submit" class="btn btn-edit">Upravit</button>
                            </form>
                        </div>
                        
                        <div class="actions">
                            <!-- Formulář pro SMAZÁNÍ prvku -->
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($interest['id']) ?>">
                                <button type="submit" class="btn btn-delete">Smazat</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-msg">Nebyly nalezeny žádné zájmy.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
