import { buildBaseOptions } from "./base-config";

export function buildDoughnutChartConfig(payload) {
    return {
        type: payload.type ?? "doughnut",
        data: {
            labels: payload.labels ?? [],
            datasets: payload.datasets ?? [],
        },
        options: {
            ...buildBaseOptions(payload),
            cutout: "68%",
            scales: {},
        },
    };
}
