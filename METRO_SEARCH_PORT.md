# Metrohco search port for Estaty

This build ports the Metrohco Old hero meta-search workflow into Estaty's theme and connects it to Estaty's real property results query.

## What changed

- Estaty home v1 hero now uses a Metro-style search layout with Buy/Rent, Borough/NYS, Neighborhood, Price Range, Property Type, More filters, and Search.
- Borough and neighborhood options reuse the Metrohco Old coordinate datasets.
- Property type and amenities use Estaty's own active database records.
- More filters support bedrooms, bathrooms, adult/child/infant capacity, pets, and amenities.
- Front-end `/properties` filtering now accepts the new controls and preserves them through pagination.
- Admin Add/Edit Property includes Borough, Neighborhood and occupancy/pet fields.
- Latitude/longitude can be selected by clicking or dragging a marker on Google Maps, or by searching an address.
- Reverse geocoding attempts to select the matching borough and neighborhood automatically.

## Google Maps environment

The build reads the same environment variable names used by Metrohco Old:

```env
PUBLIC_GOOGLE_MAP_API_KEY=
PUBLIC_GOOGLE_MAP_ID=
```

They are exposed to Laravel through `config/services.php`. If config is cached after deployment, run `php artisan config:clear` (or rebuild your config cache).

For a production Google Maps key, make sure the new Estaty/Metrohco domain is allowed in the key's HTTP referrer restrictions and that the Maps JavaScript API + Geocoding API are enabled.

## Required database step

Run after deploying the updated code:

```bash
php artisan migrate
php artisan config:clear
```

The migration adds `borough`, `neighborhood`, `adults`, `children`, `infants`, and `pets_allowed` to `properties`.

Existing properties remain valid because the new database columns are nullable/defaulted. To make an older listing searchable by Borough/Neighborhood, edit it in Admin and pick/confirm its location on the new map.

## Hero MSB visibility fix
The Metro search bar is rendered on Home V1, V2 and V3. Its wrapper intentionally does not use AOS animation so the search controls cannot remain opacity-hidden before/when AOS initializes.

After deploying Blade changes, clear Laravel's compiled views/cache:

```bash
php artisan optimize:clear
php artisan view:clear
```

## Results map highlighting update

- The property results map now uses the Google Maps JavaScript API configured by `PUBLIC_GOOGLE_MAP_API_KEY` and `PUBLIC_GOOGLE_MAP_ID`.
- Default/no-area view is focused on New York City.
- Borough searches highlight all neighbourhood polygons within the selected borough(s).
- Neighbourhood searches highlight only the selected neighbourhood polygon(s).
- Polygon geometry comes directly from the original MetroHCO coordinate datasets (`manhattanData.js`, `brooklynData.js`, `queensData.js`, `statenIslandData.js`, `bronxData.js`, `nyStateData.js`).
- Property markers remain visible on top of the search-area polygons.
- Search-area polygon bounds take priority over marker bounds so results stay visually focused on the requested NYC area.

## 2026-08-31 - MSB dropdown + map stability pass
- MSB dropdowns are temporarily portaled to `document.body` while open so hero/Swiper stacking contexts and overflow cannot clip or cover them.
- Desktop dropdowns are viewport-clamped, automatically open upward when needed, and use internal vertical scrolling.
- Tablet/mobile dropdowns use a safe bottom-sheet layout with touch scrolling and a backdrop; background page scrolling is locked only while the panel is open.
- The Google results-map stage now absolutely fills Estaty's ratio wrapper to prevent a zero-height map.
- Google Maps loader reuses an existing API script when present and handles missing/invalid configuration gracefully.
- Empty/null/invalid property coordinates are ignored instead of generating invalid markers.
