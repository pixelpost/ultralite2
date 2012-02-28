<?php

namespace app\plugins\photo;

use pixelpost\plugins\auth\Plugin   as Auth,
	pixelpost\plugins\api\Plugin    as Api,
	pixelpost\plugins\api\Exception as ApiError;

// grants checks
if (!Auth::is_granted('write')) throw new ApiError\Ungranted('upload.init');

// input checks
if (!isset($event->request->name))   throw new ApiError\FieldRequired('upload.init', 'name');
if (!isset($event->request->size))   throw new ApiError\FieldRequired('upload.init', 'size');
if (!isset($event->request->type))   throw new ApiError\FieldRequired('upload.init', 'type');
if (!isset($event->request->chunks)) throw new ApiError\FieldRequired('upload.init', 'chunks');

$type   = trim($event->request->type);
$name   = trim($event->request->name);
$size   = filter_var($event->request->size,   FILTER_VALIDATE_INT);
$chunks = filter_var($event->request->chunks, FILTER_VALIDATE_INT);

if ($size === false)   throw new ApiError\FieldNotValid('size', 'not an integer');
if ($chunks === false) throw new ApiError\FieldNotValid('chunks', 'not an integer');


if (!isset(self::$valid_mime[$type]))
{
	throw new ApiError\FieldNotInList('type', array_keys(self::$valid_mime));
}

// Test cohesion between file size and chunks number
extract(Api::call_api_method('upload.max-size')); // set $max_size

$file_max_size = $max_size * $chunks * 3 / 4; // 3/4 is base64 -> bytes

if ($file_max_size < $size)
{
	$msg = 'With %d chunks max file size is %d bytes at most';
	$msg = sprintf($msg, $chunks, $file_max_size);

	throw new ApiError\FieldNotValid('size', $msg);
}

// where are stored uploads
$path = PRIV_PATH . SEP . 'upload';

// the metadata filename
$fname = tempnam($path, 'up_');

// the file id
$id = basename($fname);

// containers for the future chunks
mkdir($fname . '_chunks', 0775);

// the metadata
$metadata = json_encode(compact('type', 'size', 'chunks', 'name', 'expire'));

// registers metadata
file_put_contents($fname, $metadata);

// return the file id
$event->response = compact('id');
