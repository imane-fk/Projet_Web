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
$errorMessage = '';
$errorTitle = '';
$successMessage = '';
$successTitle = '';
$userId = '';
$etudiantId = '';
$user = [];
$assurance = [];
$paiement = [];
function FetchEtudiant($pdo, $userId, &$errorMessage, &$errorTitle)
{
    try {
        $stmt = $pdo->prepare("
        SELECT u.ID_User, u.Nom,u.Prenom,u.Derniere_Connexion,u.Date_Creation,u.Telephone,u.Photo_Profil,u.Statut,u.Adresse, u.Email, e.ID_Etudiant, e.Nom_Arabe,e.Prenom_Arabe,e.CNE,e.Date_Naissance,e.Sexe,e.Telephone_Parents,e.Adresse_Parents,e.Nationalite,e.Email_Universitaire,e.Email_Universitaire,e.Boursier,e.Boursier,e.Acte_Cautionnement,e.Reglement_Vie_Collective,e.Reglement_Etudes,e.Cliche_Pulmonaire,e.Certificat_Medical,e.Copie_Baccalaureat
        FROM Utilisateur u
        LEFT JOIN etudiant e ON u.ID_User = e.ID_Utilisateur
        WHERE LOWER(u.Profil) = 'etu'
        AND u.ID_User = :userId
    ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($results)) {
            echo "<script>
            Swal.fire({
                position: 'center', // Position the alert in the center
                icon: 'error', // Show an error icon
                title: 'Erreur de recherche!', // The title of the alert
                showConfirmButton: false, // Disable the confirm button
                timer: 3000, // Alert will close automatically after 3 seconds
                text: 'Aucun utilisateur avec le profil \"etu\" n\'a été trouvé.' // Error message text
            }).then(() => {
                // Redirect the user to the comptes-etudiants.php page after the alert is closed
                window.location.href = './comptes-etudiants.php';
            });
            </script>";
        }

        return $results;
    } catch (PDOException $e) {
        $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
        $errorTitle = "Échec";
        exit;
    }
}
function FetchAssurance($pdo, $etudiantId, &$errorMessage, &$errorTitle)
{
    try {
        $stmt = $pdo->prepare("
        SELECT a.ID_Assurance, a.Montant, a.Justificatif, an.Date_Debut, an.Date_Fin
        FROM assurance a
        LEFT JOIN annee_universitaire an ON  a.ID_Annee=an.ID_Annee
        WHERE ID_Etudiant = :userId

        ");
        $stmt->bindParam(':userId', $etudiantId, PDO::PARAM_INT);
        $stmt->execute();


        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if (!empty($results)) {
            $assurance = [];
            foreach ($results as $result) {
                $assurance[] = $result;
            }
            return $assurance;
        }


        return $results;
    } catch (PDOException $e) {
        // Handle exception and set error message
        $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
        $errorTitle = "Échec";
        exit;
    }
}
function FetchPaiement($pdo, $etudiantId, &$errorMessage, &$errorTitle)
{
    try {
        $stmt = $pdo->prepare("
        SELECT p.ID_Paiement, p.Caution_Annuel, p.Date_Paiement, p.Justificatif, p.Prix_Restau, p.prix_internat, an.Date_Debut, an.Date_Fin
        FROM paiement p
        LEFT JOIN annee_universitaire an ON p.ID_Annee = an.ID_Annee
        WHERE ID_Etudiant = :userId
        ");
        $stmt->bindParam(':userId', $etudiantId, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($results)) {
            $paiement = [];
            foreach ($results as $result) {

                $paiement[] = $result;
            }
            return $paiement;
        }


        return $results;
    } catch (PDOException $e) {
        echo $e->getMessage();
        $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
        $errorTitle = "Échec";
        exit;
    }
}

if (!isset($pdo)) {
    $errorMessage = "Échec de la connexion à la base de données.";
    $errorTitle = "Erreur de connexion";
} else {
    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];
        $user = FetchEtudiant($pdo, $userId, $errorMessage, $errorTitle);
        $assurance = FetchAssurance($pdo, $user['ID_Etudiant'], $errorMessage, $errorTitle);
        $paiement = FetchPaiement($pdo, $user['ID_Etudiant'], $errorMessage, $errorTitle);
    } else {
        echo "<script>
        Swal.fire({
            position: 'center', // Position the alert in the center
            icon: 'error', // Show an error icon
            title: 'Erreur de recherche!', // The title of the alert
            showConfirmButton: false, // Disable the confirm button
            timer: 3000, // Alert will close automatically after 3 seconds
            text: 'ID utilisateur non fourni.' // Error message text
        }).then(() => {
            // Redirect the user to the comptes-etudiants.php page after the alert is closed
            window.location.href = './comptes-etudiants.php';
        });
        </script>";
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

    <script>
        function showPDF(base64Data) {
            var iframe = document.getElementById('pdfIframe');
            var pdfData = 'data:application/pdf;base64,' + base64Data;
            iframe.src = pdfData; // Set the iframe source to display the PDF
            document.getElementById('pdfContainer').style.display = 'block'; // Show the iframe container
        }
    </script>
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
        <?php if (!empty($user)) : ?>
            <form class="m-5" method="POST" action="../back-end/updateStudent.php" enctype="multipart/form-data">

                <!-- Information personelle -->
                <div class="w-full flex flex-col items-center justify-center bg-white p-6 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
                    <img class="rounded-full w-40 h-40 mb-4 border-2 border-blue-500 shadow-lg transition-transform duration-300 transform hover:scale-105"
                        src="<?php echo !empty($user['Photo_Profil'])
                                    ? 'data:image/jpeg;base64,' . base64_encode($user['Photo_Profil'])
                                    : 'https://static.wikia.nocookie.net/herofanon/images/d/d0/Eren_Yeager.png/revision/latest?cb=20200310003401'; ?>"
                        alt="Extra large avatar">
                    <span class="bg-purple-100 text-purple-800 mb-5 font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-purple-900 dark:text-purple-300"><?php echo $user['Prenom'] . ' ' . $user['Nom']; ?></span>

                    <input type="file" id="Photo_Profil" name="Photo_Profil" accept="image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full max-w-xs p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                </div>
                <input type="text" value="<?php echo $user['ID_User']; ?>" hidden name="ID_User" id="ID_User">
                <input type="text" value="<?php echo $user['ID_Etudiant']; ?>" hidden name="ID_Etudiant" id="ID_Etudiant">
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="text-center bg-blue-100 text-blue-800  font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                        Les informations Personnel
                    </span>
                </div>


                <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">
                    <div class="px-2 mb-4">
                        <label for="Nom" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nom</label>
                        <input value="<?php echo $user['Nom']; ?>" type="text" id="Nom" name="Nom" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Prenom" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prenom</label>
                        <input value="<?php echo $user['Prenom']; ?>" type="text" id="Prenom" name="Prenom" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>

                    <div class="px-2 mb-4">
                        <label for="Nom_Arabe" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nom (Arabe)</label>
                        <input value="<?php echo $user['Nom_Arabe']; ?>" type="text" id="Nom_Arabe" name="Nom_Arabe" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Prenom_Arabe" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prenom (Arabe)</label>
                        <input value="<?php echo $user['Prenom_Arabe']; ?>" type="text" id="Prenom_Arabe" name="Prenom_Arabe" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Telephone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Téléphone</label>
                        <input value="<?php echo $user['Telephone']; ?>" type="tel" id="Telephone" name="Telephone" pattern="[0-9]{10}" required placeholder="0123456789" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="EmailP" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Personnel</label>
                        <input value="<?php echo $user['Email']; ?>" type="email" id="EmailP" name="EmailP" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Adresse" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adresse</label>
                        <input value="<?php echo $user['Adresse']; ?>" type="text" id="Adresse" name="Adresse" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>

                    <div class="px-2 mb-4">
                        <label for="Date_Naissance" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date de Naissance</label>
                        <input value="<?php echo $user['Date_Naissance']; ?>" type="date" id="Date_Naissance" name="Date_Naissance" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Sexe" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sexe</label>
                        <select id="Sexe" name="Sexe" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <option value="" disabled <?php echo empty($user['Sexe']) ? 'selected' : ''; ?>>Select Sexe</option>
                            <option value="Homme" <?php echo $user['Sexe'] === 'Homme' ? 'selected' : ''; ?>>Homme</option>
                            <option value="Femme" <?php echo $user['Sexe'] === 'Femme' ? 'selected' : ''; ?>>Femme</option>
                        </select>

                    </div>
                    <div class="px-2 mb-4">
                        <label for="Nationalite" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nationalité</label>
                        <input value="<?php echo $user['Nationalite']; ?>" type="text" id="Nationalite" name="Nationalite" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                </div>
                <!-- Account Information -->
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="bg-green-100 text-green-800 font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300"> Les informations sur le compte</span>
                </div>
                <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">
                    <div class="px-2 mb-4">
                        <label for="Derniere_Connexion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dernière Connexion</label>
                        <input value="<?php echo $user['Derniere_Connexion']; ?>" type="text" disabled id="Derniere_Connexion" name="Derniere_Connexion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Date_Creation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Création</label>
                        <input value="<?php echo $user['Date_Creation']; ?>" type="text" disabled id="Date_Creation" name="Date_Creation" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Statut" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Statut</label>
                        <select id="Statut" name="Statut" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <option value="" disabled <?php echo empty($user['Statut']) ? 'selected' : ''; ?>>Select Statut</option>
                            <option value="Actif" <?php echo $user['Statut'] === 'Actif' ? 'selected' : ''; ?>>Actif</option>
                            <option value="Inactif" <?php echo $user['Statut'] === 'Inactif' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                </div>


                <!-- Parent Information -->
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="bg-yellow-100 text-yellow-800 font-medium me-2 px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300"> Les informations sur les parents</span>
                </div>
                <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">
                    <div class="px-2 mb-4">
                        <label for="Telephone_Parents" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Téléphone des Parents</label>
                        <input value="<?php echo $user['Telephone_Parents']; ?>" type="tel" id="Telephone_Parents" name="Telephone_Parents" pattern="[0-9]{10}" placeholder="0123456789" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Adresse_Parents" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adresse des Parents</label>
                        <input value="<?php echo $user['Adresse_Parents']; ?>" type="text" id="Adresse_Parents" name="Adresse_Parents" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                </div>
                <!-- Student Information -->
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="bg-purple-100 text-purple-800 font-medium me-2 px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300"> Les informations sur l'étude</span>
                </div>
                <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">
                    <div class="px-2 mb-4">
                        <label for="CNE" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">CNE</label>
                        <input value="<?php echo $user['CNE']; ?>" type="text" id="CNE" name="CNE" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Email_Universitaire" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Universitaire</label>
                        <input value="<?php echo $user['Email_Universitaire']; ?>" type="email" id="Email_Universitaire" name="Email_Universitaire" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    </div>
                    <div class="px-2 mb-4">
                        <label for="Boursier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Boursier</label>
                        <select id="Boursier" name="Boursier" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            <option value="" disabled <?php echo !isset($user['Boursier']) ? 'selected' : ''; ?>>Select</option>
                            <option value="Oui" <?php echo isset($user['Boursier']) && $user['Boursier'] == true ? 'selected' : ''; ?>>Oui</option>
                            <option value="Non" <?php echo isset($user['Boursier']) && $user['Boursier'] == false ? 'selected' : ''; ?>>Non</option>
                        </select>

                    </div>
                    <div></div>
                    <div class="px-2 mb-4">
                        <label for="Acte_Cautionnement" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Acte de Cautionnement</label>

                        <input
                            type="file"
                            id="Acte_Cautionnement"
                            name="Acte_Cautionnement"
                            accept="application/pdf"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Acte_Cautionnement'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Acte_Cautionnement']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Acte_Cautionnement.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Acte_Cautionnement'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>

                    <div class="px-2 mb-4">
                        <label for="Reglement_Vie_Collective" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Règlement Vie Collective</label>
                        <input
                            type="file"
                            id="Reglement_Vie_Collective"
                            name="Reglement_Vie_Collective"
                            accept="application/pdf,image/*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Reglement_Vie_Collective'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Reglement_Vie_Collective']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Reglement_Vie_Collective.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Reglement_Vie_Collective'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>

                    <div class="px-2 mb-4">
                        <label for="Reglement_Etudes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Règlement des Études</label>
                        <input
                            type="file"
                            id="Reglement_Etudes"
                            name="Reglement_Etudes"
                            accept="application/pdf,image/*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Reglement_Etudes'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Reglement_Etudes']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Reglement_Etudes.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Reglement_Etudes'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>

                    <div class="px-2 mb-4">
                        <label for="Cliche_Pulmonaire" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cloche Pulmonaire</label>
                        <input
                            type="file"
                            id="Cliche_Pulmonaire"
                            name="Cliche_Pulmonaire"
                            accept="application/pdf,image/*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Cliche_Pulmonaire'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Cliche_Pulmonaire']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Cliche_Pulmonaire.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Cliche_Pulmonaire'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>

                    <div class="px-2 mb-4">
                        <label for="Certificat_Medical" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Certificat Médical</label>
                        <input
                            type="file"
                            id="Certificat_Medical"
                            name="Certificat_Medical"
                            accept="application/pdf,image/*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Cliche_Pulmonaire'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Cliche_Pulmonaire']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Cliche_Pulmonaire.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Cliche_Pulmonaire'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>

                    <div class="px-2 mb-4">
                        <label for="Copie_Baccalaureat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Copie Baccalauréat</label>
                        <input
                            type="file"
                            id="Copie_Baccalaureat"
                            name="Copie_Baccalaureat"
                            accept="application/pdf,image/*"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                        <?php if (!empty($user['Copie_Baccalaureat'])): ?>
                            <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($user['Copie_Baccalaureat']); ?>"
                                download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Copie_Baccalaureat.pdf">Télécharger le reçu de paiement</a>
                        <?php endif; ?>
                        <?php if (empty($user['Copie_Baccalaureat'])): ?>
                            <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="bg-purple-100 text-pink-800 font-medium me-2 px-2.5 py-0.5 rounded dark:bg-pink-900 dark:text-pink-300"> Assurances</span>
                </div>
                <?php if (!empty($assurance)) : ?>
                    <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">
                        <?php foreach ($assurance as $item) : ?>
                            <div class="px-2 mb-4">
                                <label for="Date_Debut" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Année scolaire</label>
                                <input disabled value="<?php echo date('Y', strtotime($item['Date_Debut'])) . '/' . date('Y', strtotime($item['Date_Fin'])); ?>" type="text" id="Date_Debut" name="Date_Debut" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div class="px-2 mb-4">
                                <label for="Montant" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Montant</label>
                                <input disabled value="<?php echo htmlspecialchars($item['Montant']); ?>" type="text" id="Montant" name="Montant" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div class="px-2 mb-4">
                                <label for="Justificatif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Justificatif</label>
                                <input type="file" id="Justificatif" name="JustificatifAssurance" accept="application/pdf,image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                                <?php if (!empty($item['Justificatif'])): ?>
                                    <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($item['Justificatif']); ?>"
                                        download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Justificatif_assurance.pdf">Télécharger le reçu de paiement</a>
                                <?php endif; ?>
                                <?php if (empty($item['Justificatif'])): ?>
                                    <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                                <?php endif; ?>

                                <input type="number" name="ID_Assurance" value="<?php echo htmlspecialchars($item['ID_Assurance']); ?>" hidden>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (empty($assurance)) : ?>
                    <div class="w-full p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                        <span class="font-medium">Aucune assurance téléchargée.</span>
                    </div>
                <?php endif; ?>
                <div class="w-full flex items-center justify-center bg-white p-4 rounded-lg">
                    <span class="bg-purple-100 text-pink-800 font-medium me-2 px-2.5 py-0.5 rounded dark:bg-pink-900 dark:text-pink-300"> Paiement</span>
                </div>

                <?php if (!empty($paiement)): ?>
                    <?php foreach ($paiement as $item) : ?>
                        <div class="grid grid-cols-4 gap-4 -mx-2 w-full bg-white dark:bg-gray-800 shadow p-4 rounded-lg">

                            <div class="px-2 mb-4">
                                <label for="Date_Debut" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Année scolaire</label>
                                <input disabled value="<?php echo date('Y', strtotime($item['Date_Debut'])) . '/' . date('Y', strtotime($item['Date_Fin'])); ?>" type="text" id="Date_Debut" name="Date_Debut" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div class="px-2 mb-4">
                                <label for="Caution_Annuel" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Caution Annuel</label>
                                <input disabled value="<?php echo htmlspecialchars($item['Caution_Annuel']); ?>" type="text" id="Caution_Annuel" name="Caution_Annuel" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div class="px-2 mb-4">
                                <label for="prix_resteau" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prix Resteau</label>
                                <input disabled value="<?php echo htmlspecialchars($item['Prix_Restau']); ?>" type="text" id="prix_resteau" name="prix_resteau" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div class="px-2 mb-4">
                                <label for="prix_internat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prix Internat</label>
                                <input disabled value="<?php echo htmlspecialchars($item['prix_internat']); ?>" type="text" id="prix_internat" name="prix_internat" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>

                            <div class="px-2 mb-4">
                                <label for="Justificatif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Justificatif</label>
                                <input type="file" id="JustificatifPaiement" name="JustificatifPaiement" accept="application/pdf,image/*" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                                <?php if (!empty($item['Justificatif'])): ?>
                                    <a class="text-blue-600" href="data:application/pdf;base64,<?php echo base64_encode($item['Justificatif']); ?>"
                                        download="<?php echo htmlspecialchars($user['Prenom'] . '_' . $user['Nom']); ?>_Justificatif_Paiement.pdf">Télécharger le reçu de paiement</a>
                                <?php endif; ?>
                                <?php if (empty($item['Justificatif'])): ?>
                                    <p class="text-sm text-red-600 dark:text-red-400">Aucun fichier n'a été téléchargé.</p>
                                <?php endif; ?>
                                <input type="number" name="ID_Paiement" value="<?php echo htmlspecialchars($item['ID_Paiement']); ?>" hidden>

                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
                <?php if (empty($paiement)) : ?>
                    <div cfull p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                        <span class="font-medium">Aucune paiement téléchargée.</span>
                    </div>
                <?php endif; ?>
    </div>
    <div class="text-center mt-4">
        <button type="submit" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-cyan-500 to-blue-500 group-hover:from-cyan-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-cyan-200 dark:focus:ring-cyan-800">
            <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                Enregistrer
            </span>
        </button>
    </div>
    </div>
    </form>
<?php endif; ?>
</div>



</body>

</html>