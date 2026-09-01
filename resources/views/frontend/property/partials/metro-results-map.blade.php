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
                <div style="font-size:15px;font-weight:700;color:#1f1f1f;margin-bottom:5px;">${safeTitle}</div>
                ${safeAddress ? `<div style="font-size:12px;color:#6f7478;margin-bottom:9px;">${safeAddress}</div>` : ''}
                <a href="${slug}" style="font-size:12px;font-weight:700;color:${secondaryColor};text-decoration:none;">VIEW PROPERTY →</a>
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
            minZoom: 8,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            clickableIcons: false,
            gestureHandling: 'greedy',
            restriction: {
                latLngBounds: { north: 45.2, south: 39.5, east: -71.2, west: -80.0 },
                strictBounds: false,
            },
        };
        if (mapId) options.mapId = mapId;

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
        const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F'];
        const polygonInfo = new google.maps.InfoWindow();

        selectedAreas.forEach((area, index) => {
            const color = colors[index % colors.length];
            const parts = area.type === 'borough' ? area.parts : [area.coordinates];

            parts.forEach(coordinates => {
                if (!Array.isArray(coordinates) || !coordinates.length) return;
                const boroughOnly = area.type === 'borough';
                const polygon = new google.maps.Polygon({
                    paths: coordinates,
                    strokeColor: color,
                    // Borough-only mode deliberately removes the internal neighbourhood
                    // edges. All constituent shapes share one fill so visually it reads
                    // as the selected borough, not individual neighbourhood polygons.
                    strokeOpacity: boroughOnly ? 0 : 0.92,
                    strokeWeight: boroughOnly ? 0 : 2,
                    fillColor: color,
                    fillOpacity: boroughOnly ? 0.30 : 0.28,
                    map,
                    clickable: !boroughOnly,
                    zIndex: 2,
                });

                coordinates.forEach(coord => polygonBounds.extend(coord));

                // Neighbourhood names/borders only appear when neighbourhoods were
                // explicitly selected in the MSB.
                if (!boroughOnly) {
                    polygon.addListener('mouseover', event => {
                        polygon.setOptions({ fillOpacity: 0.42, strokeWeight: 3 });
                        polygonInfo.setContent(`<div style="padding:5px 7px;font-weight:700;color:#1f1f1f;">${area.name}<div style="font-size:11px;font-weight:500;color:#6f7478;margin-top:2px;">${area.borough}</div></div>`);
                        polygonInfo.setPosition(event.latLng);
                        polygonInfo.open({ map });
                    });
                    polygon.addListener('mousemove', event => polygonInfo.setPosition(event.latLng));
                    polygon.addListener('mouseout', () => {
                        polygon.setOptions({ fillOpacity: 0.28, strokeWeight: 2 });
                        polygonInfo.close();
                    });
                }
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
