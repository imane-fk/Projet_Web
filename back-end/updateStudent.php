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
            window.location.href = '../admin/comptes-etudiants.php';
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
            $stmt = $pdo->prepare("UPDATE utilisateur SET Email = :email, Nom = :nom, Prenom = :prenom, Telephone = :telephone, Statut = :Statut, Adresse = :adresse WHERE ID_User = :id_Utilisateur");
            $stmt->execute([
                ':email' => $email,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone,
                ':Statut' => $Statut,
                ':adresse' => $adresse,
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
                window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
            });
        </script>";
        }

        $id_Etudiant = $_POST['ID_Etudiant'];
        $Nom_Arabe = $_POST['Nom_Arabe'];
        $Prenom_Arabe = $_POST['Prenom_Arabe'];
        $Sexe = $_POST['Sexe'];
        $Nationalite = $_POST['Nationalite'];
        $prenom = $_POST['Prenom'];
        $Telephone_Parents = $_POST['Telephone_Parents'];
        $Adresse_Parents = $_POST['Adresse_Parents'];
        $Email_Universitaire = $_POST['Email_Universitaire'];
        $Boursier = $_POST['Boursier'];
        $date_naissance = $_POST['Date_Naissance'];
        $cne = $_POST['CNE'];


        try {
            $stmt = $pdo->prepare("UPDATE etudiant SET Nom_Arabe = :Nom_Arabe, Prenom_Arabe = :Prenom_Arabe, Sexe = :Sexe, Nationalite = :Nationalite, Telephone_Parents = :Telephone_Parents, Adresse_Parents = :Adresse_Parents, Email_Universitaire = :Email_Universitaire, Boursier = :Boursier, Date_Naissance = :Date_Naissance, CNE = :CNE WHERE ID_Etudiant = :id_Etudiant");
            $stmt->execute([
                ':Nom_Arabe' => $Nom_Arabe,
                ':Prenom_Arabe' => $Prenom_Arabe,
                ':Sexe' => $Sexe,
                ':Nationalite' => $Nationalite,
                ':Telephone_Parents' => $Telephone_Parents,
                ':Adresse_Parents' => $Adresse_Parents,
                ':Email_Universitaire' => $Email_Universitaire,
                ':Boursier' => $Boursier,
                ':Date_Naissance' => $date_naissance,
                ':CNE' => $cne,
                ':id_Etudiant' => $id_Etudiant
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
                    window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                });
            </script>";
        }
        if (isset($_FILES['Acte_Cautionnement']) && $_FILES['Acte_Cautionnement']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Acte_Cautionnement']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Acte_Cautionnement']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Acte_Cautionnement = :Acte_Cautionnement WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Acte_Cautionnement', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Acte_Cautionnement',
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
                        title: 'Erreur Acte_Cautionnement',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }
        // Reglement_Vie_Collective
        if (isset($_FILES['Reglement_Vie_Collective']) && $_FILES['Reglement_Vie_Collective']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Reglement_Vie_Collective']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Reglement_Vie_Collective']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Reglement_Vie_Collective = :Reglement_Vie_Collective WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Reglement_Vie_Collective', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Reglement_Vie_Collective',
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
                        title: 'Erreur Reglement_Vie_Collective',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }
        if (isset($_FILES['Reglement_Etudes']) && $_FILES['Reglement_Etudes']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Reglement_Etudes']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Reglement_Etudes']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Reglement_Etudes = :Reglement_Etudes WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Reglement_Etudes', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Reglement_Etudes',
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
                        title: 'Erreur Reglement_Etudes',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }
        if (isset($_FILES['Cliche_Pulmonaire']) && $_FILES['Cliche_Pulmonaire']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Cliche_Pulmonaire']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Cliche_Pulmonaire']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Cliche_Pulmonaire = :Cliche_Pulmonaire WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Cliche_Pulmonaire', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Cliche_Pulmonaire',
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
                        title: 'Erreur Cliche_Pulmonaire',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }
        if (isset($_FILES['Certificat_Medical']) && $_FILES['Certificat_Medical']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Certificat_Medical']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Certificat_Medical']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Certificat_Medical = :Certificat_Medical WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Certificat_Medical', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Certificat_Medical',
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
                        title: 'Erreur Certificat_Medical',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
        }
        if (isset($_FILES['Copie_Baccalaureat']) && $_FILES['Copie_Baccalaureat']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['Copie_Baccalaureat']['tmp_name']);
            if ($fileType === 'application/pdf') {
                $filecontent = file_get_contents($_FILES['Copie_Baccalaureat']['tmp_name']);
                try {
                    $stmt = $pdo->prepare("UPDATE etudiant SET Copie_Baccalaureat = :Copie_Baccalaureat WHERE ID_Etudiant = :id_Etudiant");
                    // Bind the parameters
                    $stmt->bindParam(':Copie_Baccalaureat', $filecontent, PDO::PARAM_LOB);
                    $stmt->bindParam(':id_Etudiant', $id_Etudiant, PDO::PARAM_INT);
                    // Execute the statement
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error alert with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Copie_Baccalaureat',
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
                        title: 'Erreur Copie_Baccalaureat',
                        text: 'Le fichier doit être un PDF.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
                    });
                </script>";
            }
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
        if (isset($_FILES['JustificatifAssurance']) && $_FILES['JustificatifAssurance']['error'] === UPLOAD_ERR_OK && isset($_POST["ID_Assurance"])) {
            $fileType = mime_content_type($_FILES['JustificatifAssurance']['tmp_name']);
            $fileExtension = pathinfo($_FILES['JustificatifAssurance']['name'], PATHINFO_EXTENSION);
            if ($fileType === 'application/pdf' && strtolower($fileExtension) === 'pdf') {

                $fileContent = file_get_contents($_FILES['JustificatifAssurance']['tmp_name']);
                try {
                    $ID_Assurance = $_POST["ID_Assurance"];
                    $stmt = $pdo->prepare("UPDATE assurance SET Justificatif = :JustificatifAssurance WHERE ID_Assurance = :ID_Assurance");
                    $stmt->bindParam(':JustificatifAssurance', $fileContent, PDO::PARAM_LOB);
                    $stmt->bindParam(':ID_Assurance', $ID_Assurance, PDO::PARAM_INT);
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Error handling with proper escaping
                    echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Erreur Justificatif Assurance',
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
                        title: 'Erreur Justificatif Assurance',
                        text: 'Le fichier téléchargé n\'est pas un PDF.',
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
                window.location.href = '../admin/page-etudiants.php?userId=" . $id_Utilisateur . "';
            });
        </script>";
    }
}
