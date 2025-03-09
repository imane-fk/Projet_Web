// use this simple function to automatically focus on the next input
function focusNextInput(el, prevId, nextId) {
  if (el.value.length === 0) {
    if (prevId) {
      document.getElementById(prevId).focus();
    }
  } else {
    if (nextId) {
      document.getElementById(nextId).focus();
    }
  }
}

document
  .querySelectorAll("[data-focus-input-init]")
  .forEach(function (element) {
    element.addEventListener("keyup", function () {
      const prevId = this.getAttribute("data-focus-input-prev");
      const nextId = this.getAttribute("data-focus-input-next");
      focusNextInput(this, prevId, nextId);
    });

    // Handle paste event to split the pasted code into each input
    element.addEventListener("paste", function (event) {
      event.preventDefault();
      const pasteData = (event.clipboardData || window.clipboardData).getData(
        "text"
      );
      const digits = pasteData.replace(/\D/g, ""); // Only take numbers from the pasted data

      // Get all input fields
      const inputs = document.querySelectorAll("[data-focus-input-init]");

      // Iterate over the inputs and assign values from the pasted string
      inputs.forEach((input, index) => {
        if (digits[index]) {
          input.value = digits[index];
          // Focus the next input after filling the current one
          const nextId = input.getAttribute("data-focus-input-next");
          if (nextId) {
            document.getElementById(nextId).focus();
          }
        }
      });
    });
  });

function verifyPassword() {
  // Get the values of the two password fields
  const password1 = document.getElementById("passwd1").value;
  const password2 = document.getElementById("passwd2").value;
  const submitButton = document.getElementById("submitButton");
  // Regex to check password rules
  const passwordRules =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

  // Error message container
  const errorDiv = document.getElementById("passwordError");

  // Check if the password meets the rules
  if (!passwordRules.test(password1)) {
    submitButton.disabled = true;
    submitButton.classList.add("cursor-not-allowed", "opacity-50");
    errorDiv.textContent =
      "Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un symbole.";
    return; // Stop further checks
  }

  // Check if the two passwords match
  if (password1 !== password2) {
    errorDiv.textContent = "Les mots de passe ne correspondent pas.";
    submitButton.disabled = true;
    submitButton.classList.add("cursor-not-allowed", "opacity-50");

    return;
  }

  // If everything is fine, clear the error message
  errorDiv.textContent = "";
  submitButton.classList.remove("cursor-not-allowed", "opacity-50");
  submitButton.disabled = false;
}
document.addEventListener("DOMContentLoaded", function () {
  const inputs = document.querySelectorAll("input[type='text']");
  const handlePaste = (event) => {
    event.preventDefault();
    const pastedText = event.clipboardData.getData("text");
    const characters = pastedText.split("");
    let i = 0;
    inputs.forEach((input) => {
      if (characters[i]) {
        input.value = characters[i];
        i++;
      } else {
        input.value = "";
      }
    });
  };

  inputs.forEach((input) => {
    input.addEventListener("paste", handlePaste);
  });
});
