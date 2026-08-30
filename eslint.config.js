import eslint from '@eslint/js';
import prettier from 'eslint-config-prettier/flat';
import globals from 'globals';

export default [
  {
    ignores: ['node_modules/**', 'vendor/**', 'public/build/**', 'dist/**'],
  },

  eslint.configs.recommended,

  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ZogographI18n: 'readonly',
        ZogographActionSummaryCard: 'readonly',
        ZogographCpuCoresCard: 'readonly',
        ZogographCpuFindingsCard: 'readonly',
        ZogographCpuGaugesCard: 'readonly',
        ZogographCpuLimitsCard: 'readonly',
        ZogographCpuProcessesCard: 'readonly',
        ZogographCpuQueueCard: 'readonly',
        ZogographCpuTimeCard: 'readonly',
        ZogographHostMemoryCard: 'readonly',
        ZogographHostMemoryGaugesCard: 'readonly',
        ZogographNetworkGaugesCard: 'readonly',
        ZogographOtherExtensionsCard: 'readonly',
        ZogographOverviewGaugesCard: 'readonly',
        ZogographOverviewVitalsCards: 'readonly',
        ZogographScanFactCards: 'readonly',
        ZogographTypeGrowthCard: 'readonly',
        ZogographUptimeCard: 'readonly',
      },
    },
  },

  // Keep last so Prettier controls formatting.
  prettier,
];
