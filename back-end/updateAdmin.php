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
require_once './connect.php';
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: ../login.php');
    exit();
}
$errorMessage = '';
$successTitle = '';
$successMessage = '';
$errorTitle = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($pdo)) {
        echo "<script> 
        Swal.fire({
            position: 'center',
            icon: 'error',
            title: 'Échec de la connexion à la base de données',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            // Redirect the user to the comptes-etudiants.php page after the alert is closed
            window.location.href = '../admin/comptes-admin.php';
        });";
    } else {
        $id_Utilisateur = $_POST['ID_User'];
        $email = $_POST['EmailP'];
        $nom = $_POST['Nom'];
        $prenom = $_POST['Prenom'];
        $telephone = $_POST['Telephone'];
        $Statut = $_POST['Statut'];
        $adresse = $_POST['Adresse'];
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $telephone = filter_var($telephone, FILTER_SANITIZE_NUMBER_INT);
        try {
            $stmt = $pdo->prepare("UPDATE utilisateur SET Email = :Email, Nom = :Nom, Prenom = :Prenom, Telephone = :Telephone, Statut = :Statut, Adresse = :Adresse WHERE ID_User = :id_Utilisateur");
            $stmt->execute([
                ':Email' => $email,
                ':Nom' => $nom,
                ':Prenom' => $prenom,
                ':Telephone' => $telephone,
                ':Statut' => $Statut,
                ':Adresse' => $adresse,
                ':id_Utilisateur' => $id_Utilisateur
            ]);
        } catch (PDOException $e) {
            echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur : " . addslashes($e->getMessage()) . "',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect the user to the comptes-etudiants.php page after the alert is closed
                window.location.href = '../admin/page-admins.php?userId=" . $id_Utilisateur . "';
            });
        </script>";
        }
        $id_Admin = $_POST['ID_Admin'];
        $Email_Universitaire = isset($_POST['Email_Universitaire']) ? $_POST['Email_Universitaire'] : null;
        $Date_prise_fonction = $_POST['Date_Prise_Fonction'] ? $_POST['Date_Prise_Fonction'] : null;
        $Date_fin_fonction = $_POST['Date_Fin_Fonction'] ? $_POST['Date_Fin_Fonction'] : null;
        $Heures_Travail = $_POST['Heures_Travail'] ? $_POST['Heures_Travail'] : null;

        try {
            $stmt = $pdo->prepare("UPDATE administration SET  Email_Universitaire = :Email_Universitaire, Date_prise_fonction = :Date_prise_fonction, Date_fin_fonction = :Date_fin_fonction, Heures_Travail = :Heures_Travail WHERE ID_Admin = :id_Admin");
            $stmt->execute([

                ':Email_Universitaire' => $Email_Universitaire,
                ':Date_prise_fonction' => $Date_prise_fonction,
                ':Date_fin_fonction' => $Date_fin_fonction,
                ':Heures_Travail' => $Heures_Travail,
                ':id_Admin' => $id_Admin
            ]);
        } catch (PDOException $e) {
            echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur : " . addslashes($e->getMessage()) . "',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect the user to the comptes-etudiants.php page after the alert is closed
                window.location.href = '../admin/page-admins.php?userId=" . $id_Utilisateur . "';
            });
        </script>";
        }


        if (isset($_FILES['Photo_Profil']) && $_FILES['Photo_Profil']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Photo_Profil']['tmp_name']);
            if (strpos($fileType, 'image') === 0) {
                $filecontent = file_get_contents($_FILES['Photo_Profil']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE utilisateur SET Photo_Profil = :Photo_Profil WHERE ID_User = :id_Utilisateur");
                    // Bind the parameters
                    $stmt->bindParam(':Photo_Profil', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Utilisateur', $id_Utilisateur, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Photo_Profil',
                            text: 'Erreur : " . addslashes($e->getMessage()) . "',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                        });
                    </script>";
                }
            } else {
                // Invalid file type alert
                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Erreur Photo_Profil',
                        text: 'Le fichier téléchargé n'est pas une image.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }




        echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Succès de la mise à jour',
                text: 'Le compte a été mis à jour avec succès.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect the user to the comptes-etudiants.php page after the alert is closed
                window.location.href = '../admin/page-admins.php?userId=" . $id_Utilisateur . "';
            });
        </script>";
    }
}
