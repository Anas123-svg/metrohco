@php
    $metroMapApiKey = config('services.google_maps.api_key');
    $metroMapId = config('services.google_maps.map_id');
    $selectedBoroughs = array_values(array_unique(array_filter((array) request()->input('boroughs', []))));
    $selectedNeighborhoods = array_values(array_unique(array_filter((array) request()->input('neighborhoods', []))));
@endphp

<script type="module">
    import { ManhattanData } from "{{ asset('assets/js/metrohco/coordinates/manhattanData.js') }}";
    import { BrooklynData } from "{{ asset('assets/js/metrohco/coordinates/brooklynData.js') }}";
    import { QueensData } from "{{ asset('assets/js/metrohco/coordinates/queensData.js') }}";
    import { StatenIslandData } from "{{ asset('assets/js/metrohco/coordinates/statenIslandData.js') }}";
    import { BronxData } from "{{ asset('assets/js/metrohco/coordinates/bronxData.js') }}";
    import { NewyorkStates } from "{{ asset('assets/js/metrohco/coordinates/nyStateData.js') }}";

    const NYC_CENTER = { lat: 40.7128, lng: -74.0060 };
    const NYC_BOUNDS = {
        north: 40.917577,
        south: 40.477399,
        east: -73.700009,
        west: -74.259090,
    };

    const selectedBoroughs = @json($selectedBoroughs);
    const selectedNeighborhoods = @json($selectedNeighborhoods);
    const properties = @json($property_contents->items());
    const mapApiKey = @json($metroMapApiKey);
    const mapId = @json($metroMapId);
    const primaryColor = @json('#' . ltrim($basicInfo->primary_color ?? 'F57F4B', '#'));
    const secondaryColor = @json('#' . ltrim($basicInfo->secondary_color ?? '255056', '#'));


    // MetroHCO / Estaty results-map base style supplied for this project.
    // Kept local to this map so it is deterministic and does not depend on
    // Cloud Map Styling configuration attached to a Google Map ID.
    // Base map styling. Road geometry is appended separately according to zoom,
    // because fixed screen-pixel road weights become visually heavy when zoomed out.
    const METRO_MAP_BASE_STYLE = [
        // Global geometry + soft label treatment.
        { featureType: 'all', elementType: 'geometry', stylers: [{ visibility: 'on' }] },
        { featureType: 'all', elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
        { featureType: 'all', elementType: 'labels.text.fill', stylers: [{ color: '#6B7280' }] },
        { featureType: 'all', elementType: 'labels.text.stroke', stylers: [{ color: '#FFFFFF' }, { weight: 0.8 }, { visibility: 'on' }] },

        // Administrative labels stay visible, while local/neighbourhood geometry
        // remains hidden to prevent thick non-selected zone boundaries.
        { featureType: 'administrative.country', elementType: 'geometry.stroke', stylers: [{ visibility: 'on' }, { color: '#D3D8DC' }, { weight: 0.28 }] },
        { featureType: 'administrative.province', elementType: 'all', stylers: [{ visibility: 'off' }] },
        { featureType: 'administrative.locality', elementType: 'geometry', stylers: [{ visibility: 'off' }] },
        { featureType: 'administrative.locality', elementType: 'labels', stylers: [{ visibility: 'on' }] },
        { featureType: 'administrative.locality', elementType: 'labels.text.fill', stylers: [{ color: '#4B5563' }] },
        { featureType: 'administrative.locality', elementType: 'labels.text.stroke', stylers: [{ color: '#FFFFFF' }, { weight: 0.65 }] },
        { featureType: 'administrative.neighborhood', elementType: 'geometry', stylers: [{ visibility: 'off' }] },
        { featureType: 'administrative.neighborhood', elementType: 'labels', stylers: [{ visibility: 'on' }] },
        { featureType: 'administrative.neighborhood', elementType: 'labels.text.fill', stylers: [{ color: '#70757A' }] },
        { featureType: 'administrative.neighborhood', elementType: 'labels.text.stroke', stylers: [{ color: '#FFFFFF' }, { weight: 0.55 }] },

        // Clean land / low visual noise.
        { featureType: 'landscape', elementType: 'all', stylers: [{ visibility: 'on' }] },
        { featureType: 'landscape', elementType: 'geometry.fill', stylers: [{ color: '#FFFFFF' }, { lightness: 100 }, { gamma: 1.15 }] },
        { featureType: 'landscape', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },
        { featureType: 'poi', elementType: 'all', stylers: [{ visibility: 'off' }] },

        // Road labels/strokes are always suppressed. Only lightweight fills are
        // introduced by getMetroMapStyle() below.
        { featureType: 'road', elementType: 'labels', stylers: [{ visibility: 'off' }] },
        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },

        // Transit stations remain hidden. Transit lines are also zoom-aware below.
        { featureType: 'transit.station', elementType: 'all', stylers: [{ visibility: 'off' }] },

        // Water remains recognisable without competing with the selection layer.
        { featureType: 'water', elementType: 'geometry.fill', stylers: [{ color: '#3F819C' }] },
        { featureType: 'water', elementType: 'labels', stylers: [{ visibility: 'off' }] },
    ];

    function getRoadZoomBucket(zoom) {
        const z = Number(zoom ?? 10);
        if (z <= 5) return 'world';
        if (z <= 7) return 'country';
        if (z <= 9) return 'region';
        if (z <= 11) return 'city';
        if (z <= 13) return 'district';
        return 'street';
    }

    function getMetroMapStyle(zoom) {
        const bucket = getRoadZoomBucket(zoom);

        // Hide lower-level roads entirely at wide zooms instead of allowing lots
        // of tiny fixed-width lines to merge into thick bands.
        const roadProfiles = {
            world: {
                highway: { visibility: 'off', weight: 0 },
                controlled: { visibility: 'off', weight: 0 },
                arterial: { visibility: 'off', weight: 0 },
                local: { visibility: 'off', weight: 0 },
                transit: { visibility: 'off', weight: 0 },
            },
            country: {
                highway: { visibility: 'on', weight: 0.18 },
                controlled: { visibility: 'on', weight: 0.12 },
                arterial: { visibility: 'off', weight: 0 },
                local: { visibility: 'off', weight: 0 },
                transit: { visibility: 'off', weight: 0 },
            },
            region: {
                highway: { visibility: 'on', weight: 0.28 },
                controlled: { visibility: 'on', weight: 0.18 },
                arterial: { visibility: 'on', weight: 0.12 },
                local: { visibility: 'off', weight: 0 },
                transit: { visibility: 'off', weight: 0 },
            },
            city: {
                highway: { visibility: 'on', weight: 0.42 },
                controlled: { visibility: 'on', weight: 0.28 },
                arterial: { visibility: 'on', weight: 0.22 },
                local: { visibility: 'off', weight: 0 },
                transit: { visibility: 'on', weight: 0.12 },
            },
            district: {
                highway: { visibility: 'on', weight: 0.58 },
                controlled: { visibility: 'on', weight: 0.38 },
                arterial: { visibility: 'on', weight: 0.34 },
                local: { visibility: 'on', weight: 0.16 },
                transit: { visibility: 'on', weight: 0.20 },
            },
            street: {
                highway: { visibility: 'on', weight: 0.78 },
                controlled: { visibility: 'on', weight: 0.50 },
                arterial: { visibility: 'on', weight: 0.46 },
                local: { visibility: 'on', weight: 0.28 },
                transit: { visibility: 'on', weight: 0.28 },
            },
        };

        const p = roadProfiles[bucket];
        return [
            ...METRO_MAP_BASE_STYLE,

            // Highways keep a muted version of the existing warm tone.
            {
                featureType: 'road.highway',
                elementType: 'geometry.fill',
                stylers: [
                    { visibility: p.highway.visibility },
                    { color: bucket === 'street' || bucket === 'district' ? '#DCE486' : '#D9DDB0' },
                    { weight: p.highway.weight },
                    { gamma: 1 },
                    { lightness: bucket === 'street' ? -5 : 4 },
                ],
            },
            { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },

            {
                featureType: 'road.highway.controlled_access',
                elementType: 'geometry.fill',
                stylers: [
                    { visibility: p.controlled.visibility },
                    { color: '#C9CBCC' },
                    { weight: p.controlled.weight },
                ],
            },
            { featureType: 'road.highway.controlled_access', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },

            // Arterials only appear once the map is close enough for them to add
            // useful context. Their green is desaturated at wider zoom levels.
            {
                featureType: 'road.arterial',
                elementType: 'geometry.fill',
                stylers: [
                    { visibility: p.arterial.visibility },
                    { color: bucket === 'street' || bucket === 'district' ? '#8BC795' : '#B4CFB8' },
                    { weight: p.arterial.weight },
                    { lightness: 0 },
                ],
            },
            { featureType: 'road.arterial', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },

            {
                featureType: 'road.local',
                elementType: 'geometry.fill',
                stylers: [
                    { visibility: p.local.visibility },
                    { color: '#D7D9DA' },
                    { weight: p.local.weight },
                ],
            },
            { featureType: 'road.local', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },

            {
                featureType: 'transit.line',
                elementType: 'geometry.fill',
                stylers: [
                    { visibility: p.transit.visibility },
                    { color: '#D8B4B4' },
                    { gamma: 1 },
                    { weight: p.transit.weight },
                ],
            },
            { featureType: 'transit.line', elementType: 'geometry.stroke', stylers: [{ visibility: 'off' }] },
        ];
    }

    // Highlight palette intentionally uses the same two brand colors as the MSB:
    // secondary = structural outline/borough emphasis, primary = selected area accent.
const HIGHLIGHT_THEME = {
    fill: '#FE7501',
    fillOpacity: 0.14,

    stroke: '#FE7501',
    strokeOpacity: 0.78,

    hoverFillOpacity: 0.22,
    hoverStrokeOpacity: 0.90,
};

// Polygon strokes are screen-pixel based in Google Maps. A fixed 2px border
// therefore feels increasingly heavy as the map gets closer. Keep it crisp
// at city scale, then progressively thin it as the user zooms in.
function getHighlightStrokeWeight(zoom, hovered = false) {
    const z = Number(zoom ?? 10);
    let weight;

    if (z >= 18) weight = 0.50;
    else if (z >= 16) weight = 0.60;
    else if (z >= 14) weight = 0.72;
    else if (z >= 12) weight = 0.86;
    else if (z >= 10) weight = 1.00;
    else weight = 1.10;

    return hovered ? Math.min(weight + 0.18, 1.22) : weight;
}

function getHighlightStrokeOpacity(zoom, hovered = false) {
    const z = Number(zoom ?? 10);
    const base = z >= 16 ? 0.66 : z >= 13 ? 0.72 : HIGHLIGHT_THEME.strokeOpacity;
    return hovered ? Math.min(base + 0.12, HIGHLIGHT_THEME.hoverStrokeOpacity) : base;
}
    // These datasets are byte-for-byte copies of metrohco_old/public/frontend/assets/coordinates.
    const boroughData = {
        'Manhattan': ManhattanData.flatMap(item => item.neighborhoods || []),
        'Brooklyn': BrooklynData.flatMap(item => item.neighborhoods || []),
        'Queens': QueensData.flatMap(item => item.neighborhoods || []),
        'Staten Island': StatenIslandData.flatMap(item => item.neighborhoods || []),
        'Bronx': BronxData.flatMap(item => item.neighborhoods || []),
        'New York State': NewyorkStates.flatMap(item => item.neighborhoods || []),
    };

    function loadGoogleMaps() {
        if (window.google?.maps) return Promise.resolve(window.google.maps);
        if (!mapApiKey) return Promise.reject(new Error('Google Maps API key is missing.'));
        if (window.__metroGoogleMapsPromise) return window.__metroGoogleMapsPromise;

        window.__metroGoogleMapsPromise = new Promise((resolve, reject) => {
            const finish = () => {
                if (window.google?.maps) resolve(window.google.maps);
                else reject(new Error('Google Maps loaded without the Maps library.'));
            };

            // Reuse an already-injected Google Maps script if another component
            // started loading it first. This prevents duplicate-loader errors.
            const existing = [...document.scripts].find(script =>
                script.src && script.src.includes('maps.googleapis.com/maps/api/js')
            );
            if (existing) {
                existing.addEventListener('load', finish, { once: true });
                existing.addEventListener('error', () => reject(new Error('Google Maps failed to load.')), { once: true });
                const pollStarted = Date.now();
                const poll = window.setInterval(() => {
                    if (window.google?.maps) {
                        window.clearInterval(poll);
                        finish();
                    } else if (Date.now() - pollStarted > 12000) {
                        window.clearInterval(poll);
                        reject(new Error('Google Maps did not finish loading.'));
                    }
                }, 80);
                return;
            }

            const callbackName = '__metroResultsMapLoaded';
            window[callbackName] = () => {
                delete window[callbackName];
                finish();
            };

            const script = document.createElement('script');
            const params = new URLSearchParams({
                key: mapApiKey,
                callback: callbackName,
                v: 'weekly',
                loading: 'async',
            });
            script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                window.__metroGoogleMapsPromise = null;
                reject(new Error('Google Maps failed to load. Check the API key, Maps JavaScript API, billing, and allowed referrers.'));
            };
            document.head.appendChild(script);
        });

        return window.__metroGoogleMapsPromise;
    }

    function normaliseName(value) {
        return String(value || '').trim().toLowerCase();
    }

    function collectSelectedAreas() {
        const areas = [];
        const seen = new Set();
        const wantedNeighborhoods = new Set(selectedNeighborhoods.map(normaliseName));

        const addNeighborhoodArea = (borough, neighborhood) => {
            if (!neighborhood?.coordinates?.length) return;
            const key = `${normaliseName(borough)}::${normaliseName(neighborhood.name)}`;
            if (seen.has(key)) return;
            seen.add(key);
            areas.push({
                type: 'neighborhood',
                borough,
                name: neighborhood.name,
                coordinates: neighborhood.coordinates,
            });
        };

        if (wantedNeighborhoods.size) {
            // A neighbourhood can be selected even when no borough was chosen. In that
            // case search all borough datasets for the requested neighbourhood name.
            const searchBoroughs = selectedBoroughs.length ? selectedBoroughs : Object.keys(boroughData);
            searchBoroughs.forEach(borough => {
                (boroughData[borough] || []).forEach(neighborhood => {
                    if (wantedNeighborhoods.has(normaliseName(neighborhood.name))) {
                        addNeighborhoodArea(borough, neighborhood);
                    }
                });
            });
        } else if (selectedBoroughs.length) {
            // Borough-only search: treat all constituent neighbourhood geometry as ONE
            // borough highlight layer. We intentionally do not draw internal borders or
            // neighbourhood hover states, so the map reads as a borough selection rather
            // than dozens of individually highlighted neighbourhoods.
            selectedBoroughs.forEach(borough => {
                const parts = (boroughData[borough] || [])
                    .map(neighborhood => neighborhood?.coordinates)
                    .filter(coordinates => Array.isArray(coordinates) && coordinates.length);
                if (parts.length) {
                    areas.push({ type: 'borough', borough, name: borough, parts });
                }
            });
        }

        return areas;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function makePropertyInfo(property) {
        const safeTitle = escapeHtml(property.title || 'Property');
        const safeAddress = escapeHtml(property.address || property.neighborhood || property.borough || '');
        const slug = property.slug ? `${@json(url('/property'))}/${encodeURIComponent(property.slug)}` : '#';
        return `
            <div style="min-width:210px;max-width:270px;padding:3px 2px 2px;font-family:inherit;">
                <div style="font-size:14px;font-weight:600;color:#2F3337;margin-bottom:5px;line-height:1.35;">${safeTitle}</div>
                ${safeAddress ? `<div style="font-size:11px;font-weight:400;color:#73777B;margin-bottom:9px;line-height:1.4;">${safeAddress}</div>` : ''}
                <a href="${slug}" style="font-size:11px;font-weight:600;color:${secondaryColor};text-decoration:none;letter-spacing:.01em;">VIEW PROPERTY →</a>
            </div>`;
    }

    function renderAreaSummary(areas) {
        const wrap = document.getElementById('metroMapAreaSummary');
        const list = document.getElementById('metroMapAreaPills');
        if (!wrap || !list) return;

        const labels = selectedNeighborhoods.length
            ? selectedNeighborhoods
            : selectedBoroughs.filter(name => name !== 'New York State');

        if (!labels.length) {
            wrap.hidden = true;
            list.innerHTML = '';
            return;
        }

        list.innerHTML = labels.slice(0, 8).map(label => `<span class="metro-map-pill">${String(label).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`).join('');
        if (labels.length > 8) list.insertAdjacentHTML('beforeend', `<span class="metro-map-pill">+${labels.length - 8}</span>`);
        wrap.hidden = false;
    }


    function makePropertyMarkerIcon() {
        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="42" viewBox="0 0 38 46">
                <path d="M19 1.5C9.5 1.5 2 9 2 18.3c0 12.2 17 26.2 17 26.2s17-14 17-26.2C36 9 28.5 1.5 19 1.5Z" fill="${secondaryColor}" stroke="#ffffff" stroke-width="2.2"/>
                <circle cx="19" cy="18" r="7" fill="${primaryColor}" stroke="#ffffff" stroke-width="2"/>
            </svg>`;
        return {
            url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`,
            scaledSize: new google.maps.Size(34, 42),
            anchor: new google.maps.Point(17, 40),
        };
    }

    async function initMetroResultsMap() {
        const mapEl = document.getElementById('main-map');
        if (!mapEl) return;

        try {
            await loadGoogleMaps();
        } catch (error) {
            mapEl.innerHTML = `<div style="height:100%;display:flex;align-items:center;justify-content:center;padding:30px;text-align:center;color:#6f7478;background:#f7f7f7;">${error.message}</div>`;
            return;
        }

        const options = {
            center: NYC_CENTER,
            zoom: 10,
            minZoom: 1,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            zoomControl: true,
            clickableIcons: false,
            gestureHandling: 'greedy',
            styles: getMetroMapStyle(10),
            backgroundColor: '#ffffff',
        };
        // Do not set mapId here: Google Cloud Map Styling can override/disable
        // a local styles[] array. The results map must use the supplied JSON exactly.

        let map;
        try {
            map = new google.maps.Map(mapEl, options);
        } catch (error) {
            console.error('MetroHCO map initialisation failed:', error);
            mapEl.innerHTML = '<div style="height:100%;display:flex;align-items:center;justify-content:center;padding:30px;text-align:center;color:#6f7478;background:#f7f7f7;">The map could not be initialised. Please verify the Google Maps API configuration.</div>';
            return;
        }
        window.activeMap = map;
        const selectedAreas = collectSelectedAreas();
        renderAreaSummary(selectedAreas);

        const polygonBounds = new google.maps.LatLngBounds();
        const polygonInfo = new google.maps.InfoWindow();
        const selectedPolygons = [];

        selectedAreas.forEach(area => {
            const parts = area.type === 'borough' ? area.parts : [area.coordinates];

            parts.forEach(coordinates => {
                if (!Array.isArray(coordinates) || !coordinates.length) return;
const boroughOnly = area.type === 'borough';

const polygon = new google.maps.Polygon({
    paths: coordinates,

    /*
     * Borough-only:
     * No stroke because each borough is constructed from multiple
     * neighbourhood polygons. Adding strokes here would expose
     * neighbourhood boundaries inside the borough.
     *
     * Neighbourhood:
     * Purple fill + deep-purple boundary.
     */
    strokeColor: HIGHLIGHT_THEME.stroke,
    strokeOpacity: boroughOnly ? 0 : getHighlightStrokeOpacity(map.getZoom()),
    strokeWeight: boroughOnly ? 0 : getHighlightStrokeWeight(map.getZoom()),

    fillColor: HIGHLIGHT_THEME.fill,
    fillOpacity: HIGHLIGHT_THEME.fillOpacity,

    map,
    clickable: !boroughOnly,
    zIndex: boroughOnly ? 2 : 3,
});

polygon.__metroHovered = false;
selectedPolygons.push({ polygon, boroughOnly });
coordinates.forEach(coord => polygonBounds.extend(coord));
                // Neighbourhood hover stays restrained: stronger fill/border, no rainbow
                // colors, so selected geography still belongs to the Estaty visual system.
if (!boroughOnly) {
    polygon.addListener('mouseover', event => {
        polygon.__metroHovered = true;
        polygon.setOptions({
            fillOpacity: HIGHLIGHT_THEME.hoverFillOpacity,
            strokeOpacity: getHighlightStrokeOpacity(map.getZoom(), true),
            strokeWeight: getHighlightStrokeWeight(map.getZoom(), true),
        });

        polygonInfo.setContent(`
            <div
                style="
                    padding: 7px 9px;
                    min-width: 110px;
                    font-family: inherit;
                    line-height: 1.35;
                "
            >
                <div
                    style="
                        font-size: 12px;
                        font-weight: 500;
                        color: #45484D;
                        letter-spacing: 0;
                    "
                >
                    ${escapeHtml(area.name)}
                </div>

                <div
                    style="
                        margin-top: 2px;
                        font-size: 10px;
                        font-weight: 400;
                        color: #7A7D82;
                        letter-spacing: .025em;
                    "
                >
                    ${escapeHtml(area.borough)}
                </div>
            </div>
        `);

        polygonInfo.setPosition(event.latLng);
        polygonInfo.open({ map });
    });

    polygon.addListener('mousemove', event => {
        polygonInfo.setPosition(event.latLng);
    });

    polygon.addListener('mouseout', () => {
        polygon.__metroHovered = false;
        polygon.setOptions({
            fillOpacity: HIGHLIGHT_THEME.fillOpacity,
            strokeOpacity: getHighlightStrokeOpacity(map.getZoom()),
            strokeWeight: getHighlightStrokeWeight(map.getZoom()),
        });

        polygonInfo.close();
    });
}            });
        });

        // Keep selected boundaries visually consistent at every zoom. Google Maps
        // uses screen-pixel stroke widths, so without this adjustment a border that
        // looks right at NYC scale becomes too dominant at street/building scale.
        let currentRoadZoomBucket = getRoadZoomBucket(map.getZoom());
        map.addListener('zoom_changed', () => {
            const zoom = map.getZoom();

            // Update road density/weight only when crossing a zoom bucket. This keeps
            // the map responsive while preventing highways and streets from turning
            // into thick bands at state/country/world scales.
            const nextRoadZoomBucket = getRoadZoomBucket(zoom);
            if (nextRoadZoomBucket !== currentRoadZoomBucket) {
                currentRoadZoomBucket = nextRoadZoomBucket;
                map.setOptions({ styles: getMetroMapStyle(zoom) });
            }

            selectedPolygons.forEach(({ polygon, boroughOnly }) => {
                if (boroughOnly) return;
                polygon.setOptions({
                    strokeOpacity: getHighlightStrokeOpacity(zoom, polygon.__metroHovered),
                    strokeWeight: getHighlightStrokeWeight(zoom, polygon.__metroHovered),
                });
            });
        });

        const markerBounds = new google.maps.LatLngBounds();
        let markerCount = 0;
        properties.forEach(property => {
            if (property.latitude === null || property.latitude === undefined || property.longitude === null || property.longitude === undefined) return;
            if (String(property.latitude).trim() === '' || String(property.longitude).trim() === '') return;
            const lat = Number(property.latitude);
            const lng = Number(property.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;

            markerCount += 1;
            markerBounds.extend({ lat, lng });
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map,
                title: property.title || 'Property',
                icon: makePropertyMarkerIcon(),
                optimized: true,
            });
            const info = new google.maps.InfoWindow({ content: makePropertyInfo(property) });
            marker.addListener('click', () => info.open({ anchor: marker, map }));
        });

        // Search area always wins over marker spread. This is what keeps a Manhattan/
        // Brooklyn/etc. search visually focused on the polygon the user chose.
        if (!polygonBounds.isEmpty()) {
            map.fitBounds(polygonBounds, 42);
            google.maps.event.addListenerOnce(map, 'idle', () => {
                if ((map.getZoom() || 0) > 15) map.setZoom(15);
            });
            return;
        }

        // No area filter: deliberately focus on NYC instead of Estaty's former London/global view.
        map.fitBounds(NYC_BOUNDS, 24);
        google.maps.event.addListenerOnce(map, 'idle', () => {
            if ((map.getZoom() || 0) < 9) map.setZoom(10);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMetroResultsMap, { once: true });
    } else {
        initMetroResultsMap();
    }
</script>
