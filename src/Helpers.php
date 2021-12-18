<?php
function approvalDate($dateP, $time = false){
	try {
		$date = new \DateTime($dateP);
	} catch (\Exception $e) {
		return $dateP;
	}
	return $date->format(env('APP_DATE_FORMAT','d/M/Y') . (($time) ? env('APP_TIME_FORMAT',' H:i:s') : ''));
}

function approvalFileName($name, $withExt = 1, $prefix = NULL, $suffix = NULL)
{
	$extension = pathinfo($name, PATHINFO_EXTENSION);
	$name = preg_replace("/[\s]|\#|\$|\&|\@|\%|\^/", "-", pathinfo($name, PATHINFO_FILENAME));
	$name = $prefix.'_'. $name.'_'.$suffix;
	if($prefix == NULL){
		$name = substr($name, -200);
	}
	else{
		$name = substr($name, 0, 200);
	}

	$name .= ('_' . time());

	if($withExt){
		$name .= ('.'.$extension);
	}

	return $name;
}

function namespacePath($file){
	$filePath = str_replace(app_path(), 'App', $file);
	return str_replace('/','\\',$filePath);
}

function namespaceBasePath($file){
	return $file;
	$base_name = explode('\\',$file);
	return end($base_name);
}
?>