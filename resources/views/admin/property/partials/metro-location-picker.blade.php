@php
    $editingProperty = $property ?? null;
    $mapApiKey = config('services.google_maps.api_key');
    $mapId = config('services.google_maps.map_id');
    $selectedBorough = old('borough', optional($editingProperty)->borough);
    $selectedNeighborhood = old('neighborhood', optional($editingProperty)->neighborhood);
    $selectedLat = old('latitude', optional($editingProperty)->latitude);
    $selectedLng = old('longitude', optional($editingProperty)->longitude);
@endphp

<div class="col-lg-3">
    <div class="form-group">
        <label>{{ __('Borough / NYS') }} *</label>
        <select name="borough" id="metroAdminBorough" class="form-control">
            <option value="" disabled {{ empty($selectedBorough) ? 'selected' : '' }}>{{ __('Select Borough') }}</option>
            @foreach (['Manhattan', 'Brooklyn', 'Queens', 'Staten Island', 'Bronx', 'New York State'] as $borough)
                <option value="{{ $borough }}" {{ $selectedBorough === $borough ? 'selected' : '' }}>{{ $borough }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>{{ __('Neighborhood') }} *</label>
        <select name="neighborhood" id="metroAdminNeighborhood" class="form-control" data-selected="{{ $selectedNeighborhood }}">
            <option value="">{{ __('Select neighborhood') }}</option>
        </select>
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>{{ __('Latitude') }} *</label>
        <input type="text" class="form-control" id="metroAdminLatitude" value="{{ $selectedLat }}" name="latitude" placeholder="Pick from map or enter latitude">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>{{ __('Longitude') }} *</label>
        <input type="text" class="form-control" id="metroAdminLongitude" value="{{ $selectedLng }}" name="longitude" placeholder="Pick from map or enter longitude">
    </div>
</div>
<div class="col-12">
    <div class="card border mb-4" style="box-shadow:none;">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end justify-content-between mb-3" style="gap:12px;">
                <div style="flex:1 1 420px;">
                    <label class="mb-1"><strong>{{ __('Pick Property Location From Map') }}</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="metroAdminMapSearch" placeholder="Search an address, landmark or neighborhood">
                        <div class="input-group-append"><button type="button" class="btn btn-primary" id="metroAdminMapSearchBtn">{{ __('Find on map') }}</button></div>
                    </div>
                    <small class="text-muted">{{ __('Click anywhere on the map or drag the marker. Latitude and longitude update automatically.') }}</small>
                </div>
                <div class="text-muted" id="metroAdminMapStatus" style="font-size:12px;"></div>
            </div>
            @if ($mapApiKey)
                <div id="metroAdminMap" style="height:420px;border-radius:10px;overflow:hidden;background:#f2f3f4;"></div>
            @else
                <div class="alert alert-warning mb-0">
                    {{ __('Google Maps key is missing. Set PUBLIC_GOOGLE_MAP_API_KEY in .env. You can still enter latitude/longitude manually.') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script type="module">
    import { ManhattanData } from "{{ asset('assets/js/metrohco/coordinates/manhattanData.js') }}";
    import { BrooklynData } from "{{ asset('assets/js/metrohco/coordinates/brooklynData.js') }}";
    import { QueensData } from "{{ asset('assets/js/metrohco/coordinates/queensData.js') }}";
    import { StatenIslandData } from "{{ asset('assets/js/metrohco/coordinates/statenIslandData.js') }}";
    import { BronxData } from "{{ asset('assets/js/metrohco/coordinates/bronxData.js') }}";
    import { NewyorkStates } from "{{ asset('assets/js/metrohco/coordinates/nyStateData.js') }}";

    const boroughSelect = document.getElementById('metroAdminBorough');
    const neighborhoodSelect = document.getElementById('metroAdminNeighborhood');
    if (boroughSelect && neighborhoodSelect) {
        const data = {
            'Manhattan': ManhattanData.flatMap(x => x.neighborhoods || []),
            'Brooklyn': BrooklynData.flatMap(x => x.neighborhoods || []),
            'Queens': QueensData.flatMap(x => x.neighborhoods || []),
            'Staten Island': StatenIslandData.flatMap(x => x.neighborhoods || []),
            'Bronx': BronxData.flatMap(x => x.neighborhoods || []),
            'New York State': NewyorkStates.flatMap(x => x.neighborhoods || [])
        };
        window.metroAdminNeighborhoodData = data;
        const render = (wanted = null) => {
            const borough = boroughSelect.value;
            const selected = wanted ?? neighborhoodSelect.dataset.selected ?? '';
            const names = [...new Set((data[borough] || []).map(n => n.name).filter(Boolean))].sort();
            neighborhoodSelect.innerHTML = '<option value="">Select neighborhood</option>' + names.map(name => `<option value="${String(name).replace(/&/g,'&amp;').replace(/"/g,'&quot;')}" ${name === selected ? 'selected' : ''}>${name}</option>`).join('');
            neighborhoodSelect.dataset.selected = '';
        };
        boroughSelect.addEventListener('change', () => render(''));
        if (window.metroPendingBorough && [...boroughSelect.options].some(o => o.value === window.metroPendingBorough)) {
            boroughSelect.value = window.metroPendingBorough;
        }
        render(window.metroPendingNeighborhood || neighborhoodSelect.dataset.selected);
        window.metroRenderAdminNeighborhoods = render;
    }
</script>

@if ($mapApiKey)
<script>
    window.initMetroPropertyMap = function () {
        const mapEl = document.getElementById('metroAdminMap');
        const latInput = document.getElementById('metroAdminLatitude');
        const lngInput = document.getElementById('metroAdminLongitude');
        const searchInput = document.getElementById('metroAdminMapSearch');
        const searchBtn = document.getElementById('metroAdminMapSearchBtn');
        const status = document.getElementById('metroAdminMapStatus');
        const boroughSelect = document.getElementById('metroAdminBorough');
        const neighborhoodSelect = document.getElementById('metroAdminNeighborhood');
        if (!mapEl || !latInput || !lngInput) return;

        const existingLat = parseFloat(latInput.value), existingLng = parseFloat(lngInput.value);
        const initial = Number.isFinite(existingLat) && Number.isFinite(existingLng)
            ? { lat: existingLat, lng: existingLng }
            : { lat: 40.7128, lng: -74.0060 };
        const options = { center: initial, zoom: Number.isFinite(existingLat) ? 15 : 11, streetViewControl: false, mapTypeControl: false };
        @if ($mapId)
            options.mapId = @json($mapId);
        @endif
        const map = new google.maps.Map(mapEl, options);
        const geocoder = new google.maps.Geocoder();
        const marker = new google.maps.Marker({ map, position: initial, draggable: true });

        const setPosition = (position, reverseGeocode = true) => {
            const lat = typeof position.lat === 'function' ? position.lat() : position.lat;
            const lng = typeof position.lng === 'function' ? position.lng() : position.lng;
            latInput.value = Number(lat).toFixed(7);
            lngInput.value = Number(lng).toFixed(7);
            marker.setPosition({lat:Number(lat),lng:Number(lng)});
            if (reverseGeocode) reverse({lat:Number(lat),lng:Number(lng)});
        };
        const setSelectIfPresent = (select, value) => {
            if (!select || !value) return false;
            const opt = [...select.options].find(o => o.value.toLowerCase() === value.toLowerCase());
            if (opt) { select.value = opt.value; select.dispatchEvent(new Event('change')); return true; }
            return false;
        };
        const normalizeBorough = text => {
            const value = (text || '').toLowerCase();
            if (value.includes('manhattan') || value.includes('new york county')) return 'Manhattan';
            if (value.includes('brooklyn') || value.includes('kings county')) return 'Brooklyn';
            if (value.includes('queens')) return 'Queens';
            if (value.includes('staten island') || value.includes('richmond county')) return 'Staten Island';
            if (value.includes('bronx')) return 'Bronx';
            return null;
        };
        const reverse = position => {
            status.textContent = 'Resolving map location…';
            geocoder.geocode({ location: position }, (results, code) => {
                if (code !== 'OK' || !results?.length) { status.textContent = 'Coordinates selected'; return; }
                const result = results[0];
                status.textContent = result.formatted_address || 'Coordinates selected';
                if (!searchInput.value) searchInput.value = result.formatted_address || '';
                const components = result.address_components || [];
                const findType = (...types) => components.find(c => types.some(t => c.types.includes(t)))?.long_name;
                const addressContext = [
                    findType('sublocality_level_1','sublocality'), findType('administrative_area_level_2'), result.formatted_address
                ].filter(Boolean).join(' ');
                let boroughCandidate = normalizeBorough(addressContext);
                const stateName = findType('administrative_area_level_1');
                if (!boroughCandidate && stateName === 'New York') boroughCandidate = 'New York State';
                const neighborhood = findType('neighborhood','sublocality_level_2','sublocality_level_3','locality','administrative_area_level_3');
                if (boroughCandidate && boroughSelect) {
                    window.metroPendingBorough = boroughCandidate;
                    window.metroPendingNeighborhood = neighborhood || '';
                    boroughSelect.value = boroughCandidate;
                    if (typeof window.metroRenderAdminNeighborhoods === 'function') {
                        window.metroRenderAdminNeighborhoods(neighborhood || '');
                    } else {
                        boroughSelect.dispatchEvent(new Event('change'));
                        setTimeout(() => {
                            if (typeof window.metroRenderAdminNeighborhoods === 'function') {
                                window.metroRenderAdminNeighborhoods(neighborhood || '');
                            } else if (neighborhood && neighborhoodSelect) {
                                setSelectIfPresent(neighborhoodSelect, neighborhood);
                            }
                        }, 150);
                    }
                }
            });
        };

        map.addListener('click', e => setPosition(e.latLng));
        marker.addListener('dragend', e => setPosition(e.latLng));
        searchBtn?.addEventListener('click', () => {
            const address = searchInput.value.trim(); if (!address) return;
            status.textContent = 'Searching…';
            geocoder.geocode({ address }, (results, code) => {
                if (code !== 'OK' || !results?.length) { status.textContent = 'Location not found'; return; }
                const loc = results[0].geometry.location; map.panTo(loc); map.setZoom(15); setPosition(loc);
            });
        });
        searchInput?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); searchBtn.click(); } });
        latInput.addEventListener('change', () => { const lat=parseFloat(latInput.value), lng=parseFloat(lngInput.value); if(Number.isFinite(lat)&&Number.isFinite(lng)){map.panTo({lat,lng});setPosition({lat,lng});} });
        lngInput.addEventListener('change', () => { const lat=parseFloat(latInput.value), lng=parseFloat(lngInput.value); if(Number.isFinite(lat)&&Number.isFinite(lng)){map.panTo({lat,lng});setPosition({lat,lng});} });
        if (Number.isFinite(existingLat) && Number.isFinite(existingLng)) reverse(initial);
    };
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($mapApiKey) }}&callback=initMetroPropertyMap&v=weekly"></script>
@endif
