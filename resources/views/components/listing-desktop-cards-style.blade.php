@once
    <style>
        @media (min-width: 1024px) {
            .desktop-listing-scope .desktop-listing-grid {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 1.25rem !important;
                justify-items: stretch !important;
                align-items: stretch;
            }

            .desktop-listing-scope .desktop-listing-item {
                padding: 0 !important;
                width: 100% !important;
                max-width: none !important;
                min-height: 440px;
                border-radius: 1rem !important;
                border: 1px solid #dbe3ef !important;
                overflow: hidden !important;
                box-shadow: 0 12px 28px -20px rgba(15, 23, 42, 0.5) !important;
                transition: transform 220ms ease, box-shadow 220ms ease;
            }

            .desktop-listing-scope .desktop-listing-grid > * {
                width: 100% !important;
                min-width: 0 !important;
            }

            .desktop-listing-scope .desktop-listing-item:hover {
                transform: translateY(-6px);
                box-shadow: 0 22px 34px -22px rgba(15, 23, 42, 0.55) !important;
            }

            .desktop-listing-scope .desktop-item-media {
                height: 220px !important;
            }

            .desktop-listing-scope .desktop-item-body {
                padding: 1rem !important;
            }

            .desktop-listing-scope .desktop-listing-item h3 {
                font-size: 1.05rem !important;
                line-height: 1.35 !important;
                letter-spacing: -0.01em;
            }

            .desktop-listing-scope .desktop-listing-item .desktop-item-muted,
            .desktop-listing-scope .desktop-listing-item p {
                font-size: 0.92rem !important;
                line-height: 1.4;
                max-width: none;
            }

            .desktop-listing-scope .desktop-listing-item .desktop-item-price {
                font-size: 1.35rem !important;
                line-height: 1.1 !important;
                font-weight: 700;
            }

            .desktop-listing-scope .desktop-listing-item .desktop-item-btn {
                min-height: 44px;
                font-size: 0.93rem !important;
                font-weight: 600;
                padding: 0.65rem 0.95rem !important;
            }

            .desktop-listing-scope .desktop-listing-item .desktop-item-chip,
            .desktop-listing-scope .desktop-listing-item .desktop-item-meta,
            .desktop-listing-scope .desktop-listing-item .desktop-item-badge {
                font-size: 0.8rem !important;
            }
        }

        @media (min-width: 1280px) {
            .desktop-listing-scope .desktop-listing-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }

        @media (min-width: 1536px) {
            .desktop-listing-scope .desktop-listing-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }
    </style>
@endonce
