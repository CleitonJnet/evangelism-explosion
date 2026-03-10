import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    TimeScale,
    Title,
    Tooltip,
    BarController,
} from "chart.js";
import "chartjs-adapter-date-fns";
import { buildCategoryChartConfig } from "./builders/category-chart";
import { buildDoughnutChartConfig } from "./builders/doughnut-chart";
import { buildTimeSeriesChartConfig } from "./builders/time-series-chart";

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    TimeScale,
    Title,
    Tooltip,
);

const chartInstances = new Map();
let mutationObserver = null;

function resolveConfig(payload) {
    if (payload?.seriesType === "time") {
        return buildTimeSeriesChartConfig(payload);
    }

    if (payload?.type === "doughnut") {
        return buildDoughnutChartConfig(payload);
    }

    return buildCategoryChartConfig(payload);
}

function parsePayload(root) {
    const payloadNode = root.querySelector("[data-dashboard-chart-payload]");

    if (!payloadNode?.textContent) {
        return null;
    }

    try {
        return JSON.parse(payloadNode.textContent);
    } catch (error) {
        console.error("dashboard chart payload parse failed", error);

        return null;
    }
}

function destroyChart(root) {
    const chartId = root.dataset.chartId;

    if (!chartId || !chartInstances.has(chartId)) {
        return;
    }

    chartInstances.get(chartId)?.destroy();
    chartInstances.delete(chartId);
}

function renderChart(root) {
    const canvas = root.querySelector("[data-dashboard-chart-canvas]");
    const payload = parsePayload(root);
    const chartId = root.dataset.chartId;
    const signature = root.dataset.chartSignature;

    if (!canvas || !payload) {
        destroyChart(root);

        return;
    }

    if (!chartId) {
        return;
    }

    if (
        root.dataset.chartRendered === "true" &&
        root.dataset.chartRenderedSignature === signature &&
        chartInstances.has(chartId)
    ) {
        return;
    }

    destroyChart(root);

    chartInstances.set(chartId, new Chart(canvas, resolveConfig(payload)));
    root.dataset.chartRendered = "true";
    root.dataset.chartRenderedSignature = signature ?? "";
}

function refreshCharts(scope = document) {
    scope.querySelectorAll("[data-dashboard-chart]").forEach((root) => {
        renderChart(root);
    });
}

function cleanupCharts() {
    chartInstances.forEach((instance, chartId) => {
        if (!document.querySelector(`[data-chart-id="${chartId}"]`)) {
            instance.destroy();
            chartInstances.delete(chartId);
        }
    });
}

export function bootDashboardCharts() {
    refreshCharts();

    if (!window.__dashboardChartsListenersBound) {
        document.addEventListener("dashboard:charts-refresh", (event) => {
            refreshCharts(event.target ?? document);
        });

        document.addEventListener("livewire:navigated", () => {
            refreshCharts();
            cleanupCharts();
        });

        window.__dashboardChartsListenersBound = true;
    }

    if (mutationObserver) {
        return;
    }

    mutationObserver = new MutationObserver(() => {
        refreshCharts();
        cleanupCharts();
    });

    mutationObserver.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootDashboardCharts, {
        once: true,
    });
} else {
    bootDashboardCharts();
}
