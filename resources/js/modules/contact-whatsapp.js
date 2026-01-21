(function () {
    "use strict";

    // Debounce util
    const debounce = (fn, wait = 150) => {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    };

    // Formatação por DDI (só dígitos -> formato legível)
    function formatByDDI(ddi, digits) {
        digits = (digits || "").replace(/\D/g, "");

        switch (ddi) {
            case "+55": {
                digits = digits.slice(0, 11);
                if (digits.length <= 2) return digits;
                const ddd = digits.slice(0, 2);
                const num = digits.slice(2);
                if (num.length <= 4) return `(${ddd}) ${num}`;
                if (num.length <= 8)
                    return `(${ddd}) ${num.slice(0, 4)}-${num.slice(4)}`;
                return `(${ddd}) ${num.slice(0, 5)}-${num.slice(5)}`;
            }
            case "+1": {
                digits = digits.slice(0, 10);
                if (!digits) return "";
                if (digits.length <= 3) return `(${digits}`;
                const a = digits.slice(0, 3),
                    b = digits.slice(3, 6),
                    c = digits.slice(6);
                if (digits.length <= 6) return `(${a}) ${b}`;
                return `(${a}) ${b}-${c}`;
            }
            case "+52":
            case "+54": {
                digits = digits.slice(0, 10);
                if (digits.length <= 2) return digits;
                const d = digits.slice(0, 2),
                    n = digits.slice(2);
                if (n.length <= 4) return `(${d}) ${n}`;
                return `(${d}) ${n.slice(0, 4)}-${n.slice(4)}`;
            }
            case "+56": {
                digits = digits.slice(0, 9);
                if (digits.length <= 1) return digits;
                return `${digits.slice(0, 1)} ${digits.slice(
                    1,
                    5
                )} ${digits.slice(5)}`;
            }
            case "+57": {
                digits = digits.slice(0, 10);
                if (digits.length <= 3) return digits;
                return `${digits.slice(0, 3)} ${digits.slice(
                    3,
                    6
                )} ${digits.slice(6)}`;
            }
            case "+51": {
                digits = digits.slice(0, 9);
                if (digits.length <= 2) return digits;
                return `(${digits.slice(0, 2)}) ${digits.slice(
                    2,
                    6
                )}-${digits.slice(6)}`;
            }
            case "+598":
            case "+595":
            case "+593":
            case "+591": {
                digits = digits.slice(0, 9);
                if (digits.length <= 2) return digits;
                const ddd = digits.slice(0, 2),
                    num = digits.slice(2);
                if (num.length <= 4) return `(${ddd}) ${num}`;
                return `(${ddd}) ${num.slice(0, 4)}-${num.slice(4)}`;
            }
            case "+351":
            case "+244": {
                digits = digits.slice(0, 9);
                if (digits.length <= 3) return digits;
                if (digits.length <= 6)
                    return `${digits.slice(0, 3)} ${digits.slice(3)}`;
                return `${digits.slice(0, 3)} ${digits.slice(
                    3,
                    6
                )} ${digits.slice(6)}`;
            }
            case "+258": {
                digits = digits.slice(0, 9);
                if (digits.length <= 2) return digits;
                if (digits.length <= 5)
                    return `${digits.slice(0, 2)} ${digits.slice(2)}`;
                return `${digits.slice(0, 2)} ${digits.slice(
                    2,
                    5
                )} ${digits.slice(5)}`;
            }
            case "+238":
            case "+239":
            case "+245": {
                digits = digits.slice(0, 8);
                if (digits.length <= 4) return digits;
                return `${digits.slice(0, 4)}-${digits.slice(4)}`;
            }
            case "+44": {
                digits = digits.slice(0, 10);
                if (digits.length <= 4) return digits;
                if (digits.length <= 7)
                    return `${digits.slice(0, 4)} ${digits.slice(4)}`;
                return `${digits.slice(0, 4)} ${digits.slice(
                    4,
                    7
                )} ${digits.slice(7)}`;
            }
            case "+33": {
                digits = digits.slice(0, 9);
                return digits.replace(
                    /(\d{1})(\d{2})(\d{2})(\d{2})(\d{0,2})/,
                    (m, a, b, c, d, e) =>
                        [a, b, c, d, e].filter(Boolean).join(" ")
                );
            }
            case "+49": {
                digits = digits.slice(0, 11);
                return digits.replace(/(\d{3})(?=\d)/g, "$1 ").trim();
            }
            case "+34":
            case "+39": {
                digits = digits.slice(0, 10);
                if (digits.length <= 3) return digits;
                if (digits.length <= 6)
                    return `${digits.slice(0, 3)} ${digits.slice(3)}`;
                return `${digits.slice(0, 3)} ${digits.slice(
                    3,
                    6
                )} ${digits.slice(6)}`;
            }
            case "+86":
            case "+91":
            case "+81":
            case "+27":
            case "+61":
            case "+64": {
                digits = digits.slice(0, 11);
                return digits.replace(/(\d{3})(?=\d)/g, "$1 ").trim();
            }
            default: {
                digits = digits.slice(0, 12);
                return digits.replace(/(\d{3})(?=\d)/g, "$1 ").trim();
            }
        }
    }

    // Gera placeholder a partir do campo 'sample' presente no JSON do componente
    function placeholderForDDI(ddi, ddis) {
        const item = Array.isArray(ddis)
            ? ddis.find((i) => i.code === ddi)
            : null;
        const sample = item?.sample || "000000000";
        const formatted = formatByDDI(ddi, sample) + " *";
        return formatted || sample;
    }

    // Filtra e mostra/oculta itens da lista
    function filterDDIList(list, term) {
        const items = list.querySelectorAll(".wa-ddi-item");
        items.forEach((item) => {
            const ddi = (item.dataset.ddi || "").toLowerCase();
            const name = (item.dataset.name || "").toLowerCase();
            const text = (item.textContent || "").toLowerCase();
            const match =
                !term ||
                ddi.includes(term) ||
                name.includes(term) ||
                text.includes(term);
            item.style.display = match ? "flex" : "none";
        });
    }

    // Inicializa controle de telefone para um widget
    function initPhoneControl(widget) {
        const wrapper = widget.querySelector(".wa-phone-group-wrapper");
        if (!wrapper) return;

        const ddiInput = wrapper.querySelector(".wa-ddi-input");
        const visible = wrapper.querySelector(".wa-phone-visible");
        const hiddenNum = wrapper.querySelector('input[name="phone_user"]');
        const hiddenDDI = wrapper.querySelector('input[name="phone_ddi"]');
        const dropdown = wrapper.querySelector(".wa-ddi-dropdown");
        const search = wrapper.querySelector(".wa-ddi-search");
        const list = wrapper.querySelector("[data-wa-ddi-list]");

        if (
            !ddiInput ||
            !visible ||
            !hiddenNum ||
            !hiddenDDI ||
            !dropdown ||
            !search ||
            !list
        )
            return;

        // Ler JSON do próprio widget
        let ddis = [];
        try {
            ddis = JSON.parse(widget.dataset.waDdis || "[]");
        } catch (err) {
            console.error("Falha ao parsear data-wa-ddis", err);
        }

        // Detectar DDI padrão por locale
        const detectDefaultDDI = () => {
            const lang = navigator.language || "";
            for (const item of ddis) {
                if (item.locales?.some((loc) => lang.startsWith(loc)))
                    return item;
            }
            return (
                ddis.find((i) => i.code === "+55") ||
                ddis[0] || {
                    code: "+55",
                    name: "Brasil",
                    flag: "🇧🇷",
                    sample: "11900000000",
                }
            );
        };

        const initial = detectDefaultDDI();
        hiddenDDI.value = initial.code;
        ddiInput.value = initial.code;

        // Atualiza placeholder inicial de acordo com o sample enviado pelo backend
        visible.placeholder = placeholderForDDI(initial.code, ddis);

        // Popula lista
        list.innerHTML = "";
        ddis.forEach((item) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "wa-ddi-item";
            btn.dataset.ddi = item.code;
            btn.dataset.flag = item.flag || "";
            btn.dataset.name = (item.name || "").toLowerCase();
            btn.style.display = "flex";
            btn.innerHTML = `<span class="flag">${item.flag || ""}</span>
                       <span class="code">${item.code}</span>
                       <span class="name">${item.name || ""}</span>`;
            list.appendChild(btn);
        });

        let activeIndex = -1;
        const getVisibleItems = () =>
            Array.from(list.querySelectorAll(".wa-ddi-item")).filter(
                (i) => i.style.display !== "none"
            );

        const setActiveItem = (index) => {
            const items = getVisibleItems();
            items.forEach((i) => i.classList.remove("is-active"));
            if (!items.length) {
                activeIndex = -1;
                return;
            }
            if (index < 0) index = items.length - 1;
            if (index >= items.length) index = 0;
            activeIndex = index;
            const it = items[activeIndex];
            it.classList.add("is-active");
            it.scrollIntoView({ block: "nearest" });
        };

        const clearActiveItem = () => {
            activeIndex = -1;
            list.querySelectorAll(".wa-ddi-item").forEach((i) =>
                i.classList.remove("is-active")
            );
        };

        const debouncedFilter = debounce((term) => {
            filterDDIList(list, term);
            clearActiveItem();
        }, 150);

        // Toggle dropdown ao clicar no DDI input
        ddiInput.addEventListener("click", () => {
            const isOpen = dropdown.classList.toggle("is-open");
            if (isOpen) {
                search.value = "";
                filterDDIList(list, "");
                clearActiveItem();
                search.focus();
            }
        });

        // Formata o número enquanto digita
        visible.addEventListener("input", () => {
            const ddi = hiddenDDI.value || initial.code;
            const digits = (visible.value || "").replace(/\D/g, "");
            hiddenNum.value = digits;
            visible.value = formatByDDI(ddi, digits);
        });

        // Busca no dropdown (debounced)
        search.addEventListener("input", () => {
            const term = search.value.trim().toLowerCase();
            debouncedFilter(term);
        });

        // Navegação por teclado na busca
        search.addEventListener("keydown", (e) => {
            const items = getVisibleItems();
            if (e.key === "Enter") {
                e.preventDefault();
                if (!items.length) return;
                const item = activeIndex >= 0 ? items[activeIndex] : items[0];
                item.click();
                return;
            }
            if (e.key === "ArrowDown") {
                e.preventDefault();
                if (!items.length) return;
                setActiveItem(activeIndex === -1 ? 0 : activeIndex + 1);
                return;
            }
            if (e.key === "ArrowUp") {
                e.preventDefault();
                if (!items.length) return;
                setActiveItem(
                    activeIndex === -1 ? items.length - 1 : activeIndex - 1
                );
                return;
            }
            if (e.key === "Escape") {
                e.preventDefault();
                dropdown.classList.remove("is-open");
                ddiInput.focus();
                return;
            }
        });

        // Clique em um item da lista
        list.addEventListener("click", (ev) => {
            const item = ev.target.closest(".wa-ddi-item");
            if (!item) return;
            const ddi = item.dataset.ddi;
            hiddenDDI.value = ddi;
            ddiInput.value = ddi;

            // Atualiza placeholder quando o país é selecionado (usa sample do backend)
            visible.placeholder = placeholderForDDI(ddi, ddis);

            const currentNumber = (hiddenNum.value || "").trim();
            visible.value = formatByDDI(ddi, currentNumber);
            dropdown.classList.remove("is-open");
            visible.focus();
        });

        // Fecha dropdown ao clicar fora (captura)
        document.addEventListener(
            "click",
            (e) => {
                if (!dropdown.classList.contains("is-open")) return;
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.remove("is-open");
                    clearActiveItem();
                }
            },
            { capture: true }
        );
    }

    // Máscara global para input[type="tel"], exceto o widget (.wa-phone-visible)
    (function attachGlobalTelMask() {
        function formatPhone(v) {
            v = v.replace(/\D/g, "").slice(0, 11);
            if (v.length <= 2) return v;
            const ddd = v.slice(0, 2),
                num = v.slice(2);
            if (num.length <= 4) return `(${ddd}) ${num}`;
            if (num.length <= 8)
                return `(${ddd}) ${num.slice(0, 4)}-${num.slice(4)}`;
            return `(${ddd}) ${num.slice(0, 5)}-${num.slice(5)}`;
        }
        document.addEventListener("input", (e) => {
            if (e.target.matches('input[type="tel"]:not(.wa-phone-visible)')) {
                e.target.value = formatPhone(e.target.value);
            }
        });
    })();

    // Popula widgets e inicializa controles
    document.querySelectorAll(".wa-widget").forEach((widget) => {
        // Init phone control (popula lista usando data-wa-ddis)
        initPhoneControl(widget);
    });

    // Modal toggle (abertura / fechamento)
    function getWidget(el) {
        return el.closest(".wa-widget");
    }
    function toggleModal(widget) {
        if (!widget) return;
        const modal = widget.querySelector(".wa-modal");
        if (!modal) return;
        const isOpen = modal.classList.toggle("is-open");
        modal.setAttribute("aria-hidden", isOpen ? "false" : "true");
        if (isOpen) {
            modal.querySelector(".wa-form .wa-input")?.focus();
        }
    }

    document.addEventListener("click", (e) => {
        const trigger = e.target.closest(".wa-button, .wa-close");
        if (!trigger) return;
        const widget = getWidget(trigger);
        if (!widget) return;
        toggleModal(widget);
    });

    // Envio do formulário (cria link wa.me)
    document.addEventListener("submit", (e) => {
        const form = e.target.closest(".wa-form");
        if (!form) return;
        e.preventDefault();
        const widget = getWidget(form);
        if (!widget) return;

        const phoneDestRaw = widget.dataset.waPhone || "";
        const phoneDest = phoneDestRaw.replace(/\D/g, "").trim(); // número destino só dígitos

        const data = new FormData(form);
        const get = (n) => (data.get(n) || "").toString().trim();

        const name = get("name");
        const email = get("email");
        const rawNum = get("phone_user"); // DDD + número (só dígitos)
        const ddi = get("phone_ddi") || "+55";
        const church = get("church");
        const subject = get("subject");
        const comment = get("comment");

        if (!name || !email || !rawNum || !church || !subject) {
            alert("Por favor, preencha todos os campos obrigatórios.");
            return;
        }

        if (!phoneDest) {
            console.warn("WhatsApp widget: número destino não configurado.");
            alert(
                "Número de atendimento não configurado. Contate o administrador do site."
            );
            return;
        }

        const phoneDisplay = formatByDDI(ddi, rawNum);
        const msg = encodeURIComponent(
            `*MENSAGEM DO SITE:*\n\n` +
                `Nome: _${name}_\n` +
                `E-mail: ${email}\n` +
                `Telefone: ${ddi ? ddi + " " : ""}${phoneDisplay || rawNum}\n` +
                `Igreja: _${church}_\n\n` +
                `*Assunto:* _${subject}_\n\n` +
                `*Mensagem:* \n ${comment}`
        );

        window.open(
            `https://wa.me/${phoneDest}?text=${msg}`,
            "_blank",
            "noopener"
        );
        toggleModal(widget);
    });
})();

function openWhatsAppModal() {
    const modal = document.getElementById("waModal");
    if (modal) {
        modal.classList.add("is-open");
    }
}

function closeWhatsAppModal() {
    const modal = document.getElementById("waModal");
    if (modal) {
        modal.classList.remove("is-open");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-open-wa]").forEach((btn) => {
        btn.addEventListener("click", (ev) => {
            ev.preventDefault();

            // dispara o clique no botão flutuante do widget
            const waBtn = document.querySelector(".wa-widget .wa-button");
            waBtn?.click();
        });
    });
});
