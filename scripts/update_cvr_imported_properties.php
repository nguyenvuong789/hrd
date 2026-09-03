<?php
declare(strict_types=1);
$wp_root='/Users/vincent/Local Sites/hrd/app/public'; require $wp_root.'/wp-load.php';
$root=dirname(__DIR__); $h=fopen($root.'/output/rewrite-master-enriched.csv','r'); $heads=fgetcsv($h); $rows=[]; while(($v=fgetcsv($h))!==false){if(count($v)===count($heads))$rows[]=array_combine($heads,$v);} fclose($h);
$centroids=['Da Nang'=>[16.0544,108.2022],'Son Tra'=>[16.0678,108.2235],'Ngu Hanh Son'=>[16.0078,108.2636],'Hai Chau'=>[16.0471,108.2068],'An Thuong'=>[16.0488,108.2440],'My An'=>[16.0415,108.2450],'Hoa Xuan'=>[15.9970,108.2190]];
$done=0;
foreach($rows as $r){$ids=get_posts(['post_type'=>'property','post_status'=>'any','fields'=>'ids','numberposts'=>1,'meta_key'=>'_hrd_shared_property_source_id','meta_value'=>$r['source_key']]); if(!$ids)continue; $id=(int)$ids[0];
 delete_post_meta($id,'REAL_HOMES_property_id'); delete_post_meta($id,'REAL_HOMES_property_old_price'); update_post_meta($id,'_hrd_property_code','HRD-'.substr($r['source_key'],0,8));
 $area=trim($r['property_area']?:'Da Nang'); $label='Da Nang'.($area!=='Da Nang'?', '.$area.' area':''); update_post_meta($id,'REAL_HOMES_property_address',$label.', Da Nang');
 $c=$centroids[$area]??$centroids['Da Nang']; update_post_meta($id,'REAL_HOMES_property_location',sprintf('%.6f,%.6f,12',$c[0],$c[1])); update_post_meta($id,'_hrd_location_basis','relative area centroid; exact unit address not supplied'); $done++;
}
echo "Updated {$done} imported properties\n";
