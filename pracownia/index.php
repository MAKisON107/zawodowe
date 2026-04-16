<?php
require_once 'database.php';
require_once 'functions.php';


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM pracownicy WHERE ID_SZEFA = :id");
    $check->execute([':id' => $_GET['delete']]);
    if($check->fetchColumn() > 0) {
        $delete_error = "Nie można usunąć pracownika, który jest szefem innych pracowników.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM pracownicy WHERE ID_PRAC = :id");
        $stmt->execute([':id' => $_GET['delete']]);
        header('Location: index.php');
        exit;
    }
}


if (isset($_POST['edit_submit'])) {
    $errors = [];
    
    if(empty($_POST['placa_pod']) || !is_numeric($_POST['placa_pod'])) {
        $errors['placa_pod'] = "Podaj poprawną liczbę";
    }
    if($_POST['placa_dod'] != '' && !is_numeric($_POST['placa_dod'])) {
        $errors['placa_dod'] = "Musi być liczbą";
    }
    
    if(empty($errors) && $_POST['etat'] != '') {
        $e = $pdo->prepare("SELECT PLACA_OD, PLACA_DO FROM etaty WHERE NAZWA = :nazwa");
        $e->execute([':nazwa' => $_POST['etat']]);
        $etat_row = $e->fetch(PDO::FETCH_ASSOC);
        if($etat_row){
            if($_POST['placa_pod'] < $etat_row['PLACA_OD'] || $_POST['placa_pod'] > $etat_row['PLACA_DO']){
                $errors['placa_pod'] = "Płaca musi być między ".$etat_row['PLACA_OD']." a ".$etat_row['PLACA_DO'];
            }
        }
    }
    
    if(empty($errors)){
        $stmt = $pdo->prepare("UPDATE pracownicy SET IMIE=:imie, NAZWISKO=:nazwisko, ETAT=:etat, ZATRUDNIONY=:zatrudniony, PLACA_POD=:placa_pod, PLACA_DOD=:placa_dod, ID_SZEFA=:szef, ID_ZESP=:zesp WHERE ID_PRAC=:id");
        $stmt->bindValue(':imie',        $_POST['imie'],                                            PDO::PARAM_STR);
        $stmt->bindValue(':nazwisko',    $_POST['nazwisko'],                                        PDO::PARAM_STR);
        $stmt->bindValue(':etat',        $_POST['etat'],                                            PDO::PARAM_STR);
        $stmt->bindValue(':zatrudniony', $_POST['zatrudniony'],                                     PDO::PARAM_STR);
        $stmt->bindValue(':placa_pod',   $_POST['placa_pod'],                                       PDO::PARAM_STR);
        $stmt->bindValue(':placa_dod',   $_POST['placa_dod'] !== '' ? $_POST['placa_dod'] : null,   PDO::PARAM_STR);
        $stmt->bindValue(':szef',        $_POST['szef']       !== '' ? (int)$_POST['szef'] : null,  PDO::PARAM_INT);
        $stmt->bindValue(':zesp',        $_POST['zesp']       !== '' ? (int)$_POST['zesp'] : null,  PDO::PARAM_INT);
        $stmt->bindValue(':id',          $_POST['edit_id'],                                         PDO::PARAM_INT);
        $stmt->execute();
        header('Location: index.php');
        exit;
    }
}


$edit_row = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $e = $pdo->prepare("SELECT * FROM pracownicy WHERE ID_PRAC = :id");
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
    <style>.error-input{border:2px solid red!important;}</style>
</head>
<body>
    <?php
    printnavbar();
    ?>
    <?php
    $sql = "SELECT p.*, s.IMIE as SZEF_IMIE, s.NAZWISKO as SZEF_NAZWISKO, z.NAZWA as ZESPOL FROM pracownicy p LEFT JOIN pracownicy s ON p.ID_SZEFA = s.ID_PRAC LEFT JOIN zespoly z ON p.ID_ZESP = z.ID_ZESP";
    if(isset($_POST['submit']) && $_POST['search']!=''){
        $stmt = $pdo->prepare($sql . " WHERE p.IMIE LIKE :s OR p.NAZWISKO LIKE :s OR p.ETAT LIKE :s OR p.ZATRUDNIONY LIKE :s OR p.PLACA_POD LIKE :s OR p.PLACA_DOD LIKE :s OR z.NAZWA LIKE :s OR s.IMIE LIKE :s OR s.NAZWISKO LIKE :s");
        $stmt -> bindValue(':s', '%'.$_POST['search'].'%', PDO::PARAM_STR);
        $stmt->execute();
    }
    else{
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    ?>
    <div class="container">
        <?php if (isset($delete_error)): ?>
        <div class="alert alert-danger mt-3"><?php echo $delete_error; ?></div>
        <?php endif; ?>
        <?php if ($edit_row): ?>
        <div class="row my-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Edytuj pracownika</div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_row['ID_PRAC']) ?>">
                            <div class="mb-2"><label class="form-label">Imię</label><input type="text" class="form-control" name="imie" value="<?= htmlspecialchars($edit_row['IMIE']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Nazwisko</label><input type="text" class="form-control" name="nazwisko" value="<?= htmlspecialchars($edit_row['NAZWISKO']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Etat</label>
                            <select name="etat" class="form-control">
                                <option value="">-- wybierz --</option>
                                <?php
                                    $e = $pdo->query("SELECT NAZWA FROM etaty");
                                    while($r = $e->fetch(PDO::FETCH_ASSOC)){
                                        echo "<option value='".$r['NAZWA']."'";
                                        if($edit_row['ETAT']==$r['NAZWA']) echo " selected";
                                        echo ">".$r['NAZWA']."</option>";
                                    }
                                ?>
                            </select>
                            </div>
                            <div class="mb-2"><label class="form-label">Zatrudniony</label><input type="date" class="form-control" name="zatrudniony" value="<?= htmlspecialchars($edit_row['ZATRUDNIONY']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Placa pod</label><input type="number" step="0.01" class="form-control <?php if(isset($errors['placa_pod'])) echo 'error-input'; ?>" name="placa_pod" value="<?= htmlspecialchars($edit_row['PLACA_POD']) ?>"></div>
                            <div class="text-danger"><?php echo $errors['placa_pod'] ?? ''; ?></div>
                            <div class="mb-2"><label class="form-label">Placa dod</label><input type="number" step="0.01" class="form-control" name="placa_dod" value="<?= htmlspecialchars($edit_row['PLACA_DOD']) ?>"></div>
                            <div class="mb-2"><label class="form-label">Szef</label>
                            <select name="szef" class="form-control">
                                <option value="">-- wybierz --</option>
                                <?php
                                    $s = $pdo->query("SELECT ID_PRAC, IMIE, NAZWISKO FROM pracownicy");
                                    while($r = $s->fetch(PDO::FETCH_ASSOC)){
                                        echo "<option value='".$r['ID_PRAC']."'";
                                        if($edit_row['ID_SZEFA']==$r['ID_PRAC']) echo " selected";
                                        echo ">".$r['IMIE']." ".$r['NAZWISKO']."</option>";
                                    }
                                ?>
                            </select>
                            </div>
                            <div class="mb-2"><label class="form-label">Zespół</label>
                            <select name="zesp" class="form-control">
                                <option value="">-- wybierz --</option>
                                <?php
                                    $z = $pdo->query("SELECT ID_ZESP, NAZWA FROM zespoly");
                                    while($r = $z->fetch(PDO::FETCH_ASSOC)){
                                        echo "<option value='".$r['ID_ZESP']."'";
                                        if($edit_row['ID_ZESP']==$r['ID_ZESP']) echo " selected";
                                        echo ">".$r['NAZWA']."</option>";
                                    }
                                ?>
                            </select>
                            </div>
                            <button type="submit" name="edit_submit" class="btn btn-success mt-2">Zapisz</button>
                            <a href="index.php" class="btn btn-secondary mt-2 ms-2">Anuluj</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <form action="" method="post">
        <div class="row my-5">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Imię, nazwisko, etat...">
            </div>
            <div class="col-auto">
                <input type="submit" class="btn btn-primary" name="submit" value="Szukaj">
            </div>
            <div class="col-auto">
                <a href="dodaj_pracownika.php" class="btn btn-primary">Dodaj pracownika</a>
            </div>
        </div>
        </form>
        <div class="row">
            <div class="col-12">

                <table class="table">
                    <thead>
                    <tr>
                        <th>Id prac</th>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>Etat</th>
                        <th>Szef</th>
                        <th>Zatrudniony</th>
                        <th>Placa pod</th>
                        <th>Placa dod</th>
                        <th>Zespół</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($stmt as $row){
                        echo '<tr>';
                        echo '<td>'.$row['ID_PRAC'].'</td>';
                        echo '<td>'.$row['IMIE'].'</td>';
                        echo '<td>'.$row['NAZWISKO'].'</td>';
                        echo '<td>'.$row['ETAT'].'</td>';
                        echo '<td>'.(!empty($row['SZEF_IMIE']) ? $row['SZEF_IMIE'].' '.$row['SZEF_NAZWISKO'] : ' '). '</td>';
                        echo '<td>'.$row['ZATRUDNIONY'].'</td>';
                        echo '<td>'.$row['PLACA_POD'].'</td>';
                        echo '<td>'.$row['PLACA_DOD'].'</td>';
                        echo '<td>'.$row['ZESPOL'].'</td>';
                        echo '<td>
                            <a href="index.php?edit='.$row['ID_PRAC'].'" class="btn btn-sm btn-primary me-1">Edytuj</a>
                            <a href="index.php?delete='.$row['ID_PRAC'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Czy na pewno usunąć? Zastanów się dobrze...\')">Usuń</a>
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