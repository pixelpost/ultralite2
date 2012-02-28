<?php

namespace app\plugins\photo;

use pixelpost\plugins\auth\Plugin   as Auth,
	pixelpost\plugins\api\Plugin    as Api,
	pixelpost\plugins\api\Exception as ApiError,
	RecursiveIteratorIterator       as RII,
	RecursiveDirectoryIterator      as RDI;

// grants checks
if (!Auth::is_granted('write')) throw new ApiError\Ungranted('upload.end');

// input checks requirement
if (!isset($event->request->id))    throw new ApiError\FieldRequired('upload.end', 'name');

$id = trim($event->request->id);

// verify path injection against $id
$is_id = (bool) preg_match('/^up_[\w\._-]+$/', $id);

// input validation
if ($is_id === false) throw new ApiError\FieldNotValid('id', 'not a valid id');

// where are stored uploads
$path = PRIV_PATH . SEP . 'upload';

// the metadata file name
$fname  = $path . SEP . $id;

//  chunk filename base
$chunks = $fname . '_chunks' . SEP . 'chunk_';

// check if it's a valid id
if (!file_exists($fname)) throw new ApiError\FieldNonExists('id');

// acquiere the file by moving him (sort of simple atomic lock)
if (!rename($fname, $fname . '_final')) throw new ApiError\Internal('cannot lock the file');

// read the metadata
$metadata = json_decode(file_get_contents($fname . '_final'));

// delete the metadata file
unlink($fname . '_final');

// create the final file
$file = $path . SEP . 'image_' . uniqid() . self::$valid_mime[$metadata->type];

// open the file in binary mode, push each chunks in and close.
$fp = fopen($file, 'wb');

 // we abort if file can't be open
$aborted = ($fp === false);

for($i = 1; !$aborted && $i <= $metadata->chunks; ++$i)
{
	$chunk   = $chunks . $i;
	$noexist = ! file_exists($chunk);
	$aborted = $noexist || ! fwrite($fp, base64_decode(file_get_contents($chunk)));
}

fclose($fp);

// clean chunks
foreach(new RII(new RDI($fname . '_chunks'), RII::CHILD_FIRST) as $fd)
{
	$method = $fd->isDir() ? 'rmdir' : 'unlink';
	$method($fd->getPathName());
}

rmdir($fname . '_chunks');

// if we had a problem
if ($aborted)
{
	is_file($file) && unlink($file);
	throw new ApiError\Internal('chunks is missing or IO error');
}

// check the file size
if ($metadata->size != $size = filesize($file))
{
	$msg = 'incorrect file size: %d instead of %d';

	throw new ApiError\Internal(sprintf($msg, $size, $metadata->size));
}

// return the file name in response
$event->response = compact('file');
