@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__hero">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>مناطق التغطية</span><span data-en>Coverage</span></span>
      <h1 class="page__title" data-i18n="block"><span data-ar>نصلك في الرياض</span><span data-en>We come to you in Riyadh</span></h1>
      <p class="page__sub" data-i18n="block"><span data-ar>تحقق من موقعك على الخريطة — وإن كنت خارج التغطية حالياً، تواصل معنا وسنخبرك عند وصولنا إليك.</span><span data-en>Check your spot on the map — if you're outside coverage for now, tell us and we'll let you know when we reach you.</span></p>
    </div>
  </section>
  <section class="page__body">
    <div class="container">
      <div class="grid grid--side">
        <div class="card" style="padding: 10px;">
          <div id="coverageMap" class="map" style="height: 520px;"></div>
        </div>
        <div>
          <div class="card">
            <h2 class="h3" style="margin-bottom: 12px;"><x-i18n ar="الأحياء المغطاة" en="Covered districts" /></h2>
            <div class="chips">
              @foreach($coverage as $d)<span class="chip"><x-i18n :ar="$d->name_ar" :en="$d->name" /></span>@endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
@push('scripts')
<script>
  // Zones straight from the API (the same polygons the app draws).
  window.initCoverageMap = function () {
    var map = new google.maps.Map(document.getElementById('coverageMap'), { center: { lat: 24.7136, lng: 46.6753 }, zoom: 10, disableDefaultUI: true, zoomControl: true, styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }] });
    fetch('/api/v1/catalog/coverage/zones', { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (r) {
      var bounds = new google.maps.LatLngBounds();
      (r.data || []).forEach(function (z) {
        // GeoJSON: Polygon → [ring…], MultiPolygon → [[ring…]…]; points are [lng, lat].
        var g = z.geometry || {}; var polys = g.type === 'MultiPolygon' ? g.coordinates : [g.coordinates || []];
        polys.forEach(function (rings) {
          var paths = (rings || []).map(function (ring) { return ring.map(function (p) { return { lat: p[1], lng: p[0] }; }); });
          if (!paths.length || !paths[0].length) return;
          new google.maps.Polygon({ paths: paths, map: map, fillColor: z.color || '#8863E5', fillOpacity: 0.18, strokeColor: z.color || '#8863E5', strokeWeight: 2 });
          paths[0].forEach(function (p) { bounds.extend(p); });
        });
      });
      if (!bounds.isEmpty()) map.fitBounds(bounds);
    }).catch(function () {});
  };
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&callback=initCoverageMap&language=ar"></script>
@endpush
