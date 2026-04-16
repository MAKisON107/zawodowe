<?php
require_once 'database.php';
require_once 'functions.php';


if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM etaty WHERE NAZWA = :nazwa");
    $stmt->execute([':nazwa' => $_GET['delete']]);
    header('Location: etaty.php');
    exit;
}

if (isset($_POST['edit_submit'])) {
    $stmt = $pdo->prepare("UPDATE etaty SET NAZWA=:nazwa_new, PLACA_OD=:placa_od, PLACA_DO=:placa_do WHERE NAZWA=:nazwa_old");
    $stmt->execute([
        ':nazwa_new' => $_POST['nazwa'],
        ':placa_od'  => $_POST['placa_od'],
        ':placa_do'  => $_POST['placa_do'],
        ':nazwa_old' => $_POST['edit_id'],
    ]);
    header('Location: etaty.php');
    exit;
}


$edit_row = null;
if (isset($_GET['edit'])) {
    $e = $pdo->prepare("SELECT * FROM etaty WHERE NAZWA = :nazwa");
    $e->execute([':nazwa' => $_GET['edit']]);
    $edit_row = $e->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <title>Hello, world!</title>
</head>
<body>
    <?php 
    printnavbar();
    ?>
    <?php
    if(isset($_POST['submit']) && $_POST['search']!=''){
        $stmt = $pdo->prepare("SELECT * FROM etaty WHERE NAZWA LIKE :nazwa OR PLACA_OD LIKE :placa_od OR PLACA_DO LIKE :placa_do");
        $stmt -> bindValue(':nazwa', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt -> bindValue(':placa_od', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt -> bindValue(':placa_do', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt->execute();
    }
    else{
        $stmt = $pdo->query('SELECT * FROM etaty');
    }
    ?>
    <div class="container">
        <?php if ($edit_row): ?>
        <div class="row my-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Edytuj etat</div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_row['NAZWA']) ?>">
                            <div class="mb-2"><label class="form-label">Nazwa</label><input type="text" class="form-control" name="nazwa" value="<?= htmlspecialchars($edit_row['NAZWA']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Placa od</label><input type="number" step="0.01" class="form-control" name="placa_od" value="<?= htmlspecialchars($edit_row['PLACA_OD']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Placa do</label><input type="number" step="0.01" class="form-control" name="placa_do" value="<?= htmlspecialchars($edit_row['PLACA_DO']) ?>"></div>
                            <button type="submit" name="edit_submit" class="btn btn-success mt-2">Zapisz</button>
                            <a href="etaty.php" class="btn btn-secondary mt-2 ms-2">Anuluj</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <form action="" method="post">
        <div class="row my-5">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Nazwa, płaca od, płaca do...">
            </div>
            <div class="col-auto">
                <input type="submit" class="btn btn-primary" name="submit" value="Szukaj">
            </div>
            <div class="col-auto">
                <a href="dodaj_etat.php" class="btn btn-primary">Dodaj etat</a>
            </div>
        </div>
        </form>
        <div class="row">
            <div class="col-12">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Placa od</th>
                        <th>Placa do</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($stmt as $row){
                        echo '<tr>';
                        echo '<td>'.$row['NAZWA'].'</td>';
                        echo '<td>'.$row['PLACA_OD'].'</td>';
                        echo '<td>'.$row['PLACA_DO'].'</td>';
                        echo '<td>
                            <a href="etaty.php?edit='.urlencode($row['NAZWA']).'" class="btn btn-sm btn-primary me-1">Edytuj</a>
                            <a href="etaty.php?delete='.urlencode($row['NAZWA']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Czy na pewno usunąć? Zastanów się dobrze...\')">Usuń</a>
                        </td>';
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</html>