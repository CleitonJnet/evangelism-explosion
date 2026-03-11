import { buildBaseOptions } from "./base-config";

export function buildCategoryChartConfig(payload) {
    return {
        type: payload.type,
        data: {
            labels: payload.labels ?? [],
            datasets: (payload.datasets ?? []).map((dataset) => ({
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                ...dataset,
            })),
        },
        options: {
            ...buildBaseOptions(payload),
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: "#64748b",
                    },
                    stacked: payload?.options?.stacked ?? false,
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: "#64748b",
                        precision: 0,
                    },
                    grid: {
                        color: "rgba(148, 163, 184, 0.18)",
                    },
                    stacked: payload?.options?.stacked ?? false,
                },
            },
        },
    };
}
