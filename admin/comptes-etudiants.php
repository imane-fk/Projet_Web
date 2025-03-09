<?php
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
require_once '../back-end/connect.php';
require_once '../back-end/functions.php';
require_once '../back-end/mail_config.php';

$errorMessage = '';
$successMessage = '';
$successTitle = '';
$errorTitle = '';
$users = [];
function FetchEtudiant($pdo, $searchKey, &$errorMessage, &$errorTitle)
{
    try {
        $query = "
        SELECT u.ID_User, u.Nom, u.Prenom, u.Derniere_Connexion, u.Date_Creation, 
               u.Telephone, u.Photo_Profil, u.Statut, u.Adresse, u.Email, 
               e.ID_Etudiant, e.Nom_Arabe, e.Prenom_Arabe, e.CNE, e.Date_Naissance, 
               e.Sexe, e.Telephone_Parents, e.Adresse_Parents, e.Nationalite, 
               e.Email_Universitaire, e.Boursier, e.Acte_Cautionnement, 
               e.Reglement_Vie_Collective, e.Reglement_Etudes, e.Cliche_Pulmonaire, 
               e.Certificat_Medical, e.Copie_Baccalaureat
        FROM Utilisateur u
        LEFT JOIN etudiant e ON u.ID_User = e.ID_Utilisateur
        WHERE LOWER(u.Profil) = 'etu'
    ";
        if (!empty($searchKey)) {
            $query .= " AND (LOWER(u.Nom) LIKE :searchKey 
                     OR LOWER(u.Prenom) LIKE :searchKey 
                     OR LOWER(u.Email) LIKE :searchKey 
                     OR LOWER(e.Email_Universitaire) LIKE :searchKey)";
        }
        $stmt = $pdo->prepare($query);
        if (!empty($searchKey)) {
            $stmt->bindValue(':searchKey', '%' . $searchKey . '%');
        }


        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results)) {
            return [];
        }
        foreach ($results as $row) {
            $users[] = $row;
        }
        return $users;
    } catch (PDOException $e) {
        $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
        $errorTitle = "Erreur de requête";
        exit();
    }
}

if (!isset($pdo)) {
    $errorMessage = "Échec de la connexion à la base de données.";
    $errorTitle = "Erreur de connexion";
} else {
    $users = FetchEtudiant($pdo, '', $errorMessage, $errorTitle);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['search'])) {
            $searchKey = filter_var($_POST['search'], FILTER_SANITIZE_STRING);
            $users = FetchEtudiant($pdo, $searchKey, $errorMessage, $errorTitle);
        } else if (isset($_POST['Edit'])) {
            $userId = $_POST['Edit'];
            header("Location: page-etudiants.php?userId=$userId");
        } else if (isset($_POST['Delete'])) {
            try {
                $userId = filter_var($_POST['Delete'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE ID_User = :userId");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                // Execute the DELETE statement
                if ($stmt->execute()) {
                    $successMessage = "L'utilisateur a été supprimé avec succès.";
                    $users = FetchEtudiant($pdo, '', $errorMessage, $errorTitle);
                } else {
                    echo "Failed to delete user with ID: " . $userId . "<br>";
                    $errorMessage = "Impossible de supprimer l'utilisateur.";
                    $errorTitle = "Erreur de suppression";
                }
            } catch (PDOException $e) {

                $errorMessage = "Échec de la requête SQL : ";
                $errorTitle = "Erreur de suppression";
            }
        }
        if (isset($_POST["Nom"]) && isset($_POST["Prenom"]) && isset($_POST["email"])) {
            $Nom = $_POST["Nom"];
            $Prenom = $_POST["Prenom"];
            $email = $_POST["email"];
            $code = generateRandomString();
            $password = password_hash($code, PASSWORD_DEFAULT);
            $Date_Creation =  date('Y-m-d H:i:s');
            if (isset($_POST["Statut"]) && $_POST["Statut"] == "yes") {
                $Statut = "Actif";
            } else {
                $Statut = "Inactif";
            }
            try {
                $stmt = $pdo->prepare("INSERT INTO Utilisateur (Nom, Prenom, Email, Password, Date_Creation, Statut, Profil) VALUES (:Nom, :Prenom, :email, :password, :Date_Creation, :Statut, 'etu')");
                $stmt->bindParam(':Nom', $Nom);
                $stmt->bindParam(':Prenom', $Prenom);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':Date_Creation', $Date_Creation);
                $stmt->bindParam(':Statut', $Statut);
                if ($stmt->execute()) {
                    $stmt = $pdo->prepare("INSERT INTO etudiant (ID_Utilisateur) VALUES (:ID_Utilisateur)");
                    $lastInsertId = $pdo->lastInsertId();
                    $stmt->bindParam(':ID_Utilisateur', $lastInsertId);
                    if ($stmt->execute()) {
                        $mail = getMailer();
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = 'Création de compte';
                        $mail->Body = "
                            <html>
                            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                                <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                    <tr>
                                        <td align='center'>
                                            <table width='600' cellpadding='0' cellspacing='0' border='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden;'>
                                                <tr>
                                                    <td style='background-color: #4CAF50; padding: 20px; text-align: center; color: #ffffff;'>
                                                        <h2>Bienvenue chez Nous </h2>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 20px; color: #333333;'>
                                                        <p>Bonjour <strong>$Nom $Prenom</strong>,</p>
                                                        <p>Votre compte a été créé avec succès.</p>
                                                        <p>Votre mot de passe temporaire est : <span style='color: #e74c3c;'>$code</span></p>
                                                        <p>Veuillez le changer lors de votre première connexion.</p>
                                                        <p>Cordialement,<br/>L'équipe de support</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style='background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 12px; color: #777777;'>
                                                        © " . date('Y') . " NotreEntreprise. Tous droits réservés.
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </body>
                            </html>
                        ";
                        if ($mail->send()) {
                            $successMessage = "L'utilisateur a été ajouté avec succès ,vérifier votre boite mail.";
                            $successTitle = "Succès d'ajout";
                            $users = FetchEtudiant($pdo, '', $errorMessage, $errorTitle);
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE ID_User = :ID_User");
                            $stmt->bindParam(':ID_User', $ID_User);
                            $stmt->execute();
                            $errorMessage = "Impossible d'ajouter l'utilisateur.";
                            $errorTitle = "Erreur d'ajout";
                        }
                    } else {
                        $errorMessage = "Impossible d'ajouter l'utilisateur.";
                        $errorTitle = "Erreur d'ajout";
                    }
                } else {
                    $stm = $pdo->prepare("DELETE FROM Utilisateur WHERE ID_User = :ID_User");
                    $stm->bindParam(':ID_User', $lastInsertId);
                    $stm->execute();
                    $errorMessage = "Impossible d'ajouter l'utilisateur.";
                    $errorTitle = "Erreur d'ajout";
                }
            } catch (PDOException $e) {
                $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
                $errorTitle = "Erreur d'ajout";
            }
        }
        if (isset($_POST["Statut"]) && isset($_POST["ID_User"])) {

            $Statut = $_POST["Statut"];
            $ID_User = $_POST["ID_User"];
            try {
                $stmt = $pdo->prepare("UPDATE Utilisateur SET Statut = :Statut WHERE ID_User = :ID_User");
                $stmt->bindParam(':Statut', $Statut);
                $stmt->bindParam(':ID_User', $ID_User);
                if ($stmt->execute()) {
                    $successMessage = "L'utilisateur a été mis à jour avec succès.";
                    $successTitle = "Succès de mise à jour";
                    $users = FetchEtudiant($pdo, '', $errorMessage, $errorTitle);
                } else {
                    $errorMessage = "Impossible de mettre à jour l'utilisateur.";
                    $errorTitle = "Erreur de mise à jour";
                }
            } catch (PDOException $e) {
                $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
                $errorTitle = "Erreur de mise à jour";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>

<body>
    <?php if (!empty($errorMessage)) : ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                text: '<?php echo addslashes($errorMessage); ?>',
                title: '<?php echo addslashes($errorTitle); ?>',
                showConfirmButton: false,
                timer: 9000
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($successMessage)) : ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                text: '<?php echo addslashes($successMessage); ?>',
                title: '<?php echo addslashes($successTitle); ?>',
                showConfirmButton: false,
                timer: 9000
            });
        </script>
    <?php endif; ?>



    <aside id="sidebar-multi-level-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform transform translate-x-0 sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full flex flex-col px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
            <ul class="flex-1 space-y-2 font-medium">
                <li>
                    <a href="./Acceuil.php" class="flex items-center p-3 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group transition duration-200">
                        <svg class="w-6 h-6 text-gray-500 transition duration-200 group-hover:scale-110 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 0 1 1.414 0l9 9a1 1 0 0 1-1.414 1.414L12 4.414l-7.707 7.707A1 1 0 0 1 2.879 10.293l9-9Z" />
                            <path d="M5 10v9a1 1 0 0 0 1 1h4v-5h4v5h4a1 1 0 0 0 1-1v-9h2v9a3 3 0 0 1-3 3h-4a1 1 0 0 1-1-1v-5h-2v5a1 1 0 0 1-1 1H6a3 3 0 0 1-3-3v-9h2Z" />
                        </svg>
                        <span class="flex-1 ms-3 text-base font-medium">Accueil</span>
                    </a>
                </li>

                <li>
                    <a href="./comptes-etudiants.php" class="flex items-center p-3 text-base font-medium text-gray-900 transition duration-200 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="compte-etudiant-dropdown" data-collapse-toggle="compte-etudiant-dropdown">
                        <!-- Icon for "Compte Étudiant" -->
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-200 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
                            <path d="M12 14c-4.336 0-8 2.015-8 4.5V20h16v-1.5c0-2.485-3.664-4.5-8-4.5Z" />
                        </svg>
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Compte Étudiant</span>
                    </a>
                </li>
                <li>
                    <a href="./comptes-admin.php" class="flex items-center p-3 text-base font-medium text-gray-900 transition duration-200 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                        <!-- Icon for "Comptes Admins" -->
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-200 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M6 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm8-6a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM6 12c-3.333 0-6 1.333-6 4v2h12v-2c0-2.667-2.667-4-6-4Zm8 0c-.513 0-1 .063-1.467.18.92.833 1.467 1.997 1.467 3.32V18h6v-2c0-2.667-2.667-4-6-4Z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Comptes Admins</span>
                    </a>
                </li>

                <li>
                    <a href="../Aide.php" class="flex items-center p-3 text-base font-medium text-gray-900 transition duration-200 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                        <!-- Icône pour "Aide" -->
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-200 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18" aria-hidden="true">
                            <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z" />
                        </svg>
                        <!-- Texte "Aide" -->
                        <span class="flex-1 ms-3 whitespace-nowrap">Aide</span>


                    </a>
                </li>

                <li>
                    <a href="../back-end/logout.php" class="flex items-center p-3 text-base font-medium text-gray-900 transition duration-200 rounded-lg group hover:bg-red-100 dark:text-white dark:hover:bg-red-700">
                        <!-- Icône pour "Déconnexion" -->
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-200 group-hover:text-red-600 dark:text-gray-400 dark:group-hover:text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18" aria-hidden="true">
                            <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z" />
                        </svg>
                        <!-- Texte "Déconnexion" -->
                        <span class="flex-1 ms-3 whitespace-nowrap">Déconnexion</span>
                    </a>
                </li>

            </ul>
        </div>
    </aside>
    </div>
    </aside>

    <div class="p-4 sm:ml-64">
        <div class="content">
            <div class="text-center mt-5">
                <button type="button" class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800" type="button" data-drawer-target="drawer-form" data-drawer-show="drawer-form" aria-controls="drawer-form">
                    Ajouter Utilisateur
                </button>
            </div>
            <div id="drawer-form" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800" tabindex="-1" aria-labelledby="drawer-form-label">
                <h5 id="drawer-label" class="inline-flex items-center mb-6 text-base font-semibold text-gray-500 uppercase dark:text-gray-400">
                    <svg class="w-3.5 h-3.5 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 1.5l7 4.5v9a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1V13H9v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-9l7-4.5zM10 3.308l-5 3.214v7.728H5V6.522l5-3.214 5 3.214V13h0.001v7.728h0A1 1 0 0 1 15 20h0a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1v-7.728h0.001V6.522L10 3.308z" />
                    </svg>Nouveau Etudiant
                </h5>
                <button type="button" data-drawer-hide="drawer-form" aria-controls="drawer-form" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close menu</span>
                </button>
                <form method="post" class="mb-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                    <div class="mb-6">
                        <label for="Nom" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nom</label>
                        <input type="text" id="Nom" name="Nom" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Nom" required />
                    </div>
                    <div class="mb-6">
                        <label for="Prenom" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prenom</label>
                        <input type="Prenom" id="Prenom" name="Prenom" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Prenom" required />
                    </div>
                    <div class="mb-6">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Email" required />
                    </div>


                    <div class="mb-6">
                        <input checked id="Statut" name="Statut" value="yes" type="checkbox" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="Statut" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Actif</label>
                    </div>
                    <button type="submit" class=" w-full justify-center flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crée</button>

                </form>
            </div>


            <div class=" mx-10 mb-10 p-5 relative overflow-x-auto shadow-md sm:rounded-lg">
                <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
                    <div>
                        <span class="bg-gray-100 text-gray-800 font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300"> <?php echo count($users) ?> Utilisateurs</span>
                        <span class="bg-green-100 text-green-800  font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300"> <?php echo count(array_filter($users, fn($user) => $user['Statut'] === 'Actif')) ?> Utilisateurs actif</span>
                        <span class="bg-red-100 text-red-800  font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300"> <?php echo count(array_filter($users, fn($user) => $user['Statut'] !== 'Actif')) ?> Utilisateurs Inactif</span>


                    </div>
                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="text" id="table-search-users" name="search" class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search for users">
                        </form>
                    </div>
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>

                            <th scope="col" class="px-6 py-3">
                                Etudiant
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Adresse
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Derniere Connexion
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Téléphone
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <?php
                            foreach ($users as $user) {
                                echo '<tr>';
                                echo '<th scope="row" class="flex items-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        <img class="w-10 h-10 rounded-full" src="';

                                if (!empty($user['Photo_Profil'])) {
                                    echo 'data:image/jpeg;base64,' . base64_encode($user['Photo_Profil']);
                                } else {
                                    echo 'https://static.wikia.nocookie.net/herofanon/images/d/d0/Eren_Yeager.png/revision/latest?cb=20200310003401';
                                }
                                echo '" alt="Extra large avatar">';

                                echo ' <div class="ps-3">
                            <div class="text-base font-semibold">' . $user['Nom'] . ' ' . $user['Prenom'] . ' <br>' . $user['Nom_Arabe'] . ' ' . $user['Prenom_Arabe'] . ' </div>
                            <div class="font-normal text-gray-500">' . $user['Email'] . '</div>
                        </div> </th>';
                                echo '  <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-2.5 w-2.5 rounded-full me-2"></div> ' . $user['Adresse'] . '
                        </div>
                    </td>';
                                echo '  <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="h-2.5 w-2.5 rounded-full me-2"></div> ' . $user['Derniere_Connexion'] . '
                    </div>
                </td>';
                                echo '  <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-2.5 w-2.5 rounded-full me-2"></div> ' . $user['Telephone'] . '
                </div>
            </td>';
                                echo '<td class="px-6 py-4">
            <div class="flex items-center">
                <form method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
                    <input type="hidden" value="' . htmlspecialchars($user["ID_User"]) . '" name="ID_User" id="ID_User">';

                                if ($user['Statut'] === 'Actif') {
                                    echo '<select name="Statut" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" onchange="this.form.submit()">
                <option value="Actif" selected>Actif</option>
                <option value="Inactif">Inactif</option>
              </select>';
                                } else {
                                    echo '<select name="Statut" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" onchange="this.form.submit()">
                <option value="Actif">Actif</option>
                <option value="Inactif" selected>Inactif</option>
              </select>';
                                }

                                echo '      </form>
            </div>
          </td>';


                                echo '<td class="px-6 py-4">
        <form method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
            <button type="submit" value="' . $user['ID_User'] . '" name="Edit" 
                class="text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-800">
                Edit
            </button>
            <button type="submit" value="' . $user['ID_User'] . '" name="Delete" 
                class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                Supprimer
            </button>
        </form>
      </td>';

                                echo '</tr>';
                            }

                            ?>

                    </tbody>
                </table>
                <!-- Edit user modal -->
                <div id="editUserModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <form class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <!-- Modal header -->
                            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                <a class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Edit user
                                </a>
                                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editUserModal">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                            <!-- Modal body -->
                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="first-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">First Name</label>
                                        <input type="text" name="first-name" id="first-name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Bonnie" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="last-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Last Name</label>
                                        <input type="text" name="last-name" id="last-name" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Green" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                                        <input type="email" name="email" id="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="example@company.com" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="phone-number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone Number</label>
                                        <input type="number" name="phone-number" id="phone-number" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="e.g. +(12)3456 789" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="department" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Department</label>
                                        <input type="text" name="department" id="department" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Development" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="company" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Company</label>
                                        <input type="number" name="company" id="company" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="123456" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="current-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Current Password</label>
                                        <input type="password" name="current-password" id="current-password" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="••••••••" required="">
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="new-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New Password</label>
                                        <input type="password" name="new-password" id="new-password" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="••••••••" required="">
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="flex items-center p-6 space-x-3 rtl:space-x-reverse border-t border-gray-200 rounded-b dark:border-gray-600">
                                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Save all</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>

</html>