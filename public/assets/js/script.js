function confirmarLogout() {
    return confirm("Deseja realmente sair?");
}

document.addEventListener("DOMContentLoaded", function () {
    const elementoData = document.getElementById("data");

    if (elementoData) {
        const data = new Date();

        elementoData.textContent =
            data.toLocaleDateString("pt-BR");
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const elementoMensagem =
        document.getElementById("mensagem");

    if (elementoMensagem) {
        const mensagens = [
            "🌱 Pequenas mudanças geram grandes resultados.",
            "♻️ Reduzir o consumo ajuda o planeta.",
            "🚇 O transporte coletivo reduz as emissões.",
            "🌳 Cada ação conta para um futuro mais sustentável."
        ];

        const indice =
            Math.floor(Math.random() * mensagens.length);

        elementoMensagem.textContent =
            mensagens[indice];
    }
});