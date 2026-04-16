<?php
require_once 'database.php';
require_once 'functions.php';
$errors = [];
$imie = "";
$nazwisko = "";
$etat = "";
$szef = "";
$zesp = "";
$data_zatr = "";
$placa_pod = "";
$placa_dod = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $etat = $_POST['etat'] ?? '';
    $szef = $_POST['szef'] ?? '';
    $zesp = $_POST['zesp'] ?? '';
    $data_zatr = $_POST['data_zatr'] ?? '';
    $placa_pod = $_POST['placa_pod'] ?? '';
    $placa_dod = $_POST['placa_dod'] ?? '';
    if($imie=='') $errors['imie']="To pole jest wymagane";
    if($nazwisko=='') $errors['nazwisko']="To pole jest wymagane";
    if($etat=='') $errors['etat']="Wybierz etat";
    if($szef=='') $errors['szef']="Wybierz szefa";
    if($zesp=='') $errors['zesp']="Wybierz zespół";
    if($data_zatr=='') $errors['data_zatr']="Wybierz datę";
    if($placa_pod=='' || !is_numeric($placa_pod)) $errors['placa_pod']="Podaj poprawną liczbę";
    if($placa_dod!='' && !is_numeric($placa_dod)) $errors['placa_dod']="Musi być liczbą";
    
    if(empty($errors) && $etat != ''){
        $e = $pdo->prepare("SELECT PLACA_OD, PLACA_DO FROM etaty WHERE NAZWA = :nazwa");
        $e->execute([':nazwa' => $etat]);
        $etat_row = $e->fetch(PDO::FETCH_ASSOC);
        if($etat_row){
            if($placa_pod < $etat_row['PLACA_OD'] || $placa_pod > $etat_row['PLACA_DO']){
                $errors['placa_pod']="Płaca musi być między ".$etat_row['PLACA_OD']." a ".$etat_row['PLACA_DO'];
            }
        }
    }
    
    if(empty($errors)){
        $stmt = $pdo->prepare("INSERT INTO pracownicy (IMIE, NAZWISKO, ETAT, ID_SZEFA, ZATRUDNIONY, PLACA_POD, PLACA_DOD, ID_ZESP) VALUES (:imie, :nazwisko, :etat, :szef, :data_zatr, :placa_pod, :placa_dod, :zesp)");
        $stmt->bindValue(':imie', $imie, PDO::PARAM_STR);
        $stmt->bindValue(':nazwisko', $nazwisko, PDO::PARAM_STR);
        $stmt->bindValue(':etat', $etat, PDO::PARAM_STR);
        $stmt->bindValue(':szef', $szef ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':data_zatr', $data_zatr, PDO::PARAM_STR);
        $stmt->bindValue(':placa_pod', $placa_pod, PDO::PARAM_STR);
        $stmt->bindValue(':placa_dod', $placa_dod==""?null:$placa_dod, PDO::PARAM_STR);
        $stmt->bindValue(':zesp', $zesp ?: null, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dodaj pracownika</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>.error-input{border:2px solid red!important;}</style>
</head>
<body>
    <?php
        printNavbar();
    ?>
    <main class="container mt-5">
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Imię</label>
                <input type="text" name="imie" class="form-control <?php if(isset($errors['imie'])) echo 'error-input'; ?>" value="<?php echo $imie; ?>">
                <div class="text-danger"><?php echo $errors['imie'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nazwisko</label>
                <input type="text" name="nazwisko" class="form-control <?php if(isset($errors['nazwisko'])) echo 'error-input'; ?>" value="<?php echo $nazwisko; ?>">
                <div class="text-danger"><?php echo $errors['nazwisko'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Etat</label>
                <select name="etat" class="form-control <?php if(isset($errors['etat'])) echo 'error-input'; ?>">
                    <option value="">-- wybierz --</option>
                    <?php
                        $stmt = $pdo->query("SELECT NAZWA FROM etaty");
                        while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo "<option value='".$r['NAZWA']."'";
                            if($etat==$r['NAZWA']) echo " selected";
                            echo ">".$r['NAZWA']."</option>";
                        }
                    ?>
                </select>
                <div class="text-danger"><?php echo $errors['etat'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Szef</label>
                <select name="szef" class="form-control <?php if(isset($errors['szef'])) echo 'error-input'; ?>">
                    <option value="">-- wybierz --</option>
                    <?php
                        $stmt = $pdo->query("SELECT ID_PRAC, IMIE, NAZWISKO FROM pracownicy");
                        while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo "<option value='".$r['ID_PRAC']."'";
                            if($szef==$r['ID_PRAC']) echo " selected";
                            echo ">".$r['IMIE']." ".$r['NAZWISKO']."</option>";
                        }
                    ?>
                </select>
                <div class="text-danger"><?php echo $errors['szef'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Zespół</label>
                <select name="zesp" class="form-control <?php if(isset($errors['zesp'])) echo 'error-input'; ?>">
                    <option value="">-- wybierz --</option>
                    <?php
                        $stmt = $pdo->query("SELECT ID_ZESP, NAZWA FROM zespoly");
                        while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo "<option value='".$r['ID_ZESP']."'";
                            if($zesp==$r['ID_ZESP']) echo " selected";
                            echo ">".$r['NAZWA']."</option>";
                        }
                    ?>
                </select>
                <div class="text-danger"><?php echo $errors['zesp'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Data zatrudnienia</label>
                <input type="date" name="data_zatr" class="form-control <?php if(isset($errors['data_zatr'])) echo 'error-input'; ?>" value="<?php echo $data_zatr; ?>">
                <div class="text-danger"><?php echo $errors['data_zatr'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Płaca podstawowa</label>
                <input type="text" name="placa_pod" class="form-control <?php if(isset($errors['placa_pod'])) echo 'error-input'; ?>" value="<?php echo $placa_pod; ?>">
                <div class="text-danger"><?php echo $errors['placa_pod'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Płaca dodatkowa (opcjonalnie)</label>
                <input type="text" name="placa_dod" class="form-control <?php if(isset($errors['placa_dod'])) echo 'error-input'; ?>" value="<?php echo $placa_dod; ?>">
                <div class="text-danger"><?php echo $errors['placa_dod'] ?? ''; ?></div>
            </div>
            <button type="submit" class="btn btn-success">Dodaj</button>
            <a href="index.php" class="btn btn-secondary">Wróć</a>
        </form>
    </main>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>