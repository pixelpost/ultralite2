<?php

namespace pixelpost\plugins\photo;

use Exception,
	pixelpost\core\Template,
	pixelpost\core\Filter,
	pixelpost\plugins\api\Plugin as Api,
	pixelpost\plugins\api\Exception\Ungranted;

// retrieve the page number of paginated photo list
$urlParams = $event->params + array('page:1');

$photoPage = array_shift($urlParams);

if (mb_substr($photoPage, 0, 5) != 'page:') $photoPage = 1;
else
{
	$photoPage = mb_substr($photoPage, 5);
	if (!is_numeric($photoPage)) $photoPage = 1;
	else                         $photoPage = intval($photoPage);
}

// retrieve photo at page $photoPage
$fields  = array('id', 'title', 'publish-date', 'visible', 'thumb-url');
$pager   = array('page' => $photoPage, 'max-per-page' => 10);
$sort    = array('publish-date' => 'desc');
$request = compact('fields', 'pager', 'sort');

$photos  = Api::call('photo.list', $request);

$is_upload = true;

// if user does not have a right access API Ungranted exception is thrown
// and cactched by Api::call.
// In this case we disable upload
try
{
	$msize = Api::call('upload.max-size');
}
catch(Exception $e)
{
	if ($e->getPrevious() instanceof Ungranted)
	{
		$msize = array('max_size' => 0);
		$is_upload = false;
	}
	else throw $e;
}

// load the template
$tpl = Template::create()
	->assign('photos', $photos['photo'])
	->assign('is_upload', $is_upload)
	->assign('post_max_size', $msize['max_size'])
	->publish('photo/tpl/admin/home.tpl');
