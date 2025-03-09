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
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'etudiant' ||
    !isset($_SESSION['id_etudiant'])
) {
    header("Location:../login.php");
    exit();
}
require_once "connect.php";
if (isset($pdo)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_utilisateur = $_SESSION['user_id'];
        $nom = $_POST["nom"];
        $nom_ar = $_POST["nom_ar"];
        $prenom = $_POST["prenom"];
        $prenom_ar = $_POST["prenom_ar"];
        $CNE = $_POST["CNE"];
        $date_naissance = $_POST["date_naissance"];
        $sexe = $_POST["sexe"];
        $telephone_p = $_POST["telephone_p"];
        $telephone_pare = $_POST["telephone_pare"];
        $telephone_p = filter_var($telephone_p, FILTER_SANITIZE_NUMBER_INT);
        $telephone_pare = filter_var($telephone_pare, FILTER_SANITIZE_NUMBER_INT);
        $nationalite = $_POST["nationalite"];
        $lieu_naissance = $_POST["lieu_naissance"];
        $adresse_personnelle = $_POST["adresse_personnelle"];
        $adresse_parents = $_POST["adresse_parents"];
        $email = $_POST["email"];
        $id_anee = 1;
        $id_etudiant = $_SESSION['id_etudiant'];
        $id_paiement = isset($_POST["id_paiement"]) ? $_POST["id_paiement"] : NULL;
        $id_assurance = isset($_POST["id_assurance"]) ? $_POST["id_assurance"] : NULL;
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $boursier = isset($_POST['boursier']) ?  1 : 0;;
        $besoins_specifiques = isset($_POST['besoins_specifiques']) ? 1 : 0;
        $frais_internat = $_POST["frais_internat"];
        $frais_restauration = $_POST["frais_restauration"];
        $caution_annuelle = $_POST["caution_annuelle"];
        $Photo_Profil = NULL;
        if (isset($_FILES['Photo_Profil']) && $_FILES['Photo_Profil']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['Photo_Profil']['type'], $allowedTypes)) {

                $Photo_Profil = file_get_contents($_FILES['Photo_Profil']['tmp_name']);
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Le type de fichier pour photo profile n\'est pas autorisé !',
                    showConfirmButton: false, // Disable the confirm button
                    timer: 3000, // Alert will close automatically after 3 seconds
                }).then(() => {
                    // Redirect the user to the previous page after the alert is closed
                    window.location.href = '../etudiant/inscription/main.php';
                });
            </script>";
            }
        }
        $justificatif = NULL;
        if (isset($_FILES['Reçu']) && $_FILES['Reçu']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Reçu']['tmp_name']);
        }

        $montant = 50;

        try {
            $stmt = $pdo->prepare("
                UPDATE etudiant
                SET Email_Universitaire = :email, 
                    Nom_Arabe = :nom_ar, 
                    Prenom_Arabe = :prenom_ar,
                    Telephone_Parents = :telephone_pare, 
                    CNE = :CNE, 
                    Date_Naissance = :date_naissance, 
                    Sexe = :sexe, 
                    Adresse_Parents = :adresse_parents,
                    Nationalite = :nationalite, 
                    Lieu_Naissance = :lieu_naissance, 
                    Boursier = :boursier, 
                    Handicap = :handicap
                WHERE ID_Utilisateur = :id_utilisateur
            ");

            // Exécution avec un tableau associatif
            $stmt->execute([
                ':email' => $email,
                ':nom_ar' => $nom_ar,
                ':prenom_ar' => $prenom_ar,
                ':telephone_pare' => $telephone_pare,
                ':CNE' => $CNE,
                ':date_naissance' => $date_naissance,
                ':sexe' => $sexe,
                ':adresse_parents' => $adresse_parents,
                ':nationalite' => $nationalite,
                ':lieu_naissance' => $lieu_naissance,
                ':boursier' => $boursier,
                ':handicap' => $besoins_specifiques,
                ':id_utilisateur' => $id_utilisateur,
            ]);

            $stmt = $pdo->prepare("SELECT id_Paiement FROM paiement WHERE id_Etudiant = :id_etudiant AND ID_Annee= :id_anee");
            $stmt->execute([
                ':id_etudiant' => $id_etudiant,
                ':id_anee' => $id_anee,
            ]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($paiement)) {

                try {
                    $stmt = $pdo->prepare("
                INSERT INTO paiement (ID_Paiement, ID_Etudiant, ID_Annee, Prix_Internat, Prix_Restau, Caution_Annuel, Date_Paiement, Justificatif) 
                VALUES (:id_paiement, :id_etudiant, :id_anee, :prix_internat, :prix_resteau, :Caution_Annuel, :Date_paiement, :Justificatif)
            ");
                    $stmt->execute([
                        ':id_paiement' => $id_paiement,
                        ':id_etudiant' => $id_etudiant,
                        ':id_anee' => $id_anee,
                        ':prix_internat' => $frais_internat,
                        ':prix_resteau' => $frais_restauration,
                        ':Caution_Annuel' => $caution_annuelle,
                        ':Date_paiement' => date('Y-m-d'),
                        ':Justificatif' => $justificatif,
                    ]);
                } catch (PDOException $e) {
                    $eroorTitle = "Erreur lors de l'insertion";
                    $eroorText = $e->getMessage();
                }
            } else {
                $stmt = $pdo->prepare("
                    UPDATE paiement
                    SET ID_Annee = :id_anee,
                        Prix_Internat = :prix_internat,
                        Prix_Restau = :prix_resteau,
                        Caution_Annuel = :Caution_Annuel,
                        Date_Paiement = :Date_paiement
                    WHERE ID_Paiement = :id_paiement
                ");
                $stmt->execute([
                    ':id_paiement' => $id_paiement,
                    ':id_anee' => $id_anee,
                    ':prix_internat' => $frais_internat,
                    ':prix_resteau' => $frais_restauration,
                    ':Caution_Annuel' => $caution_annuelle,
                    ':Date_paiement' => date('Y-m-d'),

                ]);
                if (isset($_FILES['Reçu']) && $_FILES['Reçu']['error'] === UPLOAD_ERR_OK) {


                    $stmt = $pdo->prepare("
                            UPDATE paiement
                            SET Justificatif = :Justificatif
                            WHERE ID_Paiement = :id_paiement
                        ");
                    $stmt->execute([
                        ':id_paiement' => $paiement['id_Paiement'],
                        ':Justificatif' => $justificatif,
                    ]);
                }
            }

            //assurance
            $stmt = $pdo->prepare("
               SELECT montant, id_assurance, id_annee 
               FROM assurance 
               WHERE id_etudiant = :id_etudiant AND id_annee = :id_annee
            ");
            $stmt->execute([
                ':id_etudiant' => $id_etudiant,
                ':id_annee' => $id_anee,
            ]);
            $assurance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($assurance)) {
                try {
                    $stmt = $pdo->prepare("
                INSERT INTO assurance(ID_Assurance,ID_Etudiant,Montant,Justificatif,ID_Annee)
                VALUES(:id_assurance,:id_etudaiant,:Montant,:justificatif,:id_annee)
                ");
                    $stmt->execute([
                        ':id_assurance' => $assurance['id_assurance'],
                        ':id_etudaiant' => $id_etudiant,
                        ':Montant' => $montant,
                        ':justificatif' => $justificatif,
                        'id_annee' => $id_anee,
                    ]);
                } catch (PDOException $e) {
                    $errorMessage = $e->getMessage();
                    $errorTitle = "Erreur lors de l'insertion";
                }
            } else {
                $stmt = $pdo->prepare("
            UPDATE assurance
            SET Montant = :montant,
                Justificatif = :justificatif
            WHERE ID_Assurance = :id_assurance AND ID_Annee = :id_annee
        ");
                $stmt->execute([
                    ':montant' => $montant,
                    ':justificatif' => $justificatif,
                    ':id_assurance' => $assurance['id_assurance'], // Récupéré depuis SELECT
                    ':id_annee' => $id_anee,
                ]);
            }
            $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET 
                Nom = :nom,
                Prenom = :prenom,
                Telephone = :telephone_p,
                Adresse = :adresse_personnelle
            WHERE ID_User = :id_utilisateur");
            $stmt->execute([

                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone_p' => $telephone_p,
                ':adresse_personnelle' => $adresse_personnelle,
                ':id_utilisateur' => $id_utilisateur,
            ]);
            if (isset($Photo_Profil)) {
                $stmt = $pdo->prepare("
        UPDATE utilisateur
        SET Photo_Profil = :Photo_Profil
        WHERE ID_User = :id
    ");
                $stmt->execute([
                    ':Photo_Profil' => $Photo_Profil,
                    ':id' => $id_utilisateur,
                ]);
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
                 window.location.href = '../etudiant/inscription/main.php';            });
        </script>";
        } catch (PDOException $e) {
            echo $e->getMessage();
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Erreur mise a a jour',
                showConfirmButton: false, // Disable the confirm button
                timer: 3000, // Alert will close automatically after 3 seconds
            }).then(() => {
                // Redirect the user to the comptes-etudiants.php page after the alert is closed
                window.location.href = '../etudiant/inscription/main.php';
            });
            </script>";
        }
        if (isset($_FILES['Acte_Cautionnement']) && $_FILES['Acte_Cautionnement']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Acte_Cautionnement']['tmp_name']);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Acte_Cautionnement = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
        if (isset($_FILES['Reglement_Vie_Collective']) && $_FILES['Reglement_Vie_Collective']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Reglement_Vie_Collective']['tmp_name']);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Reglement_Vie_Collective = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
        if (isset($_FILES['Reglement_Etudes']) && $_FILES['Reglement_Etudes']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Reglement_Etudes']['tmp_name']);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Reglement_Etudes = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
        if (isset($_FILES['Certificat_Medical']) && $_FILES['Certificat_Medical']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Certificat_Medical']['tmp_name']);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Certificat_Medical = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
        if (isset($_FILES['Cliche_Pulmonaire']) && $_FILES['Cliche_Pulmonaire']['error'] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES['Cliche_Pulmonaire']['tmp_name']);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Cliche_Pulmonaire = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
        if (isset($_FILES["Copie_Baccalaureat"]) && $_FILES["Copie_Baccalaureat"]["error"] === UPLOAD_ERR_OK) {
            $justificatif = file_get_contents($_FILES["Copie_Baccalaureat"]["tmp_name"]);
            $stmt = $pdo->prepare(" UPDATE etudiant  SET Copie_Baccalaureat = :justificatif WHERE ID_Utilisateur = :id_utilisateur");
            $stmt->execute([
                ':justificatif' => $justificatif,
                ':id_utilisateur' => $id_utilisateur,
            ]);
        }
    }
} else {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Erreur lors de la connexion à la base de données !',
            showConfirmButton: false, // Disable the confirm button
            timer: 3000, // Alert will close automatically after 3 seconds
        }).then(() => {
            // Redirect the user to the comptes-etudiants.php page after the alert is closed
            window.location.href = '../etudiant/inscription/main.php';
        });
        </script>";
}
