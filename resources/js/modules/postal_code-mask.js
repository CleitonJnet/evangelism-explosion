/**
 * Frontend Module: postal_code-mask.js (Máscara de CEP)
 *
 * Objetivo
 * - Aplicar máscara e sanitização em inputs de CEP identificados pelo seletor ".postal_code".
 * - Manter a entrada “limpa” (somente dígitos) e formatada no padrão: 12.345-678.
 * - Funcionar bem com Livewire (inclui inputs renderizados/atualizados dinamicamente).
 *
 * Como funciona
 * - SELECTOR = ".postal_code": todo input com essa classe é “elegível” para máscara.
 * - onlyDigits(v): remove não-dígitos e limita o CEP a 8 caracteres.
 * - formatCep(digits): formata progressivamente:
 *   - até 2 dígitos: "12"
 *   - até 5 dígitos: "12.345"
 *   - completo:      "12.345-678"
 * - applyMask(input): aplica a máscara apenas quando necessário (reduz re-render/“briga” com Livewire).
 *
 * Inicialização e idempotência
 * - setup(input):
 *   - impede inicialização duplicada via dataset (cepMaskInit="1").
 *   - registra listeners:
 *     - "input": mascara em tempo real (digitar/colar/arrastar).
 *     - "focus": se já estiver completo (8 dígitos), seleciona o texto para facilitar substituição.
 *   - aplica a máscara imediatamente no campo.
 * - init(root):
 *   - varre (root || document) e executa setup em todos os inputs elegíveis.
 *
 * Integração com Livewire
 * - Escuta eventos comuns (v2/v3) para reaplicar init quando o DOM muda:
 *   - livewire:load, livewire:init, livewire:navigated, livewire:update.
 * - Escuta também o evento customizado "reapply-cep-mask":
 *   - usado pelo componente AddressFields após preencher dados via ViaCEP,
 *     garantindo que o CEP exibido fique no formato esperado.
 *
 * API pública mínima
 * - window.CEPMask = { init, setup }
 *   (útil se algum trecho do app precisar reinicializar manualmente).
 */

(function (global) {
    "use strict";

    const SELECTOR = ".postal_code";

    function onlyDigits(v) {
        return (v || "").toString().replace(/\D/g, "").slice(0, 8);
    }

    // Mantém seu padrão: 12.345-678
    function formatCep(digits) {
        if (digits.length <= 2) return digits;
        if (digits.length <= 5)
            return `${digits.slice(0, 2)}.${digits.slice(2)}`;
        return `${digits.slice(0, 2)}.${digits.slice(2, 5)}-${digits.slice(5)}`;
    }

    function applyMask(input) {
        const digits = onlyDigits(input.value);
        const masked = formatCep(digits);

        // Evita mexer no DOM sem necessidade (reduz "briga" com Livewire)
        if (input.value !== masked) input.value = masked;
    }

    function setup(input) {
        if (!input || input.dataset.cepMaskInit === "1") return;
        input.dataset.cepMaskInit = "1";

        // Sanitiza/mascara no input (pasta, digita, arrasta... tudo funciona)
        input.addEventListener("input", () => applyMask(input), {
            passive: true,
        });

        // "Anti-travamento": se já está completo, foco seleciona tudo para substituir digitando
        input.addEventListener("focus", () => {
            const digits = onlyDigits(input.value);
            if (digits.length === 8) input.select();
        });

        // Garante máscara já no carregamento
        applyMask(input);
    }

    function init(root) {
        (root || document).querySelectorAll(SELECTOR).forEach(setup);
    }

    // DOM pronto
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => init());
    } else {
        init();
    }

    // Livewire: tenta cobrir v2/v3 sem depender de um evento específico
    document.addEventListener("livewire:load", () => init());
    document.addEventListener("livewire:init", () => init());
    document.addEventListener("livewire:navigated", () => init());
    document.addEventListener("livewire:update", (e) =>
        init(e.target || document)
    );

    // Seu evento atual (mantido)
    document.addEventListener("reapply-cep-mask", () => {
        init();
        document.querySelectorAll(SELECTOR).forEach((el) => applyMask(el));
    });

    // API pública (mantida, porém mínima)
    global.CEPMask = { init, setup };
})(window);
