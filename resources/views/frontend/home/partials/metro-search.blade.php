@php
    $saleMin = isset($sale_min) && $sale_min !== null ? (int) $sale_min : 0;
    $saleMax = max((int) ($sale_max ?? 0), 1000000);
    $rentMin = isset($rent_min) && $rent_min !== null ? (int) $rent_min : 0;
    $rentMax = max((int) ($rent_max ?? 0), 10000);
    $primary = '#' . ltrim($basicInfo->primary_color ?? 'F57F4B', '#');
    $secondary = '#' . ltrim($basicInfo->secondary_color ?? '255056', '#');
@endphp

<style>
    /*
     * MetroHCO meta search behaviour, styled to belong to Estaty's hero.
     * Desktop is one continuous search surface; tablet/mobile use deliberate
     * grid areas so controls never wrap into uneven or misaligned rows.
     */
    .home-banner.home-banner-1,
    .home-banner.home-banner-2,
    .home-banner.home-banner-3 {
        overflow: visible;
    }

    .metro-msb-host {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative;
        z-index: 30;
        width: 100%;
    }

    .home-banner-2 .metro-msb-host .metro-search-shell,
    .home-banner-3 .metro-msb-host .metro-search-shell {
        margin-left: auto;
        margin-right: auto;
    }

    .home-banner.home-banner-1 .col-xxl-12 {
        position: relative;
        z-index: 8;
    }

    .metro-search-shell {
        --metro-primary: var(--color-primary, {{ $primary }});
        --metro-secondary: var(--color-secondary, {{ $secondary }});
        --metro-dark: var(--color-dark, #1f1f1f);
        --metro-medium: var(--color-medium, #6f7478);
        --metro-border: var(--border-color, #e7e7e7);
        position: relative;
        z-index: 20;
        display: grid !important;
        visibility: visible !important;
        opacity: 1 !important;
        grid-template-columns: minmax(150px, 1.05fr) minmax(165px, 1.12fr) minmax(165px, 1.08fr) minmax(165px, 1.1fr) 112px 122px;
        grid-template-areas: "borough neighborhood price property more search";
        align-items: stretch;
        width: 100%;
        max-width: 1220px;
        min-height: 82px;
        padding: 0;
        margin: 88px 0 0;
        background: var(--color-white, #fff);
        border: 2px solid var(--metro-secondary);
        /* The top-left corner belongs to the attached Buy/Rent tab. Keeping
         * the shell square here makes the outer teal boundary one continuous
         * line, matching MetroHCO old. */
        border-radius: 0 10px 10px 10px;
        box-shadow: 0 20px 55px rgba(31, 31, 31, .13);
        isolation: isolate;
    }

    .metro-search-shell * {
        box-sizing: border-box;
    }

    .metro-search-shell button,
    .metro-search-shell input {
        font-family: inherit;
    }

    /* Do not let legacy Estaty/AOS/filter-form rules hide the Metro controls. */
    .metro-search-shell > .metro-mode,
    .metro-search-shell > .metro-control,
    .metro-search-shell > .metro-search-btn {
        visibility: visible !important;
        opacity: 1 !important;
    }


    .metro-mode {
        position: absolute;
        left: -1px;
        top: -42px;
        z-index: 4;
        display: inline-flex !important;
        align-items: flex-end;
        gap: 0;
        min-width: 0;
        margin: 0;
        padding: 0;
        background: transparent;
        border-radius: 0;
        overflow: visible;
    }

    /* Exact old-MetroHCO left boundary: the first tab and the body share
     * the same x-coordinate, with no rounded-shell gap at their junction. */
    .metro-mode > .metro-mode-btn:first-child {
        border-top-left-radius: 10px;
    }

    .metro-mode > .metro-mode-btn:first-child.active {
        box-shadow: none;
    }

    .metro-mode-btn {
        position: relative;
        min-width: 78px;
        min-height: 42px;
        padding: 0 17px;
        border: 2px solid transparent;
        border-bottom: 0;
        border-radius: 10px 10px 0 0;
        background: #f1e4de;
        color: var(--metro-dark);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .01em;
        transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
        box-shadow: none;
    }

    .metro-mode-btn + .metro-mode-btn {
        margin-left: -2px;
    }

    .metro-mode-btn + .metro-mode-btn::before {
        content: none;
    }

    .metro-mode-btn.active {
        color: var(--metro-dark);
        background: #fff;
        border-color: var(--metro-secondary);
        border-bottom-color: #fff;
        transform: translateY(2px);
        z-index: 2;
    }

    .metro-mode-btn:not(.active):hover {
        background: #ebddd6;
    }

    .metro-mode-btn.active::before,
    .metro-mode-btn.active + .metro-mode-btn::before {
        opacity: 0;
    }

    .metro-control {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative;
        min-width: 0;
        background: #fff;
    }

    .metro-control[data-control="borough"] { grid-area: borough; }
    .metro-control[data-control="borough"]::before { content: none; }

    /* Desktop bottom-left corner: keep the first field background inside the
     * shell's rounded border so the outer corner stays clean and continuous. */
    @media (min-width: 1200px) {
        .metro-control[data-control="borough"] {
            border-bottom-left-radius: 8px;
            overflow: hidden;
        }

        .metro-control[data-control="borough"] .metro-field {
            border-bottom-left-radius: 8px;
        }
    }
    .metro-control[data-control="neighborhood"] { grid-area: neighborhood; }
    .metro-control[data-control="price"] { grid-area: price; }
    .metro-control[data-control="property"] { grid-area: property; }
    .metro-control[data-control="more"] { grid-area: more; }

    .metro-control::before {
        content: "";
        position: absolute;
        left: 0;
        top: 18px;
        bottom: 18px;
        width: 1px;
        background: var(--metro-border);
        z-index: 2;
    }

    .metro-field {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 78px;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        gap: 8px;
        padding: 14px 34px 14px 18px;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: var(--metro-dark);
        text-align: left;
        transition: background-color .18s ease;
        overflow: hidden;
    }

    .metro-field:hover,
    .metro-control.open .metro-field {
        background: rgba(var(--color-primary-rgb, 245, 127, 75), .055);
    }

    .metro-field::after {
        content: "";
        position: absolute;
        right: 17px;
        top: 50%;
        width: 7px;
        height: 7px;
        margin-top: -5px;
        border-right: 1.5px solid var(--metro-primary);
        border-bottom: 1.5px solid var(--metro-primary);
        transform: rotate(45deg);
        transition: transform .18s ease, margin-top .18s ease;
    }

    .metro-control.open .metro-field::after {
        margin-top: -1px;
        transform: rotate(225deg);
    }

    .metro-field-label {
        display: block;
        max-width: 100%;
        color: var(--metro-medium);
        font-size: 10px;
        line-height: 1;
        font-weight: 600;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .metro-field-value {
        display: block;
        width: 100%;
        color: var(--metro-dark);
        font-size: 13px;
        line-height: 1.2;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .metro-field-value::after {
        content: none;
    }

    .metro-search-btn {
        grid-area: search;
        align-self: stretch;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 0;
        min-height: 62px;
        margin: 8px;
        padding: 0 16px;
        border: 0;
        border-radius: 7px;
        background: var(--metro-secondary);
        color: #fff;
        font-size: 11px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: .065em;
        box-shadow: 0 9px 20px rgba(31, 31, 31, .12);
        transition: transform .18s ease, background-color .18s ease, box-shadow .18s ease;
    }

    .metro-search-btn svg {
        width: 15px;
        height: 15px;
        flex: 0 0 15px;
    }

    .metro-search-btn:hover {
        transform: translateY(-1px);
        background: var(--metro-primary);
        box-shadow: 0 12px 22px rgba(31, 31, 31, .16);
    }

    .metro-search-btn:focus-visible,
    .metro-mode-btn:focus-visible,
    .metro-field:focus-visible,
    .metro-option:focus-visible,
    .metro-preset:focus-visible {
        outline: 2px solid var(--metro-primary);
        outline-offset: -2px;
    }

    .metro-dropdown {
        /* JS pins every open panel to the viewport. This deliberately avoids
         * clipping from hero/swiper overflow and transformed stacking contexts. */
        position: fixed;
        top: 0;
        left: 0;
        right: auto;
        bottom: auto;
        z-index: 2147483001;
        display: none;
        width: min(410px, calc(100vw - 24px));
        max-width: calc(100vw - 24px);
        max-height: min(490px, calc(100dvh - 24px));
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--metro-border);
        border-radius: 10px;
        box-shadow: 0 24px 70px rgba(31, 31, 31, .24);
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-gutter: stable;
        touch-action: pan-y;
    }

    .metro-dropdown::-webkit-scrollbar {
        width: 7px;
    }

    .metro-dropdown::-webkit-scrollbar-thumb {
        border: 2px solid transparent;
        border-radius: 999px;
        background: rgba(31, 31, 31, .28);
        background-clip: padding-box;
    }

    .metro-dropdown::-webkit-scrollbar-track {
        background: transparent;
    }

    .metro-msb-backdrop {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: none;
        background: transparent;
    }

    .metro-msb-backdrop.show {
        display: block;
    }

    .metro-control.open .metro-dropdown,
    .metro-dropdown.metro-portal-open {
        display: block;
        animation: metroDropIn .16s ease both;
    }

    @keyframes metroDropIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .metro-dropdown.wide {
        width: min(530px, 92vw);
    }

    .metro-dropdown.right {
        /* Alignment is viewport-clamped in JS. */
    }

    .metro-drop-title {
        margin: 0 0 14px;
        color: var(--metro-dark);
        font-size: 13px;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: none;
    }

    .metro-drop-search {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        margin: 0 0 14px;
        border: 1px solid var(--metro-border);
        border-radius: 7px;
        outline: none;
        color: var(--metro-dark);
        background: #fff;
        font-size: 12px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .metro-drop-search::placeholder {
        color: #9a9da0;
    }

    .metro-drop-search:focus {
        border-color: var(--metro-primary);
        box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 245, 127, 75), .10);
    }

    .metro-option-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .metro-option {
        min-width: 0;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid var(--metro-border);
        border-radius: 7px;
        background: #fff;
        color: var(--metro-dark);
        font-size: 12px;
        line-height: 1.25;
        font-weight: 500;
        text-align: left;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }

    .metro-option:hover,
    .metro-option.active {
        border-color: var(--metro-primary);
        background: rgba(var(--color-primary-rgb, 245, 127, 75), .075);
        color: var(--metro-primary);
    }

    .metro-group-title {
        grid-column: 1 / -1;
        margin: 8px 0 1px;
        color: var(--metro-medium);
        font-size: 10px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .metro-empty {
        grid-column: 1 / -1;
        padding: 8px 2px;
        color: var(--metro-medium);
        font-size: 12px;
    }

    .metro-price-values {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 12px;
        margin: 4px 0 18px;
    }

    .metro-price-values > span {
        color: var(--metro-medium);
    }

    .metro-price-box {
        padding: 11px 12px;
        border: 1px solid var(--metro-border);
        border-radius: 7px;
        background: #fff;
    }

    .metro-price-box small {
        display: block;
        color: var(--metro-medium);
        font-size: 9px;
        line-height: 1;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .metro-price-box strong {
        display: block;
        margin-top: 5px;
        color: var(--metro-dark);
        font-size: 14px;
        line-height: 1;
        font-weight: 700;
    }

    .metro-range-wrap {
        position: relative;
        height: 34px;
        margin: 7px 5px 15px;
    }

    .metro-range-track,
    .metro-range-fill {
        position: absolute;
        top: 15px;
        height: 3px;
        border-radius: 3px;
    }

    .metro-range-track {
        left: 0;
        right: 0;
        background: #e8e8e8;
    }

    .metro-range-fill {
        background: var(--metro-primary);
    }

    .metro-range {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 34px;
        margin: 0;
        background: transparent;
        pointer-events: none;
        appearance: none;
        -webkit-appearance: none;
    }

    .metro-range::-webkit-slider-thumb {
        width: 18px;
        height: 18px;
        border: 4px solid var(--metro-primary);
        border-radius: 50%;
        appearance: none;
        -webkit-appearance: none;
        background: #fff;
        box-shadow: 0 2px 7px rgba(0, 0, 0, .14);
        cursor: pointer;
        pointer-events: auto;
    }

    .metro-range::-moz-range-thumb {
        width: 10px;
        height: 10px;
        border: 4px solid var(--metro-primary);
        border-radius: 50%;
        background: #fff;
        cursor: pointer;
        pointer-events: auto;
    }

    .metro-presets {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .metro-preset {
        min-height: 37px;
        padding: 8px 10px;
        border: 1px solid var(--metro-border);
        border-radius: 7px;
        background: #fff;
        color: var(--metro-dark);
        font-size: 11px;
        line-height: 1.2;
        text-align: left;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }

    .metro-preset:hover {
        border-color: var(--metro-primary);
        background: rgba(var(--color-primary-rgb, 245, 127, 75), .05);
        color: var(--metro-primary);
    }

    .metro-more-section {
        padding: 12px 0;
        border-bottom: 1px solid var(--metro-border);
    }

    .metro-more-section:first-of-type {
        padding-top: 2px;
    }

    .metro-more-section:last-child {
        padding-bottom: 2px;
        border-bottom: 0;
    }

    .metro-more-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .metro-more-row > span:first-child {
        color: var(--metro-dark);
        font-size: 12px;
        font-weight: 600;
    }

    .metro-qty {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .metro-qty button {
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid var(--metro-border);
        border-radius: 50%;
        background: #fff;
        color: var(--metro-dark);
        font-size: 17px;
        line-height: 28px;
        transition: border-color .15s ease, color .15s ease;
    }

    .metro-qty button:hover {
        border-color: var(--metro-primary);
        color: var(--metro-primary);
    }

    .metro-qty strong {
        min-width: 20px;
        color: var(--metro-dark);
        font-size: 12px;
        font-weight: 600;
        text-align: center;
    }

    .metro-pets {
        display: flex;
        gap: 6px;
    }

    .metro-pets button {
        min-height: 31px;
        padding: 6px 10px;
        border: 1px solid var(--metro-border);
        border-radius: 7px;
        background: #fff;
        color: var(--metro-dark);
        font-size: 10px;
        font-weight: 700;
    }

    .metro-pets button.active {
        border-color: var(--metro-primary);
        background: rgba(var(--color-primary-rgb, 245, 127, 75), .075);
        color: var(--metro-primary);
    }

    .metro-amenities {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px 12px;
        margin-top: 11px;
    }

    .metro-check {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
        color: var(--metro-dark);
        font-size: 11px;
        line-height: 1.35;
    }

    .metro-check input {
        flex: 0 0 auto;
        margin-top: 2px;
        accent-color: var(--metro-primary);
    }

    /* Laptop / small desktop: intentional two-row layout. */
    @media (max-width: 1199px) {
        .metro-search-shell {
            max-width: 930px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-areas:
                "borough neighborhood price"
                "property more search";
            gap: 8px;
            padding: 8px;
            margin-top: 38px;
            border-width: 2px;
            border-radius: 0 10px 10px 10px;
            background: rgba(255, 255, 255, .98);
        }

        .metro-mode {
            left: -1px;
            top: -38px;
        }

        .metro-control,
        .metro-search-btn {
            min-height: 66px;
            border: 1px solid var(--metro-border);
            border-radius: 7px;
            overflow: visible;
        }

        .metro-mode-btn {
            min-height: 38px;
            min-width: 74px;
            padding: 0 15px;
            border-width: 2px;
            border-bottom: 0;
            border-radius: 9px 9px 0 0;
        }

        .metro-field {
            min-height: 64px;
        }

        .metro-control::before {
            content: none;
        }

        .metro-search-btn {
            margin: 0;
        }

        .metro-control[data-control="property"] .metro-dropdown {
            left: 0;
            right: auto;
        }
    }

    /* Tablet: two balanced columns. */
    @media (max-width: 767px) {
        .metro-search-shell {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-areas:
                "mode mode"
                "borough neighborhood"
                "price property"
                "more search";
            gap: 7px;
            width: 100%;
            max-width: 100%;
            padding: 7px;
            margin-top: 0;
            border-radius: 10px;
            box-shadow: 0 14px 38px rgba(31, 31, 31, .12);
        }

        .metro-mode,
        .metro-control,
        .metro-search-btn {
            min-height: 60px;
        }

        .metro-mode {
            position: relative;
            top: auto;
            left: auto;
            display: grid !important;
            grid-area: mode;
            grid-template-columns: 1fr 1fr;
            align-items: stretch;
            border: 1px solid var(--metro-border);
            border-radius: 10px;
            overflow: hidden;
            background: rgba(var(--color-primary-rgb, 245, 127, 75), .09);
        }

        .metro-mode-btn,
        .metro-field {
            min-height: 58px;
        }

        .metro-mode-btn {
            min-width: 0;
            padding: 0 12px;
            border: 0;
            border-radius: 0;
            background: transparent;
            font-size: 12px;
            letter-spacing: .04em;
            transform: none;
        }

        .metro-mode-btn.active {
            color: #fff;
            background: var(--metro-secondary);
            border: 0;
            transform: none;
            box-shadow: none;
        }

        .metro-field {
            gap: 6px;
            padding: 11px 31px 11px 13px;
        }

        .metro-field::after {
            right: 13px;
        }

        .metro-field-label {
            font-size: 9px;
        }

        .metro-field-value {
            font-size: 12px;
        }

        .metro-dropdown,
        .metro-dropdown.wide,
        .metro-dropdown.right {
            position: fixed;
            width: calc(100vw - 20px);
            max-width: calc(100vw - 20px);
            max-height: min(76dvh, 580px);
            padding: 18px 16px;
            border-radius: 14px;
            z-index: 2147483001;
        }

        .metro-msb-backdrop.show {
            background: rgba(20, 24, 25, .24);
            backdrop-filter: blur(1.5px);
        }

        body.metro-msb-dropdown-open {
            overflow: hidden !important;
            touch-action: none;
        }

        body.metro-msb-dropdown-open .metro-dropdown {
            touch-action: pan-y;
        }

        .metro-option-grid,
        .metro-presets,
        .metro-amenities {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Phone: simple single-column controls, with the toggle remaining horizontal. */
    @media (max-width: 520px) {
        .metro-search-shell {
            grid-template-columns: 1fr;
            grid-template-areas:
                "mode"
                "borough"
                "neighborhood"
                "price"
                "property"
                "more"
                "search";
        }

        .metro-mode,
        .metro-control,
        .metro-search-btn {
            width: 100%;
        }

        .metro-search-btn {
            min-height: 54px;
        }

        .metro-option-grid,
        .metro-presets,
        .metro-amenities {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="metro-msb-backdrop" id="metroMsbBackdrop" aria-hidden="true"></div>

<form id="metroSearchForm" class="metro-search-shell" action="{{ route('frontend.properties') }}" method="get" autocomplete="off">
    <input type="hidden" name="purpose" id="metroPurpose" value="sale">
    <input type="hidden" name="min" id="metroMinInput" value="0">
    <input type="hidden" name="max" id="metroMaxInput" value="{{ $saleMax }}">
    <input type="hidden" name="beds" id="metroBedsInput" value="">
    <input type="hidden" name="baths" id="metroBathsInput" value="">
    <input type="hidden" name="adults" id="metroAdultsInput" value="">
    <input type="hidden" name="children" id="metroChildrenInput" value="">
    <input type="hidden" name="infants" id="metroInfantsInput" value="">
    <input type="hidden" name="pets_allowed" id="metroPetsInput" value="">
    <div id="metroDynamicInputs"></div>

    <div class="metro-mode" aria-label="Property purpose">
        <button type="button" class="metro-mode-btn active" data-purpose="sale">Buy</button>
        <button type="button" class="metro-mode-btn" data-purpose="rent">Rent</button>
    </div>

    <div class="metro-control" data-control="borough">
        <button type="button" class="metro-field" data-toggle="borough">
            <span class="metro-field-label">Borough / NYS</span><span class="metro-field-value" id="metroBoroughValue">NYS / BOROUGH</span>
        </button>
        <div class="metro-dropdown">
            <div class="metro-drop-title">Choose Borough</div>
            <input class="metro-drop-search" type="search" placeholder="Search your locations" data-search="borough">
            <div class="metro-option-grid" id="metroBoroughGrid">
                @foreach (['Manhattan', 'Brooklyn', 'Queens', 'Staten Island', 'Bronx', 'New York State'] as $borough)
                    <button type="button" class="metro-option" data-borough="{{ $borough }}">{{ $borough }}</button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="metro-control" data-control="neighborhood">
        <button type="button" class="metro-field" data-toggle="neighborhood">
            <span class="metro-field-label">Neighbourhood</span><span class="metro-field-value" id="metroNeighborhoodValue">NEIGHBORHOOD</span>
        </button>
        <div class="metro-dropdown wide">
            <div class="metro-drop-title">Choose Neighborhood</div>
            <input class="metro-drop-search" type="search" placeholder="Search neighborhoods" data-search="neighborhood">
            <div class="metro-option-grid" id="metroNeighborhoodGrid"><div class="metro-empty">Loading neighbourhoods…</div></div>
        </div>
    </div>

    <div class="metro-control" data-control="price">
        <button type="button" class="metro-field" data-toggle="price">
            <span class="metro-field-label">Pricing</span><span class="metro-field-value" id="metroPriceValue">MIN TO MAX</span>
        </button>
        <div class="metro-dropdown wide">
            <div class="metro-drop-title">Choose Price Range</div>
            <div class="metro-price-values">
                <div class="metro-price-box"><small>Min price</small><strong id="metroMinDisplay">$0</strong></div>
                <span>—</span>
                <div class="metro-price-box"><small>Max price</small><strong id="metroMaxDisplay">MAX</strong></div>
            </div>
            <div class="metro-range-wrap">
                <div class="metro-range-track"></div><div class="metro-range-fill" id="metroRangeFill"></div>
                <input class="metro-range" id="metroMinRange" type="range" min="0" max="{{ $saleMax }}" step="25000" value="0">
                <input class="metro-range" id="metroMaxRange" type="range" min="0" max="{{ $saleMax }}" step="25000" value="{{ $saleMax }}">
            </div>
            <div class="metro-presets" id="metroPresets"></div>
        </div>
    </div>

    <div class="metro-control" data-control="property">
        <button type="button" class="metro-field" data-toggle="property">
            <span class="metro-field-label">Property Type</span><span class="metro-field-value" id="metroPropertyValue">PROPERTY TYPE</span>
        </button>
        <div class="metro-dropdown wide right">
            <div class="metro-drop-title">Choose Property Type</div>
            <input class="metro-drop-search" type="search" placeholder="Search property types" data-search="property">
            <div class="metro-option-grid" id="metroPropertyGrid">
                @foreach ($all_proeprty_categories->groupBy('type') as $groupType => $groupCategories)
                    <div class="metro-group-title">{{ ucfirst($groupType) }}</div>
                    @foreach ($groupCategories as $category)
                        @if ($category->categoryContent)
                            <button type="button" class="metro-option" data-category-id="{{ $category->id }}">{{ $category->categoryContent->name }}</button>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <div class="metro-control" data-control="more">
        <button type="button" class="metro-field" data-toggle="more">
            <span class="metro-field-label">More</span><span class="metro-field-value" id="metroMoreValue">MORE</span>
        </button>
        <div class="metro-dropdown wide right">
            <div class="metro-drop-title">More Filters</div>
            @foreach ([['Bathrooms','baths'],['Bedrooms','beds'],['Adults','adults'],['Children','children'],['Infants','infants']] as [$label,$key])
                <div class="metro-more-section"><div class="metro-more-row"><span>{{ $label }}</span><div class="metro-qty" data-qty="{{ $key }}"><button type="button" data-action="minus">−</button><strong data-count>0</strong><button type="button" data-action="plus">+</button></div></div></div>
            @endforeach
            <div class="metro-more-section"><div class="metro-more-row"><span>Pet(s)?</span><div class="metro-pets"><button type="button" class="active" data-pets="">ANY</button><button type="button" data-pets="1">YES</button><button type="button" data-pets="0">NO</button></div></div></div>
            <div class="metro-more-section">
                <div class="metro-more-row"><span>Amenities</span></div>
                <div class="metro-amenities">
                    @foreach ($amenities as $amenity)
                        @if ($amenity->amenityContent)
                            <label class="metro-check"><input type="checkbox" value="{{ $amenity->id }}" data-amenity> <span>{{ $amenity->amenityContent->name }}</span></label>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="metro-search-btn" aria-label="Search properties">
        <span>SEARCH</span>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"></circle>
            <path d="M16 16L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
        </svg>
    </button>
</form>

<script type="module">
    import { ManhattanData } from "{{ asset('assets/js/metrohco/coordinates/manhattanData.js') }}";
    import { BrooklynData } from "{{ asset('assets/js/metrohco/coordinates/brooklynData.js') }}";
    import { QueensData } from "{{ asset('assets/js/metrohco/coordinates/queensData.js') }}";
    import { StatenIslandData } from "{{ asset('assets/js/metrohco/coordinates/statenIslandData.js') }}";
    import { BronxData } from "{{ asset('assets/js/metrohco/coordinates/bronxData.js') }}";
    import { NewyorkStates } from "{{ asset('assets/js/metrohco/coordinates/nyStateData.js') }}";

    const form = document.getElementById('metroSearchForm');
    if (form) {

    const currency = @json($basicInfo->base_currency_symbol ?? '$');
    const limits = {
        sale: { min: 0, max: {{ $saleMax }}, step: 25000 },
        rent: { min: 0, max: {{ $rentMax }}, step: 500 }
    };
    const presets = {
        sale: [[100000,175000],[175001,250000],[250001,350000],[350001,450000],[450001,600000],[600001,750000],[750001,900000],[900001,1000000]],
        rent: [[500,1000],[1000,1500],[1500,2000],[2000,2500],[2500,3000],[3000,4000],[4000,5000],[5000,10000]]
    };
    const neighborhoodData = {
        'Manhattan': ManhattanData.flatMap(x => x.neighborhoods || []),
        'Brooklyn': BrooklynData.flatMap(x => x.neighborhoods || []),
        'Queens': QueensData.flatMap(x => x.neighborhoods || []),
        'Staten Island': StatenIslandData.flatMap(x => x.neighborhoods || []),
        'Bronx': BronxData.flatMap(x => x.neighborhoods || []),
        'New York State': NewyorkStates.flatMap(x => x.neighborhoods || [])
    };

    const state = { purpose:'sale', boroughs:[], neighborhoods:[], categoryIds:[], beds:0, baths:0, adults:0, children:0, infants:0, pets:'', amenities:[] };
    const controls = [...form.querySelectorAll('.metro-control')];
    const backdrop = document.getElementById('metroMsbBackdrop');
    // Move the overlay to <body> immediately so transformed/overflow-hidden hero
    // ancestors cannot create a containing block around this fixed layer.
    if (backdrop && backdrop.parentElement !== document.body) document.body.appendChild(backdrop);

    let activeControl = null;
    let positionFrame = null;

    const isCompactViewport = () => window.matchMedia('(max-width: 767px)').matches;

    const getDropdown = control => control?.__metroDropdown || control?.querySelector('.metro-dropdown') || null;

    const portalDropdown = control => {
        const dropdown = getDropdown(control);
        if (!dropdown || dropdown.parentElement === document.body) return dropdown;
        const placeholder = document.createComment('metro-dropdown-placeholder');
        dropdown.parentNode.insertBefore(placeholder, dropdown);
        control.__metroDropdown = dropdown;
        control.__metroDropdownPlaceholder = placeholder;
        document.body.appendChild(dropdown);
        dropdown.classList.add('metro-portal-open');
        return dropdown;
    };

    const restoreDropdown = control => {
        const dropdown = control?.__metroDropdown;
        const placeholder = control?.__metroDropdownPlaceholder;
        if (!dropdown) return;
        dropdown.classList.remove('metro-portal-open');
        resetDropdownPosition(dropdown);
        if (placeholder?.parentNode) {
            placeholder.parentNode.insertBefore(dropdown, placeholder);
            placeholder.remove();
        }
        delete control.__metroDropdown;
        delete control.__metroDropdownPlaceholder;
    };

    const resetDropdownPosition = dropdown => {
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.right = '';
        dropdown.style.bottom = '';
        dropdown.style.width = '';
        dropdown.style.maxWidth = '';
        dropdown.style.maxHeight = '';
    };

    const syncDropdownChrome = () => {
        const hasOpen = controls.some(c => c.classList.contains('open'));
        backdrop?.classList.toggle('show', hasOpen);
        backdrop?.setAttribute('aria-hidden', hasOpen ? 'false' : 'true');
        document.body.classList.toggle('metro-msb-dropdown-open', hasOpen && isCompactViewport());
    };

    const closeAll = except => {
        controls.forEach(control => {
            if (control === except) return;
            control.classList.remove('open');
            control.querySelector('[data-toggle]')?.setAttribute('aria-expanded', 'false');
            restoreDropdown(control);
        });
        activeControl = except && except.classList.contains('open') ? except : null;
        syncDropdownChrome();
    };

    const positionActiveDropdown = () => {
        if (!activeControl || !activeControl.classList.contains('open')) return;
        const trigger = activeControl.querySelector('[data-toggle]');
        const dropdown = getDropdown(activeControl);
        if (!trigger || !dropdown) return;

        const vw = document.documentElement.clientWidth;
        const vh = window.innerHeight || document.documentElement.clientHeight;
        const edge = isCompactViewport() ? 10 : 12;
        const gap = isCompactViewport() ? 10 : 9;

        dropdown.style.right = 'auto';
        if (isCompactViewport()) {
            const width = Math.max(0, vw - edge * 2);
            const maxHeight = Math.max(180, Math.min(vh - edge * 2, Math.round(vh * .76), 580));
            dropdown.style.width = `${width}px`;
            dropdown.style.maxWidth = `${width}px`;
            dropdown.style.maxHeight = `${maxHeight}px`;
            dropdown.style.left = `${edge}px`;
            dropdown.style.top = 'auto';
            dropdown.style.bottom = `${edge}px`;
            return;
        }

        const rect = trigger.getBoundingClientRect();
        const desiredWidth = dropdown.classList.contains('wide') ? 530 : 410;
        const width = Math.min(desiredWidth, Math.max(260, vw - edge * 2));
        let left = dropdown.classList.contains('right') ? rect.right - width : rect.left;
        left = Math.max(edge, Math.min(left, vw - width - edge));

        const below = Math.max(0, vh - rect.bottom - gap - edge);
        const above = Math.max(0, rect.top - gap - edge);
        const preferred = Math.min(490, Math.round(vh * .66));
        const openAbove = below < Math.min(300, preferred) && above > below;
        const available = openAbove ? above : below;
        const maxHeight = Math.max(140, Math.min(preferred, available || preferred));

        dropdown.style.width = `${width}px`;
        dropdown.style.maxWidth = `${width}px`;
        dropdown.style.maxHeight = `${maxHeight}px`;
        dropdown.style.left = `${left}px`;
        dropdown.style.bottom = 'auto';

        // Measure after display:block so an above-opening panel hugs the trigger
        // instead of leaving a gap when its content is shorter than maxHeight.
        const actualHeight = Math.min(dropdown.scrollHeight, maxHeight);
        const top = openAbove
            ? Math.max(edge, rect.top - gap - actualHeight)
            : Math.min(vh - edge - actualHeight, rect.bottom + gap);
        dropdown.style.top = `${Math.max(edge, top)}px`;
    };

    const requestDropdownPosition = () => {
        if (positionFrame) cancelAnimationFrame(positionFrame);
        positionFrame = requestAnimationFrame(() => {
            positionFrame = null;
            positionActiveDropdown();
        });
    };

    form.querySelectorAll('[data-toggle]').forEach(btn => {
        btn.setAttribute('aria-expanded', 'false');
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const control = btn.closest('.metro-control');
            const willOpen = !control.classList.contains('open');
            closeAll(control);
            control.classList.toggle('open', willOpen);
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                portalDropdown(control);
                activeControl = control;
            } else {
                restoreDropdown(control);
                activeControl = null;
            }
            syncDropdownChrome();
            if (willOpen) requestDropdownPosition();
        });
    });

    form.querySelectorAll('.metro-dropdown').forEach(dropdown => {
        dropdown.addEventListener('click', e => e.stopPropagation());
        dropdown.addEventListener('wheel', e => e.stopPropagation(), { passive: true });
        dropdown.addEventListener('touchmove', e => e.stopPropagation(), { passive: true });
    });

    backdrop?.addEventListener('click', () => closeAll());
    document.addEventListener('click', () => closeAll());
    window.addEventListener('resize', requestDropdownPosition, { passive: true });
    window.addEventListener('scroll', requestDropdownPosition, { passive: true, capture: true });

    const summary = (items, empty) => items.length === 0 ? empty : items.length === 1 ? items[0] : `${items.length} selected`;
    const updateMoreSummary = () => {
        const count = ['beds','baths','adults','children','infants'].filter(k => state[k] > 0).length + (state.pets ? 1 : 0) + state.amenities.length;
        document.getElementById('metroMoreValue').textContent = count ? `${count} FILTER${count > 1 ? 'S' : ''}` : 'MORE';
    };

    const boroughButtons = [...form.querySelectorAll('[data-borough]')];
    const neighborhoodGrid = document.getElementById('metroNeighborhoodGrid');
    const renderNeighborhoods = () => {
        const source = state.boroughs.length
            ? state.boroughs.flatMap(b => neighborhoodData[b] || [])
            : Object.values(neighborhoodData).flat();
        const names = [...new Set(source.map(n => n.name).filter(Boolean))].sort();
        // Only discard a selected neighbourhood when the user has actively scoped
        // the list to one or more boroughs and that neighbourhood is outside them.
        if (state.boroughs.length) {
            state.neighborhoods = state.neighborhoods.filter(n => names.includes(n));
        }
        neighborhoodGrid.innerHTML = names.length ? names.map(name => `<button type="button" class="metro-option ${state.neighborhoods.includes(name) ? 'active' : ''}" data-neighborhood="${String(name).replace(/&/g,'&amp;').replace(/"/g,'&quot;')}">${name}</button>`).join('') : '<div class="metro-empty">No neighbourhoods available.</div>';
        document.getElementById('metroNeighborhoodValue').textContent = summary(state.neighborhoods, 'NEIGHBORHOOD');
        neighborhoodGrid.querySelectorAll('[data-neighborhood]').forEach(btn => btn.addEventListener('click', () => {
            const value = btn.dataset.neighborhood; btn.classList.toggle('active');
            state.neighborhoods = btn.classList.contains('active') ? [...new Set([...state.neighborhoods,value])] : state.neighborhoods.filter(x => x !== value);
            document.getElementById('metroNeighborhoodValue').textContent = summary(state.neighborhoods, 'NEIGHBORHOOD');
        }));
    };
    boroughButtons.forEach(btn => btn.addEventListener('click', () => {
        const value = btn.dataset.borough; btn.classList.toggle('active');
        state.boroughs = btn.classList.contains('active') ? [...new Set([...state.boroughs,value])] : state.boroughs.filter(x => x !== value);
        document.getElementById('metroBoroughValue').textContent = summary(state.boroughs, 'NYS / BOROUGH'); renderNeighborhoods();
    }));
    renderNeighborhoods();

    const propertyButtons = [...form.querySelectorAll('[data-category-id]')];
    propertyButtons.forEach(btn => btn.addEventListener('click', () => {
        const id = btn.dataset.categoryId; btn.classList.toggle('active');
        state.categoryIds = btn.classList.contains('active') ? [...new Set([...state.categoryIds,id])] : state.categoryIds.filter(x => x !== id);
        const names = propertyButtons.filter(x => x.classList.contains('active')).map(x => x.textContent.trim());
        document.getElementById('metroPropertyValue').textContent = summary(names, 'PROPERTY TYPE');
    }));

    form.querySelectorAll('[data-search]').forEach(input => input.addEventListener('input', () => {
        const term = input.value.toLowerCase().trim(); const type = input.dataset.search;
        const selector = type === 'borough' ? '[data-borough]' : type === 'neighborhood' ? '[data-neighborhood]' : '[data-category-id]';
        input.closest('.metro-dropdown').querySelectorAll(selector).forEach(btn => btn.style.display = btn.textContent.toLowerCase().includes(term) ? '' : 'none');
    }));

    const minRange = document.getElementById('metroMinRange'), maxRange = document.getElementById('metroMaxRange');
    const minHidden = document.getElementById('metroMinInput'), maxHidden = document.getElementById('metroMaxInput');
    const fmt = value => {
        value = Number(value); if (value >= 1000000) return currency + (value/1000000).toFixed(value % 1000000 ? 1 : 0) + 'm'; if (value >= 1000) return currency + Math.round(value/1000) + 'k'; return currency + value.toLocaleString();
    };
    const updatePrice = () => {
        const l = limits[state.purpose]; let min = Number(minRange.value), max = Number(maxRange.value);
        if (min > max - l.step) { min = Math.max(l.min, max - l.step); minRange.value = min; }
        minHidden.value = min; maxHidden.value = max;
        document.getElementById('metroMinDisplay').textContent = min === l.min ? 'MIN' : fmt(min);
        document.getElementById('metroMaxDisplay').textContent = max === l.max ? 'MAX' : fmt(max);
        document.getElementById('metroPriceValue').textContent = `${min === l.min ? 'MIN' : fmt(min)} TO ${max === l.max ? 'MAX' : fmt(max)}`;
        const span = l.max-l.min || 1; const left=((min-l.min)/span)*100, right=((max-l.min)/span)*100;
        const fill=document.getElementById('metroRangeFill'); fill.style.left=left+'%'; fill.style.width=(right-left)+'%';
    };
    const renderPresets = () => {
        const l=limits[state.purpose]; const wrap=document.getElementById('metroPresets');
        wrap.innerHTML=presets[state.purpose].map(([a,b])=>`<button type="button" class="metro-preset" data-min="${Math.min(a,l.max)}" data-max="${Math.min(b,l.max)}">${fmt(a)} – ${b >= l.max ? 'MAX' : fmt(b)}</button>`).join('');
        wrap.querySelectorAll('.metro-preset').forEach(btn=>btn.addEventListener('click',()=>{minRange.value=btn.dataset.min; maxRange.value=btn.dataset.max; updatePrice();}));
    };
    const applyPurpose = purpose => {
        state.purpose=purpose; document.getElementById('metroPurpose').value=purpose;
        form.querySelectorAll('.metro-mode-btn').forEach(b=>b.classList.toggle('active',b.dataset.purpose===purpose));
        const l=limits[purpose]; [minRange,maxRange].forEach(r=>{r.min=l.min;r.max=l.max;r.step=l.step;}); minRange.value=l.min; maxRange.value=l.max; renderPresets(); updatePrice();
    };
    form.querySelectorAll('.metro-mode-btn').forEach(btn=>btn.addEventListener('click',()=>applyPurpose(btn.dataset.purpose)));
    minRange.addEventListener('input',updatePrice); maxRange.addEventListener('input',updatePrice); applyPurpose('sale');

    form.querySelectorAll('[data-qty]').forEach(q => {
        const key=q.dataset.qty, count=q.querySelector('[data-count]');
        q.querySelectorAll('button').forEach(btn=>btn.addEventListener('click',()=>{state[key]=Math.max(0,state[key]+(btn.dataset.action==='plus'?1:-1)); count.textContent=state[key]; updateMoreSummary();}));
    });
    form.querySelectorAll('[data-pets]').forEach(btn=>btn.addEventListener('click',()=>{form.querySelectorAll('[data-pets]').forEach(b=>b.classList.remove('active'));btn.classList.add('active');state.pets=btn.dataset.pets;updateMoreSummary();}));
    form.querySelectorAll('[data-amenity]').forEach(ch=>ch.addEventListener('change',()=>{state.amenities=[...form.querySelectorAll('[data-amenity]:checked')].map(x=>x.value);updateMoreSummary();}));

    form.addEventListener('submit', () => {
        document.getElementById('metroBedsInput').value = state.beds || '';
        document.getElementById('metroBathsInput').value = state.baths || '';
        document.getElementById('metroAdultsInput').value = state.adults || '';
        document.getElementById('metroChildrenInput').value = state.children || '';
        document.getElementById('metroInfantsInput').value = state.infants || '';
        document.getElementById('metroPetsInput').value = state.pets;
        const d=document.getElementById('metroDynamicInputs'); d.innerHTML='';
        const add=(name,value)=>{const i=document.createElement('input');i.type='hidden';i.name=name;i.value=value;d.appendChild(i);};
        state.boroughs.forEach(v=>add('boroughs[]',v)); state.neighborhoods.forEach(v=>add('neighborhoods[]',v)); state.categoryIds.forEach(v=>add('category_ids[]',v)); state.amenities.forEach(v=>add('amenity_ids[]',v));
    });
    }
</script>
