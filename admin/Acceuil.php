<?php
require_once('../back-end/connect.php');
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
if ($pdo) {
    $id_utilisateur = $_SESSION['user_id'];
    $admin = [];
    try {
        $stmt = $pdo->prepare("
          SELECT u.ID_User, u.Nom, u.Prenom, u.Derniere_Connexion, u.Date_Creation, 
               u.Telephone, u.Photo_Profil, u.Statut, u.Adresse, u.Email, 
               a.ID_Admin, a.Email_universitaire, a.Role, a.Date_Prise_Fonction, a.Date_Fin_Fonction, 
               a.Heures_Travail
        FROM Utilisateur u
        LEFT JOIN administration a ON u.ID_User = a.ID_User
        WHERE LOWER(u.Profil) = 'adm'
        AND u.ID_User = :userId
    ");

        $stmt->execute(['userId' => $id_utilisateur]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body class="bg-gray-100 text-gray-900">
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
        <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gradient-to-r from-blue-50 via-white to-blue-50">
            <div class="bg-white rounded-lg shadow-xl p-10 max-w-2xl text-center border border-gray-200">
                <!-- Titre principal -->
                <h1 class="text-5xl font-extrabold text-blue-700 mb-6">
                    Bienvenue, cher administrateur ! 🎉
                </h1>

                <!-- Photo de profil avec effet amélioré et centrée -->
                <div class="relative">
                    <img class="mx-auto rounded-full w-40 h-40 mb-4 border-4 border-blue-600 shadow-xl transition-transform duration-500 transform hover:scale-110 hover:rotate-3"
                        src="<?php echo !empty($admin['Photo_Profil'])
                                    ? 'data:image/jpeg;base64,' . base64_encode($admin['Photo_Profil'])
                                    : 'https://static.wikia.nocookie.net/herofanon/images/d/d0/Eren_Yeager.png/revision/latest?cb=20200310003401'; ?>"
                        alt="Photo de profil de l'administrateur" />
                    <div class="absolute top-2 right-2 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
                </div>

                <!-- Nom de l'administrateur -->
                <h2 class="text-3xl font-semibold text-gray-800 mb-4">
                    <?php echo htmlspecialchars($admin['Nom'] . ' ' . $admin['Prenom']); ?>
                </h2>

                <!-- Message de bienvenue -->
                <p class="text-lg text-gray-700 leading-relaxed">
                    Vous avez accès à <span class="font-medium text-blue-600">tous les outils</span> nécessaires pour gérer efficacement les étudiants, les cours, et optimiser vos tâches administratives.
                </p>

                <!-- Bloc d'information sur la mission -->
                <div class="mt-8 p-6 bg-blue-50 rounded-lg border-l-4 border-blue-600 shadow-sm">
                    <p class="text-xl italic font-medium text-blue-700">
                        "Votre mission : <span class="font-bold">Gérer, superviser</span> et optimiser les processus académiques pour une meilleure organisation."
                    </p>
                </div>

            </div>
        </div>


    </div>

</body>

</html>