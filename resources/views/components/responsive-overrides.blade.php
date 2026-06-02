<style>
    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    img,
    svg,
    video,
    canvas {
        max-width: 100%;
    }

    .row,
    [class*="col-"],
    .nexo-main,
    .nexo-card,
    .nexo-panel-card,
    .nexo-dashboard-panel,
    .nexo-leads-panel,
    .nexo-public-card,
    .nexo-settings,
    .nexo-settings-content,
    .nexo-beneficiary-card,
    .nexo-document-card,
    .nexo-client-form-shell,
    .nexo-subscription-lock-card,
    .nexo-login-card,
    .nexo-register-card {
        min-width: 0;
    }

    .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-inline: contain;
    }

    .table-responsive > .table {
        margin-bottom: 0;
        min-width: 720px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .dropdown-menu,
    .modal,
    .modal-dialog,
    .modal-content {
        max-width: calc(100vw - 24px);
    }

    .modal-dialog {
        margin-right: auto;
        margin-left: auto;
    }

    .modal-footer,
    .nexo-form-actions,
    .nexo-section-header,
    .nexo-dashboard-header,
    .nexo-dashboard-panel-header,
    .nexo-leads-header,
    .nexo-leads-panel-header,
    .nexo-client-form-header,
    .nexo-beneficiary-header,
    .nexo-document-header,
    .nexo-page-hero,
    .nexo-page-header {
        min-width: 0;
        flex-wrap: wrap;
    }

    .form-control,
    .form-select,
    textarea,
    select,
    input,
    button {
        max-width: 100%;
    }

    .btn,
    .nexo-btn-primary,
    .nexo-primary-btn,
    .nexo-secondary-btn,
    .nexo-warning-btn,
    .nexo-dashboard-public-link,
    .nexo-leads-new-btn,
    .nexo-open-btn,
    .nexo-dashboard-open,
    .nexo-review-btn {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .nexo-phone-actions,
    .nexo-table-user,
    .nexo-lead-user,
    .nexo-dashboard-lead,
    .nexo-beneficiary-title,
    .nexo-document-title {
        min-width: 0;
    }

    .nexo-phone-actions strong,
    .nexo-table-user strong,
    .nexo-table-user small,
    .nexo-lead-user strong,
    .nexo-lead-user small,
    .nexo-dashboard-lead strong,
    .nexo-dashboard-lead small,
    .nexo-user-name,
    .nexo-user-role {
        overflow-wrap: anywhere;
    }

    @media (max-width: 1100px) {
        .nexo-header-top {
            min-height: auto !important;
            padding: 18px 0 !important;
        }

        .nexo-header-row {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 16px !important;
        }

        .nexo-header-actions {
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            width: 100%;
        }

        .nexo-user-menu {
            min-width: 0 !important;
            width: auto;
        }

        .nexo-header-nav {
            height: auto !important;
        }

        .nexo-header-nav .nexo-header-container {
            min-height: 76px !important;
            align-items: center !important;
            overflow: hidden !important;
        }

        .nexo-nav-links {
            flex: 1 1 auto;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 0 8px 0 0;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        .nexo-nav-item {
            flex: 0 0 auto;
        }

        .nexo-public-card,
        .nexo-settings,
        .nexo-subscription-lock-card,
        .nexo-login-card,
        .nexo-register-card,
        .nexo-access-card {
            grid-template-columns: minmax(0, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        body {
            min-width: 0;
        }

        .nexo-header-container {
            padding-right: 14px !important;
            padding-left: 14px !important;
        }

        .nexo-logo,
        .nexo-logo-light {
            width: min(210px, 70vw) !important;
            height: auto !important;
            min-height: 0 !important;
        }

        .nexo-search {
            width: 100% !important;
            max-width: none !important;
            min-height: 52px !important;
            height: auto !important;
            padding: 0 14px !important;
        }

        .nexo-search input {
            font-size: 15px !important;
        }

        .nexo-header-actions {
            gap: 10px !important;
        }

        .nexo-user-menu {
            flex: 1 1 190px;
            padding-left: 0 !important;
            border-left: 0 !important;
        }

        .nexo-user-menu > button {
            width: 100%;
            max-width: 100%;
        }

        .nexo-avatar,
        .nexo-avatar-placeholder {
            width: 46px !important;
            height: 46px !important;
            flex-basis: 46px !important;
        }

        .nexo-user-text {
            min-width: 0;
        }

        .nexo-user-name {
            font-size: 15px !important;
        }

        .nexo-user-role {
            margin-top: 4px !important;
            font-size: 12px !important;
        }

        .nexo-header-nav .nexo-header-container {
            gap: 10px !important;
            padding-right: 14px !important;
        }

        .nexo-nav-links {
            gap: 12px !important;
        }

        .nexo-nav-item {
            min-height: 54px;
            font-size: 13px !important;
            gap: 7px !important;
        }

        .nexo-nav-item i {
            font-size: 18px !important;
        }

        .nexo-header-nav .nexo-btn-primary {
            flex: 0 0 auto;
            min-height: 40px !important;
            padding: 0 12px !important;
            font-size: 13px !important;
            gap: 7px !important;
        }

        .nexo-main {
            width: 100% !important;
            max-width: 100% !important;
            padding: 18px 12px 44px !important;
        }

        .nexo-dashboard-header,
        .nexo-leads-header,
        .nexo-section-header,
        .nexo-dashboard-panel-header,
        .nexo-leads-panel-header,
        .nexo-client-form-header,
        .nexo-beneficiary-header,
        .nexo-document-header {
            align-items: flex-start !important;
            flex-direction: column !important;
            gap: 12px !important;
        }

        .nexo-dashboard-header h1,
        .nexo-leads-header h1,
        .nexo-lead-create-hero h1,
        .nexo-pre-cadastro-hero h1,
        .nexo-page-hero h1,
        .nexo-client-form-header h1 {
            font-size: clamp(1.65rem, 8vw, 2.1rem) !important;
            line-height: 1.08 !important;
            letter-spacing: -0.035em !important;
        }

        .nexo-dashboard-grid,
        .nexo-dashboard-attention-grid,
        .nexo-leads-summary-grid,
        .nexo-page-summary-grid,
        .nexo-client-summary-grid,
        .nexo-stats-grid,
        .nexo-kpi-grid,
        .nexo-metrics-grid {
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 12px !important;
        }

        .nexo-dashboard-panel,
        .nexo-leads-panel,
        .nexo-panel-card,
        .nexo-card,
        .nexo-public-card,
        .nexo-settings-content,
        .nexo-beneficiary-card,
        .nexo-document-card {
            padding: 16px !important;
            border-radius: 18px !important;
        }

        .table-responsive {
            margin-right: -16px;
            margin-left: -16px;
            padding-right: 16px;
            padding-left: 16px;
        }

        .nexo-pagination {
            align-items: stretch;
            flex-direction: column;
            gap: 10px;
        }

        .nexo-page-numbers {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .modal-dialog {
            width: calc(100vw - 24px);
            margin: 12px auto;
        }

        .modal-footer > * {
            flex: 1 1 100%;
        }

        .nexo-login-page,
        .nexo-register-page,
        .nexo-subscription-lock-page,
        .nexo-access-page {
            min-height: 100svh !important;
            height: auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 12px !important;
            align-items: flex-start !important;
        }

        .nexo-login-card,
        .nexo-register-card,
        .nexo-subscription-lock-card,
        .nexo-access-card {
            width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow: visible !important;
            border-radius: 22px !important;
        }

        .nexo-login-brand,
        .nexo-register-brand,
        .nexo-subscription-lock-hero,
        .nexo-access-brand {
            padding: 24px !important;
        }

        .nexo-login-form-area,
        .nexo-register-form-area,
        .nexo-subscription-lock-content,
        .nexo-access-content {
            height: auto !important;
            min-height: 0 !important;
            padding: 24px !important;
            overflow: visible !important;
        }

        .nexo-form-scroll {
            overflow: visible !important;
            padding-right: 0 !important;
        }

        .nexo-login-brand h1,
        .nexo-register-brand h1,
        .nexo-subscription-lock-hero h1,
        .nexo-access-brand h1 {
            font-size: clamp(1.8rem, 9vw, 2.45rem) !important;
            letter-spacing: -0.04em !important;
        }
    }

    @media (max-width: 576px) {
        .nexo-header-actions {
            display: grid !important;
            grid-template-columns: repeat(3, 44px) minmax(0, 1fr);
            align-items: center;
        }

        .nexo-action-dropdown {
            min-width: 0;
        }

        .nexo-action-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 22px !important;
        }

        .nexo-header-nav .nexo-header-container {
            flex-direction: column;
            align-items: stretch !important;
            padding-top: 8px !important;
            padding-bottom: 10px !important;
        }

        .nexo-nav-links {
            width: 100%;
            min-height: 50px;
        }

        .nexo-header-nav .nexo-btn-primary {
            width: 100%;
        }

        .nexo-dashboard-public-link,
        .nexo-leads-new-btn,
        .nexo-form-actions .btn,
        .nexo-form-actions a,
        .nexo-form-actions button,
        .nexo-primary-btn,
        .nexo-secondary-btn,
        .nexo-warning-btn {
            width: 100% !important;
            justify-content: center !important;
        }

        .nexo-dashboard-metric,
        .nexo-dashboard-attention,
        .nexo-leads-summary-card,
        .nexo-page-summary-card {
            min-height: 86px !important;
            padding: 14px !important;
        }

        .nexo-dashboard-metric-icon,
        .nexo-dashboard-attention-icon,
        .nexo-leads-summary-icon {
            width: 44px !important;
            height: 44px !important;
            border-radius: 14px !important;
            font-size: 1.05rem !important;
        }

        .nexo-dashboard-metric-content strong,
        .nexo-dashboard-attention strong,
        .nexo-leads-summary-card strong {
            font-size: 1.55rem !important;
        }

        .nexo-floating-toast {
            top: 10px !important;
            right: 10px !important;
            left: 10px !important;
            width: auto !important;
            max-width: none !important;
            padding: 12px !important;
            gap: 10px !important;
        }

        .nexo-floating-toast-icon {
            width: 44px !important;
            height: 44px !important;
            flex-basis: 44px !important;
        }

        .nexo-floating-toast-close {
            width: 36px !important;
            height: 36px !important;
            flex-basis: 36px !important;
        }

        .nexo-login-brand,
        .nexo-register-brand,
        .nexo-subscription-lock-hero,
        .nexo-access-brand {
            padding: 20px !important;
        }

        .nexo-login-form-area,
        .nexo-register-form-area,
        .nexo-subscription-lock-content,
        .nexo-access-content {
            padding: 20px !important;
        }

        .nexo-plan-summary,
        .nexo-card-flags,
        .nexo-payment-commercial-box {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .nexo-card-number-wrapper .form-control {
            padding-right: 86px !important;
        }

        .nexo-detected-card {
            min-width: 66px !important;
            padding-right: 6px !important;
            padding-left: 6px !important;
        }
    }

    @media (max-width: 390px) {
        .nexo-header-actions {
            grid-template-columns: repeat(3, 38px) minmax(0, 1fr);
            gap: 8px !important;
        }

        .nexo-action-icon {
            width: 38px !important;
            height: 38px !important;
        }

        .nexo-avatar,
        .nexo-avatar-placeholder {
            width: 40px !important;
            height: 40px !important;
            flex-basis: 40px !important;
        }

        .nexo-user-chevron {
            display: none !important;
        }

        .nexo-dashboard-panel,
        .nexo-leads-panel,
        .nexo-panel-card,
        .nexo-card,
        .nexo-public-card,
        .nexo-settings-content,
        .nexo-beneficiary-card,
        .nexo-document-card {
            padding: 14px !important;
        }

        .table-responsive {
            margin-right: -14px;
            margin-left: -14px;
            padding-right: 14px;
            padding-left: 14px;
        }
    }
</style>
