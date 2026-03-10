export function buildBaseOptions(payload) {
    const valueSuffix = payload?.options?.valueSuffix ?? "";
    const valuePrefix = payload?.options?.valuePrefix ?? "";

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: "index",
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: payload?.options?.legendPosition ?? "top",
                labels: {
                    usePointStyle: true,
                    boxWidth: 10,
                    color: "#334155",
                },
            },
            tooltip: {
                backgroundColor: "rgba(15, 23, 42, 0.92)",
                titleColor: "#f8fafc",
                bodyColor: "#e2e8f0",
                padding: 12,
                callbacks: {
                    label(context) {
                        const value = context.parsed?.y ?? context.parsed;

                        return `${context.dataset.label}: ${valuePrefix}${value}${valueSuffix}`;
                    },
                },
            },
        },
    };
}
