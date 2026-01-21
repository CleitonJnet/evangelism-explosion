/**
 * Frontend Module: tel-mask.js (Máscara global de telefone)
 *
 * Objetivo
 * - Aplicar máscara automaticamente em inputs type="tel" (formato brasileiro com DDD).
 * - Compatível com Livewire (pega inputs inseridos dinamicamente).
 * - Permite opt-out por atributo (data-no-tel-mask) e ignora o widget WhatsApp (.wa-phone-visible).
 *
 * Segurança contra duplicação
 * - O script é um IIFE que seta window.__globalTelMaskBound.
 * - Se o arquivo for carregado duas vezes, evita registrar listeners duplicados.
 *
 * Regras de máscara
 * - formatPhone(v):
 *   - remove não-dígitos e limita a 11 caracteres (DD + número).
 *   - formata progressivamente:
 *     - (DD) 1234-5678  (fixo/8 dígitos)
 *     - (DD) 91234-5678 (móvel/9 dígitos)
 *
 * Elegibilidade do campo
 * - shouldMask(el):
 *   - precisa ser input[type="tel"]
 *   - não pode ter classe .wa-phone-visible
 *   - não pode ter atributo data-no-tel-mask (válvula de escape)
 *
 * Aplicação em tempo real
 * - Listener global em "input":
 *   - se o alvo for elegível, aplica formatPhone no value.
 *
 * Aplicação inicial (opcional, mas útil)
 * - applyOnExisting():
 *   - mascara inputs já preenchidos no carregamento (old()/model), respeitando data-no-tel-mask.
 * - Executa em:
 *   - DOMContentLoaded
 *   - livewire:navigated (Livewire v3 / navegação SPA)
 */

(function attachGlobalTelMask() {
    // Evita registrar o listener mais de uma vez (caso o script seja carregado duas vezes)
    if (window.__globalTelMaskBound) return;
    window.__globalTelMaskBound = true;

    function formatPhone(v) {
        v = (v || "").replace(/\D/g, "").slice(0, 11);
        if (v.length <= 2) return v;
        const ddd = v.slice(0, 2);
        const num = v.slice(2);

        if (num.length <= 4) return `(${ddd}) ${num}`;
        if (num.length <= 8)
            return `(${ddd}) ${num.slice(0, 4)}-${num.slice(4)}`;
        return `(${ddd}) ${num.slice(0, 5)}-${num.slice(5)}`;
    }

    function shouldMask(el) {
        return (
            el &&
            el.matches &&
            el.matches('input[type="tel"]:not(.wa-phone-visible)') &&
            !el.hasAttribute("data-no-tel-mask")
        ); // “válvula de escape”
    }

    // Máscara em tempo real (pega inputs adicionados depois também — Livewire incluso)
    document.addEventListener("input", (e) => {
        if (!shouldMask(e.target)) return;
        e.target.value = formatPhone(e.target.value);
    });

    // Opcional: aplica máscara ao carregar (para campos já preenchidos por old()/model)
    function applyOnExisting() {
        document
            .querySelectorAll('input[type="tel"]:not(.wa-phone-visible)')
            .forEach((el) => {
                if (el.hasAttribute("data-no-tel-mask")) return;
                el.value = formatPhone(el.value);
            });
    }

    document.addEventListener("DOMContentLoaded", applyOnExisting);

    // Livewire v3 (quando navega/renderiza via SPA)
    document.addEventListener("livewire:navigated", applyOnExisting);
})();
