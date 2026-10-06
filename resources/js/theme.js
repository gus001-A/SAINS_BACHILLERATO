import { theme as antdThemeAlgorithms } from 'ant-design-vue';
import esES from 'ant-design-vue/es/locale/es_ES';

/** Locale español para <a-config-provider :locale="antdLocale"> (calendarios, etc.). */
export const antdLocale = esES;

export const SAINS_PRIMARY = '#4f46e5';
export const SAINS_PRIMARY_SOFT = 'rgba(79, 70, 229, 0.10)';

/** Azul institucional SAINS (usado en las pantallas de autenticación). */
export const SAINS_BLUE = '#1d4ed8';

/**
 * Tema de Ant Design Vue para las pantallas públicas de autenticación
 * (login, registro, recuperar contraseña). Azul + dorado, acorde al logo.
 */
export const authTheme = {
    algorithm: antdThemeAlgorithms.defaultAlgorithm,
    token: {
        colorPrimary: SAINS_BLUE,
        colorInfo: SAINS_BLUE,
        colorLink: SAINS_BLUE,
        colorLinkHover: '#2563eb',
        colorError: '#dc2626',
        colorTextBase: '#1e293b',
        borderRadius: 12,
        borderRadiusLG: 14,
        borderRadiusSM: 8,
        fontFamily: "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
        fontSize: 14,
        controlHeight: 44,
        wireframe: false,
    },
    components: {
        Input: { activeShadow: '0 0 0 3px rgba(29, 78, 216, 0.12)' },
        Button: { primaryShadow: '0 10px 24px -10px rgba(29, 78, 216, 0.55)', fontWeight: 600 },
        Checkbox: { colorPrimary: SAINS_BLUE },
    },
};

/**
 * Tema global de Ant Design Vue para los paneles admin / estudiante.
 * Diseño: SaaS moderno, tarjetas suaves, tipografía Inter, densidad cómoda.
 */
export const antdTheme = {
    algorithm: antdThemeAlgorithms.defaultAlgorithm,
    token: {
        colorPrimary: SAINS_PRIMARY,
        colorInfo: SAINS_PRIMARY,
        colorLink: SAINS_PRIMARY,
        colorLinkHover: '#6366f1',
        colorSuccess: '#16a34a',
        colorWarning: '#d97706',
        colorError: '#dc2626',
        colorTextBase: '#0f172a',
        colorBgLayout: '#f4f6fb',
        borderRadius: 10,
        borderRadiusLG: 14,
        borderRadiusSM: 8,
        fontFamily: "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
        fontSize: 14,
        controlHeight: 38,
        lineWidth: 1,
        wireframe: false,
        boxShadow: '0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px -12px rgba(15, 23, 42, 0.12)',
        boxShadowSecondary: '0 6px 16px -8px rgba(15, 23, 42, 0.12), 0 9px 28px -14px rgba(15, 23, 42, 0.1)',
    },
    components: {
        Layout: {
            headerBg: '#ffffff',
            headerHeight: 62,
            bodyBg: '#f4f6fb',
            footerBg: 'transparent',
        },
        Menu: {
            itemSelectedBg: SAINS_PRIMARY_SOFT,
            itemSelectedColor: SAINS_PRIMARY,
            itemHoverColor: SAINS_PRIMARY,
            horizontalItemSelectedColor: SAINS_PRIMARY,
            itemBorderRadius: 8,
            activeBarBorderWidth: 0,
        },
        Card: {
            borderRadiusLG: 16,
            paddingLG: 22,
            headerFontSize: 15,
            colorBorderSecondary: '#eef1f6',
        },
        Table: {
            headerBg: '#fafbfd',
            headerColor: '#475569',
            headerSplitColor: 'transparent',
            rowHoverBg: '#f7f8fc',
            borderColor: '#eef1f6',
            cellPaddingBlock: 12,
        },
        Statistic: {
            contentFontSize: 26,
        },
        Modal: {
            borderRadiusLG: 18,
            titleFontSize: 17,
        },
        Button: {
            controlHeight: 38,
            primaryShadow: '0 6px 16px -8px rgba(79, 70, 229, 0.5)',
            fontWeight: 500,
        },
        Segmented: {
            itemSelectedBg: '#ffffff',
        },
        Tag: {
            borderRadiusSM: 6,
        },
        Input: {
            activeShadow: '0 0 0 3px rgba(79, 70, 229, 0.12)',
        },
        Select: {
            optionSelectedBg: SAINS_PRIMARY_SOFT,
        },
        PageHeader: {
            paddingInline: 0,
        },
    },
};
