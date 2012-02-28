<?php

namespace pixelpost\plugins\photo;

use pixelpost\Template;
use pixelpost\plugins\api\Plugin as Api;
use pixelpost\Filter;

// retrieve the page number of paginated photo list
$urlParams = $event->params + array('page:1');

$photoPage = array_shift($urlParams);

if (substr($photoPage, 0, 5) != 'page:') $photoPage = 1;
else
{
	$photoPage = substr($photoPage, 5);
	if (!is_numeric($photoPage)) $photoPage = 1;
	else                         $photoPage = intval($photoPage);
}

// retrieve photo at page $photoPage
$fields  = array('id', 'title', 'publish-date', 'visible', 'thumb-url');
$pager   = array('page' => $photoPage, 'max-per-page' => 10);
$sort    = array('publish-date' => 'desc');
$request = compact('fields', 'pager', 'sort');

$photos  = Api::call_api_method('photo.list', $request);
$msize   = Api::call_api_method('upload.max-size');

// load the template
$tpl = Template::create()
	->assign('photos', $photos['photo'])
	->assign('post_max_size', $msize['max_size'])
	->publish('photo/tpl/admin/home.php');
