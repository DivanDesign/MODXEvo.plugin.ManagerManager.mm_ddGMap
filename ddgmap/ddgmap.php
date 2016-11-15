<?php
/**
 * mm_ddGMap
 * @version 1.2b (2014-05-14)
 * 
 * @desc Widget for ManagerManager plugin allowing Google Maps integration.
 * 
 * @uses PHP >= 5.4.
 * @uses MODXEvo.plugin.ManagerManager >= 0.7.
 * 
 * @param $params {array_associative|stdClass} — The object of params. @required
 * @param $params['fields'] {string_commaSeparated} — TV names to which the widget is applied. @required
 * @param $params['APIkey'] {string} — Google Maps API key. @required
 * @param $params['mapWidth'] {'auto'|integer} — Width of the map container. Default: 'auto'.
 * @param $params['mapHeight'] {integer} — Height of the map container. Default: 400.
 * @param $params['hideOriginalInput'] {boolean} — Original coordinates field hiding status (1 — hide, 0 — show). Default: 1.
 * @param $params['roles'] {string_commaSeparated} — The roles that the widget is applied to (when this parameter is empty then widget is applied to the all roles). Default: ''.
 * @param $params['templates'] {string_commaSeparated} — Id of the templates to which this widget is applied (when this parameter is empty then widget is applied to the all templates). Default: ''.
 * 
 * @link http://code.divandesign.biz/modx/mm_ddgmap/1.2b
 * 
 * @copyright 2012–2016 DivanDesign {@link http://www.DivanDesign.biz }
 */

function mm_ddGMap($params){
	//For backward compatibility
	if (
		!is_array($params) &&
		!is_object($params)
	){
		//Convert ordered list of params to named
		$params = ddTools::orderedParamsToNamed([
			'paramsList' => func_get_args(),
			'compliance' => [
				'fields',
				'roles',
				'templates',
				'mapWidth',
				'mapHeight',
				'hideOriginalInput',
				'APIkey'
			]
		]);
	}
	
	//Defaults
	$params = (object) array_merge([
// 		'fields' => '',
// 		'APIkey' => '',
		'mapWidth' => 'auto',
		'mapHeight' => 400,
		'hideOriginalInput' => true,
		'roles' => '',
		'templates' => ''
	], (array) $params);
	
	if (!useThisRule($params->roles, $params->templates)){return;}
	
	global $modx;
	$e = &$modx->Event;
	
	if ($e->name == 'OnDocFormPrerender'){
		global $modx_lang_attribute;
		
		//The main js file including
		$output = includeJsCss($modx->config['site_url'].'assets/plugins/managermanager/widgets/ddgmap/jQuery.ddMM.mm_ddGMap.js', 'html', 'jQuery.ddMM.mm_ddGMap', '1.1');
		//The Google.Maps library including
		$output .= includeJsCss('http://maps.google.com/maps/api/js?sensor=false&hl='.$modx_lang_attribute.'&key='.$params->APIkey.'&callback=mm_ddGMap_init', 'html', 'maps.google.com', '0');
		
		$e->output($output);
	}else if ($e->name == 'OnDocFormRender'){
		global $mm_current_page;
		
		$output = '';
		$params->fields = makeArray($params->fields);
		
		$usedTvs = tplUseTvs($mm_current_page['template'], $params->fields, '', 'id', 'name');
		if ($usedTvs == false){return;}
		
		$output .= '//---------- mm_ddGMap :: Begin -----'.PHP_EOL;
		
		//Iterate over supplied TVs instead of doing so to the result of tplUseTvs() to maintain rendering order.
		foreach ($params->fields as $field){
			//If this $field is used in a current template
			if (isset($usedTvs[$field])){
				$output .= 
'
$j("#tv'.$usedTvs[$field]['id'].'").mm_ddGMap({
	hideField: '.intval($params->hideOriginalInput).',
	width: "'.$params->mapWidth.'",
	height: "'.$params->mapHeight.'"
});
';
			}
		}
		
		$output .= '//---------- mm_ddGMap :: End -----'.PHP_EOL;
		
		$e->output($output);
	}
}
?>