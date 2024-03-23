import {defaultTheme} from '@vuepress/theme-default'
import {defineUserConfig} from 'vuepress/cli'
import {viteBundler} from '@vuepress/bundler-vite'
import {copyCodePlugin} from '@vuepress/plugin-copy-code'
import {gitPlugin} from '@vuepress/plugin-git'

// const isProd = process.env.NODE_ENV === 'production'

export default defineUserConfig({
    lang: 'en-US',
    base: '/opentelemetry-bundle/',

    title: 'OpenTelemetry Bundle',
    description: 'Traces, metrics, and logs instrumentation within your Symfony application',

    markdown: {
        code: {
            lineNumbers: false
        }
    },

    theme: defaultTheme({
        repo: 'https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle',
        docsRepo: 'https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle',
        docsDir: 'docs/src',
        lastUpdated: true,
        navbar: [
            {
                text: 'User Guide',
                children: [
                    '/user-guide/introduction.md',
                    '/user-guide/getting-started.md',
                    '/user-guide/configuration.md',
                    '/user-guide/troubleshooting.md',
                ],
            },
            {
                text: 'Instrumentation',
                children: [
                    '/instrumentation/traces.md',
                    '/instrumentation/metrics.md',
                    '/instrumentation/logs.md',
                ],
            },
        ],
    }),

    plugins: [
        copyCodePlugin(),
        gitPlugin(),
    ],

    bundler: viteBundler(),
})
