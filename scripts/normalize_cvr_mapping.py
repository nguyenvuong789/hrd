#!/usr/bin/env python3
import csv
from pathlib import Path

root = Path(__file__).resolve().parents[1]
path = root / 'output/rewrite-master-enriched.csv'
with path.open(newline='', encoding='utf-8') as f:
    rows = list(csv.DictReader(f))
fields = list(rows[0])
if 'building_id' not in fields:
    fields.insert(fields.index('building_name') + 1, 'building_id')
for column in ('property_type_slug', 'property_city_slug'):
    if column not in fields:
        fields.append(column)

buildings = {
    'One River Villas': ('one-river-villas', 'one-river-villas'),
    'One River': ('one-river-villas', 'one-river-villas'),
    'Fusion': ('fusion-resort-villas', 'fusion-resort-villas'),
    'Fusion Resort': ('fusion-resort-villas', 'fusion-resort-villas'),
    'Ocean Estates': ('ocean-estates', 'ocean-estates'),
    'Montgomerie Links': ('montgomerie-links', 'montgomerie-links'),
    'Koi Resort': ('koi-resort', 'koi-resort'),
    'Premier Village': ('premier-village', 'premier-village'),
    'Regal Victoria': ('regal-victoria', 'regal-victoria'),
    'The Point': ('the-point', 'the-point'),
    'Sun Ponte': ('sun-ponte', 'sun-ponte'),
    'Vista Residence': ('vista-residence', 'vista-residence'),
    'Hiyori': ('hiyori-garden-tower', 'hiyori-garden-tower'),
}
title_buildings = {
    'one river': ('One River Villas', 'one-river-villas'),
    'fusion': ('Fusion Resort & Villas', 'fusion-resort-villas'),
    'koi resort': ('KOI Resort', 'koi-resort'),
    'premier village': ('Premier Village', 'premier-village'),
    'hiyori': ('Hiyori Garden Tower', 'hiyori-garden-tower'),
    'filmore': ('The Filmore Da Nang', 'the-filmore'),
    'ocean suites': ('The Ocean Suites', 'ocean-suites'),
    'mornachy': ('The Monarchy', 'monarchy'),
    'monarchy': ('The Monarchy', 'monarchy'),
    'altara': ('Altara Suites', 'altara-suites'),
    'sam tower': ('SAM Towers', 'sam-towers'),
    'blooming': ('Blooming Tower', 'blooming-tower'),
    'golden bay': ('Wyndham Danang Golden Bay', 'wyndham-danang-golden-bay'),
    'ocean estates': ('The Ocean Estates', 'ocean-estates'),
    'montgomerie': ('Montgomerie Links', 'montgomerie-links'),
    'regal victoria': ('Regal Victoria', 'regal-victoria'),
    'the point': ('The Point Villa', 'the-point'),
    'sun ponte': ('Sun Ponte', 'sun-ponte'),
    'vista residence': ('Vista Residence', 'vista-residence'),
}
registered_ids = {
    'monarchy', 'azura', 'hiyori-garden-tower', 'indochina-riverside-towers',
    'the-filmore', 'fpt-plaza', 'ocean-suites', 'ocean-villas',
    'one-river-villas', 'fusion-resort-villas', 'koi-resort', 'premier-village',
    'altara-suites', 'sam-towers', 'blooming-tower', 'wyndham-danang-golden-bay',
}
feature_map = {
    'river view': 'river-view', 'river-view': 'river-view',
    'garden/outdoor space': 'garden', 'garden': 'garden',
    'furnished': 'furnished', 'swimming-pool': 'swimming-pool',
    'ocean-view': 'ocean-view', 'balcony': 'balcony', 'gym': 'gym',
    'security-guard': 'security-guard', 'parking-space': 'parking-space',
}
type_map = {'Apartment': 'apartment', 'Villa': 'villas', 'Houses': 'houses'}
city_map = {'Da Nang': 'danang', 'Ngu Hanh Son': 'ngu-hanh-son', 'Hai Chau': 'hai-chau', 'Son Tra': 'son-tra', 'An Thuong': 'an-thuong'}

for r in rows:
    name = r.get('building_name', '').strip()
    if not name:
        title = (r.get('original_title') or '').lower()
        for needle, (display_name, slug) in title_buildings.items():
            if needle in title:
                r['building_name'], r['building_slug'] = display_name, slug
                name = display_name
                break
    if name in buildings:
        display, slug = buildings[name]
        r['building_name'] = {'one-river-villas':'One River Villas','fusion-resort-villas':'Fusion Resort & Villas','ocean-estates':'The Ocean Estates','montgomerie-links':'Montgomerie Links','koi-resort':'KOI Resort','premier-village':'Premier Village','regal-victoria':'Regal Victoria','the-point':'The Point Villa','sun-ponte':'Sun Ponte','vista-residence':'Vista Residence','hiyori-garden-tower':'Hiyori Garden Tower'}.get(slug, display)
        r['building_slug'] = slug
        r['building_id'] = slug if slug in registered_ids else ''
    elif r.get('building_slug'):
        r['building_id'] = r['building_slug'] if r['building_slug'] in registered_ids else ''
    else:
        r['building_id'] = ''
        r['project_confidence'] = 'not_applicable'
    r['property_type_slug'] = type_map.get(r.get('property_type',''), '')
    r['property_city_slug'] = city_map.get(r.get('property_city',''), '')
    feats = []
    for raw in (r.get('property_features') or '').split(';'):
        key = raw.strip().lower()
        if key in feature_map and feature_map[key] not in feats: feats.append(feature_map[key])
    r['property_features'] = ';'.join(feats)
    r['taxonomy_mapping_status'] = 'normalized_native_slugs'

with path.open('w', newline='', encoding='utf-8') as f:
    w = csv.DictWriter(f, fieldnames=fields); w.writeheader(); w.writerows(rows)
print('Normalized', len(rows), 'rows')
