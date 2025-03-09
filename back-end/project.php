<?php
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'etudiant' ||
    !isset($_SESSION['id_etudiant'])
) {
    header('Location: ../back-end/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
</body>

</html>
<?php
$host = 'localhost';
$dbname = 'ensem_web_project';
$username = 'root';
$password = '';

try {
    // Création d'une instance PDO

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_etudiant = $_SESSION['id_etudiant'];
    $id_paiement = $_POST['id_paiement'];
    $id_assurance = $_POST['id_assurance'];
    $id_annee = 2;
    $id_utilisateur = $_SESSION['user_id'];
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $sexe = $_POST['sexe'] ?? null;
    $cne = $_POST['CNE'] ?? null;
    $adresse_parents = $_POST['adresse_parents'] ?? null;
    $adresse = $_POST['adresse'] ?? null;
    $email_universitaire = $_POST['email_universitaire'] ?? null;
    $telephone_personnel = $_POST['telephone'] ?? null;
    $telephone_parents = $_POST['telephone_parents'] ?? null;
    $justificatif_assurance = null;
    if (isset($_FILES['assurance']) && $_FILES['assurance']['error'] === UPLOAD_ERR_OK) {

        $justificatif_assurance = file_get_contents($_FILES['assurance']['tmp_name']);
    }
    $caution = $_POST['caution'] ?? null;
    $date_paiement = $_POST['date'] ?? null;
    $justificatif = null;
    if (isset($_FILES['frais']) && $_FILES['frais']['error'] === UPLOAD_ERR_OK) {
        $justificatif = file_get_contents($_FILES['frais']['tmp_name']);
    }
    $internat = $_POST['prix_Internat'] ?? null;
    $restauration = $_POST['Prix_Restau'] ?? null;
    try {
        $stmt = $pdo->prepare("UPDATE etudiant SET CNE = :cne, Email_Universitaire = :email_universitaire, Telephone_Parents = :telephone_parents, Adresse_Parents = :adresse_parents WHERE ID_Etudiant = :id_etudiant");
        $stmt->execute([
            ':id_etudiant' => $id_etudiant,
            ':cne' => $cne,
            ':email_universitaire' => $email_universitaire,
            ':adresse_parents' => $adresse_parents,
            ':telephone_parents' => $telephone_parents,
        ]);
        $stmt = $pdo->prepare("UPDATE utilisateur SET Nom = :nom, Prenom = :prenom, Telephone = :telephone_personnel, Adresse = :adresse WHERE ID_User = :id_utilisateur");
        $stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':telephone_personnel' => $telephone_personnel,
            ':adresse' => $adresse,
        ]);
        $stmt = $pdo->prepare("SELECT * FROM paiement WHERE ID_Etudiant = :id_etudiant AND ID_Annee = :id_annee");
        $stmt->execute([
            ':id_etudiant' => $id_etudiant,
            ':id_annee' => $id_annee,
        ]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($payment)) {
            $stmt = $pdo->prepare("UPDATE paiement SET Caution_Annuel = :caution, prix_internat = :internat, Prix_Restau = :restauration WHERE ID_Paiement = :id_paiement");
            $stmt->execute([
                ':id_paiement' => $id_paiement,
                ':caution' => $caution,
                ':internat' => $internat,
                ':restauration' => $restauration,

            ]);
            if (isset($_FILES['frais']) && $_FILES['frais']['error'] === UPLOAD_ERR_OK) {
                $stmt = $pdo->prepare("UPDATE paiement SET Justificatif = :justificatif WHERE ID_Paiement = :id_paiement");
                $stmt->execute([
                    ':id_paiement' => $id_paiement,
                    ':justificatif' => $justificatif,
                ]);
            }
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO paiement (ID_Etudiant, ID_Annee, Caution_Annuel, Date_Paiement, Justificatif, Prix_Restau, Prix_Internat) VALUES ( :id_etudiant, :id_annee, :caution, :date_paiement, :justificatif, :internat, :restauration)");
                $stmt->execute([
                    ':id_etudiant' => $id_etudiant,
                    ':id_annee' => $id_annee,
                    ':caution' => $caution,
                    ':date_paiement' => $date_paiement,
                    'justificatif' => $justificatif,
                    ':internat' => $internat,
                    ':restauration' => $restauration,
                ]);
            } catch (PDOException $e) {
                echo $e->getMessage();
                echo "<script>
                Swal.fire({
                    position: 'center', 
                    icon: 'error', 
                    title: 'Echec de la mise à jour !', 
                    showConfirmButton: false, // Disable the confirm button
                    timer: 3000, // Alert will close automatically after 3 seconds
                    text: 'Opération Echouée.' // Error message text
                })
                </script>";
            }
        }
        $stmt = $pdo->prepare("SELECT * FROM assurance WHERE ID_Etudiant = :id_etudiant AND ID_Annee = :id_annee");
        $stmt->execute([
            ':id_etudiant' => $id_etudiant,
            ':id_annee' => $id_annee,
        ]);
        $verify_assurance = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($verify_assurance)) {
            if (isset($_FILES['assurance']) && $_FILES['assurance']['error'] === UPLOAD_ERR_OK) {
                $stmt = $pdo->prepare("UPDATE Assurance SET Justificatif = :justificatif WHERE ID_Assurance = :id_assurance");
                $stmt->execute([
                    ':id_assurance' => $id_assurance,
                    'justificatif' => $justificatif_assurance,
                ]);
            }
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO assurance ( ID_Etudiant, Justificatif, ID_Annee) VALUES ( :id_etudiant, :justificatif, :id_annee)");
                $stmt->execute([

                    ':id_etudiant' => $id_etudiant,
                    'justificatif' => $justificatif_assurance,
                    ':id_annee' => $id_annee,
                ]);
            } catch (PDOException $e) {
                echo $e->getMessage();
                echo "<script>
                Swal.fire({
                    position: 'center', // Position the alert in the center
                    icon: 'error', // Show an error icon
                    title: 'Echec de la mise à jour !', // The title of the alert
                    showConfirmButton: false, // Disable the confirm button
                    timer: 3000, // Alert will close automatically after 3 seconds
                    text: 'Opération Echouée.' // Error message text
                });
                </script>";
            }
        }
        echo "<script>
        Swal.fire({
            position: 'center', // Position the alert in the center
            icon: 'success', // Show an error icon
            title: 'Mise à jour effectuée avec succès !', // The title of the alert
            showConfirmButton: false, // Disable the confirm button
            timer: 3000, // Alert will close automatically after 3 seconds
            text: 'Opération terminée.' // Error message text
        }).then(() => {
                window.location.href = '../etudiant/Réinscription.php';
            });
        </script>";



        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<script>
            Swal.fire({
                position: 'center', // Position the alert in the center
                icon: 'error', // Show an error icon
                title: 'Echec de la mise à jour !', // The title of the alert
                text: 'Opération échouée : Une donnée similaire existe déjà.', // Specific error message for duplicate key
                showConfirmButton: false, // Disable the confirm button
                timer: 3000 // Alert will close automatically after 3 seconds
            }).then(() => {
                window.location.href = '../etudiant/Réinscription.php';
            });
            </script>";
        } else {
            // Handle other errors
            echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Echec de la mise à jour !',
                text: 'Une erreur est survenue. Veuillez réessayer plus tard.',
                showConfirmButton: false,
                timer: 3000
            }).then(() => {
                window.location.href = '../etudiant/Réinscription.php';
            });
            </script>";
        }
    }
}
?>