const formulario = document.querySelector("form");

formulario.addEventListener("submit", function (evento) {
    const email = document.querySelector('input[name="email"]').value;

    if (!email.includes("@")) {
        alert("Digite um e-mail válido.");

        evento.preventDefault();
    }
});