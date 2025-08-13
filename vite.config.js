import { resolve } from 'path';
import { defineConfig } from 'vite';
import * as glob from 'glob';

export default defineConfig({
    server: {
        host: 'localhost',
        port: 3000,
        // hot: true,
        watch: {
            // que busque todos los archivos css dentro de la carpeta "media/css" y sus subdirectorios. Lo mismo con los archivos js.
            paths: glob.sync('media/css/**/*.css'),
            ignored: glob.sync('media/css/**/*.css')
        }
    }
});