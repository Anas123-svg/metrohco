@php
    $sidebarPurpose = request()->input('purpose', 'sale');
    if (!in_array($sidebarPurpose, ['sale', 'rent'], true)) {
        $sidebarPurpose = 'sale';
    }

    $sidebarBoroughs = array_values(array_unique(array_filter((array) request()->input('boroughs', []))));
    $sidebarNeighborhoods = array_values(array_unique(array_filter((array) request()->input('neighborhoods', []))));
    $sidebarCategoryIds = array_map('strval', array_values(array_unique(array_filter((array) request()->input('category_ids', [])))));
    $sidebarAmenityIds = array_map('strval', array_values(array_unique(array_filter((array) request()->input('amenity_ids', [])))));

    $sidebarMin = request()->filled('min') ? (int) request()->input('min') : (int) ($min ?? 0);
    $sidebarMax = request()->filled('max') ? (int) request()->input('max') : (int) ($max ?? 0);

    $sidebarBeds = max(0, (int) request()->input('beds', 0));
    $sidebarBaths = max(0, (int) request()->input('baths', 0));
    $sidebarAdults = max(0, (int) request()->input('adults', 0));
    $sidebarChildren = max(0, (int) request()->input('children', 0));
    $sidebarInfants = max(0, (int) request()->input('infants', 0));
    $sidebarPets = request()->filled('pets_allowed') ? (string) request()->input('pets_allowed') : '';
@endphp

<style>
    .metro-side-filter {
        --msf-primary: var(--color-primary, #f57f4b);
        --msf-secondary: var(--color-secondary, #255056);
        --msf-border: rgba(31, 31, 31, .10);
        --msf-muted: #74787c;
        --msf-bg: #fff;
        border: 1px solid var(--msf-border);
        border-radius: 14px;
        background: var(--msf-bg);
        overflow: hidden;
        box-shadow: 0 14px 38px rgba(31,31,31,.06);
    }

    .metro-side-filter__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 18px 14px;
        border-bottom: 1px solid var(--msf-border);
    }

    .metro-side-filter__header h4 {
        margin: 0;
        color: var(--color-dark, #222);
        font-size: 16px;
        font-weight: 600;
    }

    .metro-side-reset {
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--msf-primary);
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
    }

    .metro-side-section {
        padding: 16px 18px;
        border-bottom: 1px solid var(--msf-border);
    }

    .metro-side-section:last-of-type {
        border-bottom: 0;
    }

    .metro-side-title {
        margin: 0 0 11px;
        color: var(--color-dark, #222);
        font-size: 12px;
        line-height: 1.2;
        font-weight: 600;
    }

    .metro-side-subtitle {
        margin: 16px 0 9px;
        color: var(--color-dark, #222);
        font-size: 10px;
        line-height: 1.2;
        font-weight: 600;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .metro-side-purpose {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .metro-side-purpose button,
    .metro-side-pet button {
        min-height: 39px;
        border: 1px solid var(--msf-border);
        border-radius: 8px;
        background: #fff;
        color: var(--color-dark, #222);
        font-size: 11px;
        font-weight: 600;
        transition: .16s ease;
    }

    .metro-side-purpose button.active,
    .metro-side-pet button.active {
        border-color: var(--msf-secondary);
        background: var(--msf-secondary);
        color: #fff;
    }

    .metro-side-search {
        width: 100%;
        height: 39px;
        margin-bottom: 9px;
        padding: 0 11px;
        border: 1px solid var(--msf-border);
        border-radius: 8px;
        outline: 0;
        background: #fff;
        color: var(--color-dark, #222);
        font-size: 11px;
    }

    .metro-side-search:focus,
    .metro-side-number:focus {
        border-color: var(--msf-primary);
        box-shadow: 0 0 0 3px rgba(245,127,75,.08);
    }

    .metro-side-scroll {
        max-height: 205px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px;
        overscroll-behavior: contain;
        scrollbar-width: thin;
    }

    .metro-side-scroll::-webkit-scrollbar { width: 5px; }
    .metro-side-scroll::-webkit-scrollbar-thumb {
        border-radius: 20px;
        background: rgba(31,31,31,.22);
    }

    .metro-side-check {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 6px 0;
        color: #4f5357;
        font-size: 11px;
        line-height: 1.35;
        cursor: pointer;
    }

    .metro-side-check input {
        flex: 0 0 auto;
        margin-top: 2px;
        accent-color: var(--msf-primary);
    }

    .metro-side-empty {
        padding: 8px 0;
        color: var(--msf-muted);
        font-size: 11px;
    }

    .metro-side-price {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .metro-side-field small {
        display: block;
        margin-bottom: 6px;
        color: var(--msf-muted);
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .metro-side-number {
        width: 100%;
        height: 40px;
        padding: 0 10px;
        border: 1px solid var(--msf-border);
        border-radius: 8px;
        outline: 0;
        background: #fff;
        color: var(--color-dark, #222);
        font-size: 11px;
    }

    .metro-side-more {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px;
    }

    .metro-side-more .metro-side-field:nth-child(n+3) {
        margin-top: 2px;
    }

    .metro-side-pet {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
        margin-top: 12px;
    }

    .metro-side-pet button {
        min-height: 34px;
        font-size: 10px;
    }

    .metro-side-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 16px 18px 18px;
    }

    .metro-side-submit {
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 9px;
        background: var(--msf-secondary);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        transition: .16s ease;
    }

    .metro-side-submit:hover {
        background: var(--msf-primary);
    }

    @media (max-width: 1199px) {
        .metro-side-filter {
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }
    }
</style>

<form action="{{ route('frontend.properties') }}" method="get" id="metroSidebarFilter" class="metro-side-filter" autocomplete="off">
    @if (request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request()->input('sort') }}">
    @endif

    <input type="hidden" name="purpose" id="metroSidebarPurpose" value="{{ $sidebarPurpose }}">
    <input type="hidden" name="pets_allowed" id="metroSidebarPets" value="{{ $sidebarPets }}">

    <div class="metro-side-filter__header">
        <h4>Filters</h4>
        <a class="metro-side-reset" href="{{ route('frontend.properties') }}">Reset</a>
    </div>

    {{-- Same BUY / RENT filter as the MSB --}}
    <div class="metro-side-section">
        <div class="metro-side-title">Buy / Rent</div>
        <div class="metro-side-purpose">
            <button type="button" data-sidebar-purpose="sale" class="{{ $sidebarPurpose === 'sale' ? 'active' : '' }}">BUY</button>
            <button type="button" data-sidebar-purpose="rent" class="{{ $sidebarPurpose === 'rent' ? 'active' : '' }}">RENT</button>
        </div>
    </div>

    {{-- Same Borough filter as the MSB --}}
    <div class="metro-side-section">
        <div class="metro-side-title">Borough / NYS</div>
        <input type="search" class="metro-side-search" placeholder="Search boroughs" data-side-search="borough">
        <div class="metro-side-scroll" id="metroSidebarBoroughs">
            @foreach (['Manhattan', 'Brooklyn', 'Queens', 'Staten Island', 'Bronx', 'New York State'] as $borough)
                <label class="metro-side-check" data-borough-row>
                    <input type="checkbox" name="boroughs[]" value="{{ $borough }}"
                        {{ in_array($borough, $sidebarBoroughs, true) ? 'checked' : '' }} data-sidebar-borough>
                    <span>{{ $borough }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Same Neighbourhood filter as the MSB. JS fills all 337 when no borough is selected. --}}
    <div class="metro-side-section">
        <div class="metro-side-title">Neighbourhood</div>
        <input type="search" class="metro-side-search" placeholder="Search neighbourhoods" data-side-search="neighborhood">
        <div class="metro-side-scroll" id="metroSidebarNeighborhoods">
            <div class="metro-side-empty">Loading neighbourhoods…</div>
        </div>
    </div>

    {{-- Same Pricing filter as the MSB --}}
    <div class="metro-side-section">
        <div class="metro-side-title">Pricing</div>
        <div class="metro-side-price">
            <label class="metro-side-field">
                <small>Min price</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="min" value="{{ $sidebarMin }}">
            </label>
            <label class="metro-side-field">
                <small>Max price</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="max" value="{{ $sidebarMax }}">
            </label>
        </div>
    </div>

    {{-- Same Property Type filter as the MSB --}}
    <div class="metro-side-section">
        <div class="metro-side-title">Property Type</div>
        <input type="search" class="metro-side-search" placeholder="Search property types" data-side-search="property">
        <div class="metro-side-scroll" id="metroSidebarPropertyTypes">
            @foreach ($categories as $category)
                @if ($category->categoryContent)
                    <label class="metro-side-check" data-property-row>
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                            {{ in_array((string) $category->id, $sidebarCategoryIds, true) ? 'checked' : '' }}>
                        <span>{{ $category->categoryContent->name }}</span>
                    </label>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Same More panel as the MSB: occupancy, pets and amenities live together. --}}
    <div class="metro-side-section">
        <div class="metro-side-title">More</div>
        <div class="metro-side-more">
            <label class="metro-side-field">
                <small>Bedrooms</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="beds" value="{{ $sidebarBeds ?: '' }}" placeholder="Any">
            </label>
            <label class="metro-side-field">
                <small>Bathrooms</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="baths" value="{{ $sidebarBaths ?: '' }}" placeholder="Any">
            </label>
            <label class="metro-side-field">
                <small>Adults</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="adults" value="{{ $sidebarAdults ?: '' }}" placeholder="Any">
            </label>
            <label class="metro-side-field">
                <small>Children</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="children" value="{{ $sidebarChildren ?: '' }}" placeholder="Any">
            </label>
            <label class="metro-side-field">
                <small>Infants</small>
                <input class="metro-side-number" type="number" min="0" step="1" name="infants" value="{{ $sidebarInfants ?: '' }}" placeholder="Any">
            </label>
        </div>

        <div class="metro-side-subtitle">Pets</div>
        <div class="metro-side-pet">
            <button type="button" data-sidebar-pets="" class="{{ $sidebarPets === '' ? 'active' : '' }}">ANY</button>
            <button type="button" data-sidebar-pets="1" class="{{ $sidebarPets === '1' ? 'active' : '' }}">YES</button>
            <button type="button" data-sidebar-pets="0" class="{{ $sidebarPets === '0' ? 'active' : '' }}">NO</button>
        </div>

        <div class="metro-side-subtitle">Amenities</div>
        <input type="search" class="metro-side-search" placeholder="Search amenities" data-side-search="amenity">
        <div class="metro-side-scroll" id="metroSidebarAmenities">
            @foreach ($amenities as $amenity)
                @if ($amenity->amenityContent)
                    <label class="metro-side-check" data-amenity-row>
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                            {{ in_array((string) $amenity->id, $sidebarAmenityIds, true) ? 'checked' : '' }}>
                        <span>{{ $amenity->amenityContent->name }}</span>
                    </label>
                @endif
            @endforeach
        </div>
    </div>

    <div class="metro-side-actions">
        <button type="submit" class="metro-side-submit">APPLY FILTERS</button>
    </div>
</form>

<script type="module">
    import { ManhattanData } from "{{ asset('assets/js/metrohco/coordinates/manhattanData.js') }}";
    import { BrooklynData } from "{{ asset('assets/js/metrohco/coordinates/brooklynData.js') }}";
    import { QueensData } from "{{ asset('assets/js/metrohco/coordinates/queensData.js') }}";
    import { StatenIslandData } from "{{ asset('assets/js/metrohco/coordinates/statenIslandData.js') }}";
    import { BronxData } from "{{ asset('assets/js/metrohco/coordinates/bronxData.js') }}";
    import { NewyorkStates } from "{{ asset('assets/js/metrohco/coordinates/nyStateData.js') }}";

    const form = document.getElementById('metroSidebarFilter');

    if (form) {
        const selectedNeighborhoods = @json($sidebarNeighborhoods);

        const neighborhoodData = {
            'Manhattan': ManhattanData.flatMap(item => item.neighborhoods || []),
            'Brooklyn': BrooklynData.flatMap(item => item.neighborhoods || []),
            'Queens': QueensData.flatMap(item => item.neighborhoods || []),
            'Staten Island': StatenIslandData.flatMap(item => item.neighborhoods || []),
            'Bronx': BronxData.flatMap(item => item.neighborhoods || []),
            'New York State': NewyorkStates.flatMap(item => item.neighborhoods || []),
        };

        const escapeHtml = value => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const boroughInputs = [...form.querySelectorAll('[data-sidebar-borough]')];
        const neighborhoodWrap = document.getElementById('metroSidebarNeighborhoods');
        const neighborhoodSearch = form.querySelector('[data-side-search="neighborhood"]');

        const getSelectedBoroughs = () => boroughInputs.filter(input => input.checked).map(input => input.value);

        const getNeighborhoodOptions = () => {
            const selectedBoroughs = getSelectedBoroughs();
            const boroughsToUse = selectedBoroughs.length ? selectedBoroughs : Object.keys(neighborhoodData);
            const seen = new Set();
            const options = [];

            boroughsToUse.forEach(borough => {
                (neighborhoodData[borough] || []).forEach(item => {
                    const name = String(item?.name || '').trim();
                    if (!name) return;
                    const key = name.toLowerCase();
                    if (seen.has(key)) return;
                    seen.add(key);
                    options.push({ name, borough });
                });
            });

            return options.sort((a, b) => a.name.localeCompare(b.name));
        };

        const renderNeighborhoods = () => {
            const previouslyChecked = new Set([
                ...selectedNeighborhoods,
                ...[...form.querySelectorAll('input[name="neighborhoods[]"]:checked')].map(input => input.value),
            ]);

            const options = getNeighborhoodOptions();

            neighborhoodWrap.innerHTML = options.length
                ? options.map(({ name, borough }) => `
                    <label class="metro-side-check" data-neighborhood-row data-search-text="${escapeHtml(`${name} ${borough}`.toLowerCase())}">
                        <input type="checkbox" name="neighborhoods[]" value="${escapeHtml(name)}" ${previouslyChecked.has(name) ? 'checked' : ''}>
                        <span>${escapeHtml(name)}</span>
                    </label>
                `).join('')
                : '<div class="metro-side-empty">No neighbourhoods found.</div>';

            if (neighborhoodSearch?.value) {
                filterRows(neighborhoodSearch, '[data-neighborhood-row]');
            }
        };

        boroughInputs.forEach(input => input.addEventListener('change', renderNeighborhoods));
        renderNeighborhoods();

        form.querySelectorAll('[data-sidebar-purpose]').forEach(button => {
            button.addEventListener('click', () => {
                form.querySelectorAll('[data-sidebar-purpose]').forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                document.getElementById('metroSidebarPurpose').value = button.dataset.sidebarPurpose;
            });
        });

        form.querySelectorAll('[data-sidebar-pets]').forEach(button => {
            button.addEventListener('click', () => {
                form.querySelectorAll('[data-sidebar-pets]').forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                document.getElementById('metroSidebarPets').value = button.dataset.sidebarPets;
            });
        });

        function filterRows(input, selector) {
            const term = input.value.trim().toLowerCase();
            const scope = input.closest('.metro-side-section');
            scope?.querySelectorAll(selector).forEach(row => {
                const haystack = (row.dataset.searchText || row.textContent || '').toLowerCase();
                row.style.display = haystack.includes(term) ? '' : 'none';
            });
        }

        const searchBindings = [
            ['borough', '[data-borough-row]'],
            ['neighborhood', '[data-neighborhood-row]'],
            ['property', '[data-property-row]'],
            ['amenity', '[data-amenity-row]'],
        ];

        searchBindings.forEach(([type, selector]) => {
            const input = form.querySelector(`[data-side-search="${type}"]`);
            input?.addEventListener('input', () => filterRows(input, selector));
        });

        form.addEventListener('submit', () => {
            // Match MSB behaviour: empty numeric filters should not be sent as zero.
            form.querySelectorAll('input[type="number"]').forEach(input => {
                if (input.value === '' || Number(input.value) <= 0) {
                    if (!['min', 'max'].includes(input.name)) input.disabled = true;
                }
            });

            if (document.getElementById('metroSidebarPets').value === '') {
                document.getElementById('metroSidebarPets').disabled = true;
            }
        });
    }
</script>
