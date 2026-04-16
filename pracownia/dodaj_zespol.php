<?php
require_once 'database.php';
require_once 'functions.php';

$errors = [];
$nazwa = "";
$adres = "";

$next_id = (int)$pdo->query("SELECT COALESCE(MAX(ID_ZESP), 0) + 10 FROM zespoly")->fetchColumn();
$max_allowed = 990;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $nazwa = $_POST['nazwa'] ?? '';
    $adres = $_POST['adres'] ?? '';
    if($nazwa=='') $errors['nazwa']="To pole jest wymagane";
    if($adres=='') $errors['adres']="To pole jest wymagane";
    if($next_id < 10) $errors['id']="ID zespołu jest za małe (minimum 10)";
    if($next_id > $max_allowed) $errors['id']="Osiągnięto maksymalną liczbę zespołów (ID nie może przekroczyć $max_allowed)";
    if(empty($errors)){
        $stmt = $pdo->prepare("INSERT INTO zespoly (ID_ZESP, NAZWA, ADRES) VALUES (:id_zesp, :nazwa, :adres)");
        $stmt->bindValue(':id_zesp', $next_id, PDO::PARAM_INT);
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':adres', $adres, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: zespoly.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dodaj zespół</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>.error-input{border:2px solid red!important;}</style>
</head>
<body>
    <?php printnavbar(); ?>
    <main class="container mt-5">
        <form method="POST" class="mt-4">
            <?php if(isset($errors['id'])): ?>
                <div class="alert alert-danger"><?php echo $errors['id']; ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Nazwa</label>
                <input type="text" name="nazwa" class="form-control <?php if(isset($errors['nazwa'])) echo 'error-input'; ?>" value="<?php echo htmlspecialchars($nazwa); ?>">
                <div class="text-danger"><?php echo $errors['nazwa'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Adres</label>
                <input type="text" name="adres" class="form-control <?php if(isset($errors['adres'])) echo 'error-input'; ?>" value="<?php echo htmlspecialchars($adres); ?>">
                <div class="text-danger"><?php echo $errors['adres'] ?? ''; ?></div>
            </div>
            <button type="submit" class="btn btn-success" <?php if($next_id > $max_allowed) echo 'disabled'; ?>>Dodaj</button>
            <a href="zespoly.php" class="btn btn-secondary">Wróć</a>
        </form>
    </main>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>