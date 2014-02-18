<?php
/**
 * mm_ddGMap
 * @version 1.1.1 (2012-11-13)
 * 
 * @desc Widget for ManagerManager plugin allowing Google Maps integration.
 * 
 * @uses ManagerManager plugin 0.4.
 * 
 * @param $tvs {comma separated string} - TV names to which the widget is applied. @required
 * @param $roles {comma separated string} - The roles that the widget is applied to (when this parameter is empty then widget is applied to the all roles). Default: ''.
 * @param $templates {comma separated string} - Id of the templates to which this widget is applied (when this parameter is empty then widget is applied to the all templates). Default: ''.
 * @param $w {'auto'; integer} - Width of the map container. Default: 'auto'.
 * @param $h {integer} - Height of the map container. Default: 400.
 * @param $hideField {0; 1} - Original coordinates field hiding status (1 — hide, 0 — show). Default: 1.
 * 
 * @link http://code.divandesign.biz/modx/mm_ddgmap/1.1.1
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.biz
 */

function mm_ddGMap($tvs, $roles = '', $templates = '', $w = 'auto', $h = '400', $hideField = true){
	global $modx, $content, $mm_fields, $modx_lang_attribute;
	$e = &$modx->Event;
	
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = '';
		
		// if we've been supplied with a string, convert it into an array
		$tvs = makeArray($tvs);
		
		// Which template is this page using?
		if (isset($content['template'])){
			$page_template = $content['template'];
		}else{
			// If no content is set, it's likely we're adding a new page at top level.
			// So use the site default template. This may need some work as it might interfere with a default template set by MM?
			$page_template = $modx->config['default_template'];
		}
		
		$tvs = tplUseTvs($page_template, $tvs);
		if ($tvs == false){
			return;
		}
		
		$style = 'width: '.$w.'px; height: '.$h.'px; position: relative; border: 1px solid #c3c3c3;';
		// We always put a JS comment, which makes debugging much easier
		$output .= "//  -------------- mm_ddGMap :: Begin ------------- \n";
		
		// Do something for each of the fields supplied
		foreach ($tvs as $tv){
			// If it's a TV, we may need to map the field name, to what it's ID is.
			// This can be obtained from the mm_fields array
			$tv_id = 'tv'.$tv['id'];
			$output .= '
//TV с координатами
var coordFieldId = "'.$tv_id.'", $coordinatesField = $j("#" + coordFieldId);
//Координаты
var ddLatLng = $coordinatesField.val();

//Родитель
var $coordFieldParent = $coordinatesField.parents("tr:first");
//Запоминаем название поля
var sectionName = $coordFieldParent.find(".warning").text();

//Скрываем родителя и разделитель
$coordFieldParent.hide().prev("tr").hide();

//Контейнер для карты
var $sectionConteiner = $j("<div class=\"sectionHeader\">" + sectionName + "</div><div class=\"sectionBody tmplvars\"><div class=\"ddGMap" + coordFieldId + "\" style=\"'.$style.'\"></div></div>");
//Добавляем контейнер
$coordinatesField.parents(".tab-page:first").append($sectionConteiner);

//Если скрывать не надо, засовываем перед картой
if (!'.intval($hideField).'){
	$coordinatesField.insertBefore(".ddGMap" + coordFieldId);
}

//Если координаты не заданны, то задаём дефолт
if(ddLatLng == "") ddLatLng = "55.19396010947335,61.3670539855957";
ddLatLng = ddLatLng.split(",");

//Callback функция для GM
window.ddgminitialize = function(){
	var GM = google.maps;
	var myOptions = {
		zoom: 15,
		center: new GM.LatLng(ddLatLng[0],ddLatLng[1]),
		mapTypeId: GM.MapTypeId.ROADMAP,
		streetViewControl: false,
		scrollwheel: false
	};
	var map = new GM.Map($sectionConteiner.find(".ddGMap" + coordFieldId).get(0), myOptions);
	//Добавляем маркер на карту
	var GMMarker = new GM.Marker({
		position: new GM.LatLng(ddLatLng[0],ddLatLng[1]),
		map: map,
		draggable: true
	});
	//При перетаскивании маркера
	GM.event.addListener(GMMarker, "drag", function(event){
		var position = event.latLng;//Координаты
		$coordinatesField.val(position.lat() + "," + position.lng());//Сохраняем значение в поле
	});
	//При клике на карте
	GM.event.addListener(map, "click", function(event){
		var position = event.latLng;//Новые координаты
		GMMarker.setPosition(position);//Меняем позицию маркера
		map.setCenter(position);//Центрируем карту на маркере
		$coordinatesField.val(position.lat() + "," + position.lng());//Сохраняем значение в поле
	});
};
//Подключаем карту, вызываем callback функцию
$j(window).on("load.ddEvents", function(){
	$j("body").append("<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=false&hl='.$modx_lang_attribute.'&callback=ddgminitialize\">");
});
';
		}
		$output .= "//  -------------- mm_ddGMap :: End ------------- \n";
		
		$e->output($output . "\n");
	}
}
?>