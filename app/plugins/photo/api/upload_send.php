<?php

namespace app\plugins\photo;

use pixelpost\plugins\auth\Plugin   as Auth,
	pixelpost\plugins\api\Plugin    as Api,
	pixelpost\plugins\api\Exception as ApiError;


// grants checks
if (!Auth::is_granted('write')) throw new ApiError\Ungranted('upload.send');

// input checks requirement
if (!isset($event->request->id))    throw new ApiError\FieldRequired('upload.send', 'name');
if (!isset($event->request->chunk)) throw new ApiError\FieldRequired('upload.send', 'chunk');
if (!isset($event->request->data))  throw new ApiError\FieldRequired('upload.send', 'data');

$id    = trim($event->request->id);
$chunk = filter_var($event->request->chunk, FILTER_VALIDATE_INT);
$data  = base64_decode(trim($event->request->data));

// verify path injection against $id
$is_id = (bool) preg_match('/^up_[\w\._-]+$/', $id);

// input validation
if ($is_id === false) throw new ApiError\FieldNotValid('id',   'not a valid id');
if ($chunk === false) throw new ApiError\FieldNotValid('id',   'not an integer');
if ($data  === false) throw new ApiError\FieldNotValid('data', 'not base64');

// where are stored uploads
$path = PRIV_PATH . SEP . 'upload';

// the metadata file name
$fname  = $path . SEP . $id;

// where are stored the chunks
$fchunk = $fname . '_chunks' . SEP . 'chunk_' . $chunk;

// check if it's a valid id
if (!file_exists($fname)) throw new ApiError\FieldNonExists('id');

// registers metadata (store the chunk in its base64 form not in binary)
file_put_contents($fchunk, trim($event->request->data), false);

// return the file id
$event->response = array('message' => 'uploaded');
