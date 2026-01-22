import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/tailwind.css",
                "resources/css/app.css",
                "resources/css/styles.css",
                "resources/js/app.js",
                "resources/js/javascript.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: "0.0.0.0", // importante: escuta em todas as interfaces
        port: 5173, // porta padrão do vite (pode mudar)
        strictPort: false, // se a porta estiver ocupada, pode usar outra (mude para true se quiser falhar)
        // // hmr: configura onde os clientes de HMR devem se conectar
        // hmr: {
        //     host: "192.168.1.16", // <-- substitua pelo IP da sua máquina na LAN.
        //     port: 5173,
        //     protocol: "ws", // 'ws' para http, 'wss' para https
        // },
        // cors: true,
        // watch: {
        //     ignored: ["**/storage/framework/views/**"],
        // },
    },
});
