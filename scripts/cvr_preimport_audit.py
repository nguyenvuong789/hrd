#!/usr/bin/env python3
import csv,json,re,requests,subprocess
from pathlib import Path
from PIL import Image
from io import BytesIO
root=Path(__file__).resolve().parents[1]; p=root/'output/rewrite-master-enriched.csv'
rows=list(csv.DictReader(p.open(newline='',encoding='utf-8'))); fields=list(rows[0])
snap={x['source_key']:x for x in json.load((root/'output/cvr-source-snapshot.json').open())}
for k in ('image_urls','image_count','image_audit','duplicate_audit'):
 if k not in fields: fields.append(k)
patterns={'swimming-pool':r'pool|swimming','furnished':r'furnished|fully equipped','garden':r'garden|landscaped','balcony':r'balcony','gym':r'\bgym\b|fitness','ocean-view':r'ocean view|sea view|beachfront','river-view':r'river view|riverfront|co co river|han river','parking-space':r'parking|garage|car park','security-guard':r'24/7 security|security guard','elevator':r'elevator|lift','washing-machine':r'washing machine','clothes-dryer':r'dryer'}
s=requests.Session(); s.headers['User-Agent']='Mozilla/5.0'; imgroot=root/'data/cvr-images-2025'; imgroot.mkdir(parents=True,exist_ok=True)
for i,r in enumerate(rows,1):
 text=(r.get('source_description') or '')+' '+(r.get('original_title') or ''); r['property_features']=';'.join(k for k,v in patterns.items() if re.search(v,text,re.I))
 urls=snap.get(r['source_key'],{}).get('image') or []; urls=urls if isinstance(urls,list) else []; d=imgroot/r['source_key']; d.mkdir(parents=True,exist_ok=True); good=0; issues=0
 for j,u in enumerate(urls,1):
  try:
   z=s.get(u,timeout=30,verify=False); z.raise_for_status(); ext='.webp' if 'webp' in z.headers.get('content-type','') or u.endswith('.webp') else '.jpg'; (d/f'{j:02d}{ext}').write_bytes(z.content); w,h=Image.open(BytesIO(z.content)).size; good+=1; issues += int(w<600 or h<400)
  except Exception: pass
 r['image_dir']=str(d.relative_to(root)); r['image_urls']=';'.join(urls); r['image_count']=str(good); r['image_audit']='downloaded_ok' if good and not issues else ('downloaded_with_issues' if good else 'missing'); r['watermark_status']='needs_manual_visual_check' if good else 'missing_images'; r['duplicate_audit']='pending_db_check'; print(i,len(r['property_features'].split(';')) if r['property_features'] else 0,good)
mysql=Path('/Applications/Local.app/Contents/Resources/extraResources/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin/mysql'); socks=list(Path('/Users/vincent/Library/Application Support/Local/run').glob('*/mysql/mysqld.sock')); existing=set()
if mysql.exists() and socks:
 vals=','.join("'"+r['source_key']+"'" for r in rows); q=f"SELECT meta_value FROM wpib_postmeta WHERE meta_key='_hrd_shared_property_source_id' AND meta_value IN ({vals});"; existing=set(subprocess.run([str(mysql),'--socket='+str(socks[0]),'-uroot','-proot','local','-N','-e',q],capture_output=True,text=True).stdout.splitlines())
for r in rows:r['duplicate_audit']='duplicate_source_id_found' if r['source_key'] in existing else 'no_source_id_match'
with p.open('w',newline='',encoding='utf-8') as f:w=csv.DictWriter(f,fieldnames=fields);w.writeheader();w.writerows(rows)
dry={'status':'dry-run-only','rows':len(rows),'duplicate_source_id_matches':len(existing),'image_count':sum(int(r['image_count']) for r in rows),'blocked':[],'records':[]}
for r in rows:
 blocked=[]
 if r['duplicate_audit']=='duplicate_source_id_found':blocked.append('duplicate_source_id')
 if r['image_audit']=='missing':blocked.append('missing_images')
 dry['records'].append({'source_key':r['source_key'],'title':r['rewritten_title'],'type_slug':r['property_type_slug'],'city_terms':r['property_city_term_slugs'],'building_id':r['building_id'],'features':r['property_features'].split(';') if r['property_features'] else [],'price':r['public_price'],'size':r['size'],'images':int(r['image_count']),'duplicate_audit':r['duplicate_audit'],'blocked':blocked})
 if blocked:dry['blocked'].append({'source_key':r['source_key'],'reasons':blocked})
(root/'output/cvr-final-dry-run.json').write_text(json.dumps(dry,ensure_ascii=False,indent=2),encoding='utf-8'); print('DRY RUN blocked',len(dry['blocked']),'duplicates',len(existing),'images',dry['image_count'])
