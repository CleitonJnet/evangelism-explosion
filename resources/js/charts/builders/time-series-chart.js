import { buildBaseOptions } from "./base-config";

export function buildTimeSeriesChartConfig(payload) {
    return {
        type: payload.type ?? "line",
        data: {
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
                    type: "time",
                    time: {
                        unit: payload?.options?.xAxis?.unit ?? "month",
                    },
                    ticks: {
                        color: "#64748b",
                    },
                    grid: {
                        display: false,
                    },
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
                },
            },
        },
    };
}
