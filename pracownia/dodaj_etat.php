<?php
require_once 'database.php';
require_once 'functions.php';

$errors = [];
$nazwa = "";
$placa_od = "";
$placa_do = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nazwa = $_POST['nazwa'] ?? '';
    $placa_od = $_POST['placa_od'] ?? '';
    $placa_do = $_POST['placa_do'] ?? '';

    $placa_od = str_replace(',', '.', trim($placa_od));
    $placa_do = str_replace(',', '.', trim($placa_do));

    $digits_od = preg_replace('/\D/', '', $placa_od);
    $digits_do = preg_replace('/\D/', '', $placa_do);

    if (strlen($digits_od) > 5) {
        $errors['placa_od'] = "Liczba może mieć maksymalnie 5 cyfr";
    }
    if (strlen($digits_do) > 5) {
        $errors['placa_do'] = "Liczba może mieć maksymalnie 5 cyfr";
    }

    $placa_od_val = is_numeric($placa_od) ? (float)$placa_od : null;
    $placa_do_val = is_numeric($placa_do) ? (float)$placa_do : null;

    if ($nazwa === '') {
        $errors['nazwa'] = "To pole jest wymagane";
    }

    if (!isset($errors['placa_od'])) {
        if ($placa_od_val === null) {
            $errors['placa_od'] = "Podaj poprawną liczbę";
        } elseif ($placa_od_val < 500) {
            $errors['placa_od'] = "Płaca od nie może być mniejsza niż 500 zł";
        }
    }

    if (!isset($errors['placa_do'])) {
        if ($placa_do_val === null) {
            $errors['placa_do'] = "Podaj poprawną liczbę";
        } elseif ($placa_do_val > 20000) {
            $errors['placa_do'] = "Płaca do nie może być większa niż 20000 zł";
        }
    }

    if (empty($errors) && $placa_od_val > $placa_do_val) {
        $errors['placa_do'] = "Płaca do musi być większa niż płaca od";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO etaty (NAZWA, PLACA_OD, PLACA_DO) VALUES (:nazwa, :placa_od, :placa_do)");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':placa_od', $placa_od_val);
        $stmt->bindValue(':placa_do', $placa_do_val);
        $stmt->execute();
        header("Location: etaty.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dodaj etat</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>.error-input{border:2px solid red!important;}</style>
</head>
<body>
<?php printnavbar(); ?>
<main class="container mt-5">
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Nazwa</label>
            <input type="text" name="nazwa" class="form-control <?php if(isset($errors['nazwa'])) echo 'error-input'; ?>" value="<?php echo htmlspecialchars($nazwa); ?>">
            <div class="text-danger"><?php echo $errors['nazwa'] ?? ''; ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Płaca od</label>
            <input type="text" name="placa_od" class="form-control <?php if(isset($errors['placa_od'])) echo 'error-input'; ?>" value="<?php echo htmlspecialchars($placa_od); ?>">
            <div class="text-danger"><?php echo $errors['placa_od'] ?? ''; ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Płaca do</label>
            <input type="text" name="placa_do" class="form-control <?php if(isset($errors['placa_do'])) echo 'error-input'; ?>" value="<?php echo htmlspecialchars($placa_do); ?>">
            <div class="text-danger"><?php echo $errors['placa_do'] ?? ''; ?></div>
        </div>
        <button type="submit" class="btn btn-success">Dodaj</button>
        <a href="etaty.php" class="btn btn-secondary">Wróć</a>
    </form>
</main>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
