<?php
require_once 'database.php';
require_once 'functions.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM zespoly WHERE ID_ZESP = :id");
    $stmt->execute([':id' => $_GET['delete']]);
    header('Location: zespoly.php');
    exit;
}

if (isset($_POST['edit_submit'])) {
    $stmt = $pdo->prepare("UPDATE zespoly SET NAZWA=:nazwa, ADRES=:adres WHERE ID_ZESP=:id");
    $stmt->execute([
        ':nazwa' => $_POST['nazwa'],
        ':adres' => $_POST['adres'],
        ':id'    => $_POST['edit_id'],
    ]);
    header('Location: zespoly.php');
    exit;
}

$edit_row = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $e = $pdo->prepare("SELECT * FROM zespoly WHERE ID_ZESP = :id");
    $e->execute([':id' => $_GET['edit']]);
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
        $stmt = $pdo->prepare("SELECT * FROM zespoly WHERE NAZWA LIKE :nazwa OR ADRES LIKE :adres");
        $stmt -> bindValue(':nazwa', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt -> bindValue(':adres', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt->execute();
    }
    else{
        $stmt = $pdo->query('SELECT * FROM zespoly');
    }
    ?>
    <div class="container">
        <?php if ($edit_row): ?>
        <div class="row my-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Edytuj zespół</div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_row['ID_ZESP']) ?>">
                            <div class="mb-2"><label class="form-label">Nazwa</label><input type="text" class="form-control" name="nazwa" value="<?= htmlspecialchars($edit_row['NAZWA']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Adres</label><input type="text" class="form-control" name="adres" value="<?= htmlspecialchars($edit_row['ADRES']) ?>"></div>
                            <button type="submit" name="edit_submit" class="btn btn-success mt-2">Zapisz</button>
                            <a href="zespoly.php" class="btn btn-secondary mt-2 ms-2">Anuluj</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <form action="" method="post">
        <div class="row my-5">
            <div class="col-auto">
                <input type="text" class="form-control" name="search" placeholder="Nazwa, adres...">
            </div>
            <div class="col-auto">
                <input type="submit" class="btn btn-primary" name="submit" value="Szukaj">
            </div>
            <div class="col-auto">
                <a href="dodaj_zespol.php" class="btn btn-primary">Dodaj zespół</a>
            </div>
        </div>
        </form>
        <div class="row">
            <div class="col-12">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Id zesp</th>
                        <th>Nazwa</th>
                        <th>Adres</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($stmt as $row){
                        echo '<tr>';
                        echo '<td>'.$row['ID_ZESP'].'</td>';
                        echo '<td>'.$row['NAZWA'].'</td>';
                        echo '<td>'.$row['ADRES'].'</td>';
                        echo '<td>
                            <a href="zespoly.php?edit='.$row['ID_ZESP'].'" class="btn btn-sm btn-primary me-1">Edytuj</a>
                            <a href="zespoly.php?delete='.$row['ID_ZESP'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Czy na pewno usunąć? Zastanów się dobrze...\')">Usuń</a>
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
</body>
</html>