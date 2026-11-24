import os from 'node:os';
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

function getPrivateIpv4Address() {
    return Object.values(os.networkInterfaces())
        .flat()
        .find(({ address, family, internal }) =>
            family === 'IPv4'
            && !internal
            && (/^10\./.test(address)
                || /^192\.168\./.test(address)
                || /^172\.(1[6-9]|2\d|3[01])\./.test(address))
        )?.address;
}

function restartViteWhenLanAddressChanges(hmrHostOverride) {
    return {
        name: 'restart-vite-on-lan-address-change',
        configureServer(server) {
            if (hmrHostOverride) {
                return;
            }

            let currentAddress = getPrivateIpv4Address();
            const interval = setInterval(() => {
                const nextAddress = getPrivateIpv4Address();

                if (!nextAddress || nextAddress === currentAddress) {
                    return;
                }

                currentAddress = nextAddress;
                server.config.logger.info(`LAN IP changed to ${nextAddress}; restarting Vite.`);
                void server.restart().catch((error) => {
                    server.config.logger.error(`Failed to restart Vite after LAN IP change: ${error.message}`);
                });
            }, 5000);

            interval.unref();
            server.httpServer?.once('close', () => clearInterval(interval));
        },
    };
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), 'VITE_');
    const defaultHmrHost = getPrivateIpv4Address() || 'localhost';

    return {
        plugins: [
            restartViteWhenLanAddressChanges(env.VITE_HMR_HOST),
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: 5174,
            cors: true,
            hmr: {
                host: env.VITE_HMR_HOST || defaultHmrHost,
                port: 5174,
            },
        },
    };
});
