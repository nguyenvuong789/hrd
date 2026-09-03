#!/usr/bin/env python3
import csv
from pathlib import Path

root = Path(__file__).resolve().parents[1]
p = root / 'output/rewrite-master-enriched.csv'
with p.open(newline='', encoding='utf-8') as f: rows = list(csv.DictReader(f))
fields = list(rows[0])
for col in ('property_area', 'property_area_slug', 'property_city_term_slugs'):
    if col not in fields: fields.append(col)
district = {'Ngu Hanh Son':'Ngu Hanh Son','Hai Chau':'Hai Chau','Son Tra':'Son Tra','An Thuong':'An Thuong','Da Nang':'Da Nang'}
for r in rows:
    title = (r.get('original_title') or '').lower()
    old = r.get('property_city','Da Nang')
    area = district.get(old, '')
    evidence = (r.get('original_title','') + ' ' + r.get('source_description','')).lower()
    if 'ngu hanh son' in evidence or 'ngũ hành sơn' in evidence: area = 'Ngu Hanh Son'
    elif 'hai chau' in evidence or 'hải châu' in evidence: area = 'Hai Chau'
    elif 'son tra' in evidence or 'sơn trà' in evidence: area = 'Son Tra'
    if 'an thuong' in title: area = 'An Thuong'
    elif 'my an' in title: area = 'My An'
    elif 'hoa xuan' in title: area = 'Hoa Xuan'
    r['property_city'] = 'Da Nang'
    r['property_city_slug'] = 'danang'
    r['property_area'] = area
    r['property_area_slug'] = area.lower().replace(' ','-') if area else ''
    terms = ['danang']
    if area in ('Ngu Hanh Son','Hai Chau','Son Tra'): terms.append(area.lower().replace(' ','-'))
    if area == 'An Thuong': terms += ['ngu-hanh-son','an-thuong']
    r['property_city_term_slugs'] = ';'.join(dict.fromkeys(terms))
with p.open('w',newline='',encoding='utf-8') as f:
    w=csv.DictWriter(f,fieldnames=fields); w.writeheader(); w.writerows(rows)
print('Normalized location for',len(rows),'rows')
