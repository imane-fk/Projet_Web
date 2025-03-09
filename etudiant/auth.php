<?php
include '../../back-end/connect.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ENSEM Scolarité</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <div class="flex flex-col md:flex-row h-screen">
    <div class="w-full flex items-center justify-center p-6">
      <div class="w-full max-w-md">
        <div class="mb-8 text-center">
          <img
            src="/public/images/logo_ensem.jpg"
            alt="ENSEM Logo"
            class="mx-auto mb-4 w-60 h-24" />
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">
          Se connecter sur ENSEM SCOLARITÉ
        </h2>

        <form>
          <div class="mb-4">
            <label for="username" class="block text-gray-700 font-medium mb-2">Identifiant</label>
            <input
              type="text"
              id="username"
              placeholder="Identifiant"
              required
              class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" />
          </div>
          <div class="mb-4">
            <label for="password" class="block text-gray-700 font-medium mb-2">Mot de passe</label>
            <div class="relative">
              <input
                type="password"
                id="password"
                placeholder="Mot de passe"
                required
                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" />
              <button
                type="button"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-600">
                <!-- Icon for show/hide password -->
                <svg
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24">
                  <!-- Icon Path -->
                </svg>
              </button>
            </div>
            <a
              href="#"
              class="text-sm text-purple-500 font-semibold mt-2 inline-block">Mot de passe oublié ?</a>
          </div>
          <button
            type="submit"
            class="w-full bg-purple-700 text-white font-medium py-2 rounded-lg hover:bg-purple-800 transition duration-200">
            Se connecter
          </button>
        </form>
      </div>
    </div>
    <div
      class="w-full h-screen bg-purple-700 hidden md:block md:flex items-center justify-center">
      <div class="bg-purple-700 text-white">
        <h2 class="text-3xl font-semibold text-center">ENSEM SCOLARITÉ</h2>
      </div>
    </div>
  </div>

</body>

</html>