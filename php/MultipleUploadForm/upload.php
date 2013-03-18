<?php 
// Set the path (relative or absolute) to the directory to save image files
define('UPLOAD_PATH', '../js/upload/server/php/files');
// Set the URL (relative or absolute) to the directory defined above
define('UPLOAD_URI', '../js/upload/server/php/files');

function uploadFiles() {
	$upload_allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'txt', 'svg', 'pdf', 'doc', 'xls', 'html', 'htm', 'zip', 'rar');

	if (!is_dir(UPLOAD_PATH)) {
		upload_error('Upload directory ' . UPLOAD_PATH . ' must exist on the server');
	}
	if (!is_writable(UPLOAD_PATH)) {
		upload_error('Upload directory ' . UPLOAD_PATH . ' must have write permissions on the server');
	}

	$status = array();
	$files = $_FILES['attachment'];
	for ($i = 0; $i < count($files['name']); $i++) {
		if (!in_array($files['error'][$i], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
			upload_error('Upload failed');
		}
		if ($files['error'][$i] == UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$max_upload_size = ini_max_upload_size();
		$ext = strtolower(substr(strrchr($files['name'][$i], '.'), 1));
		if (!in_array($ext, $upload_allowed_extensions)) {
			upload_error('Invalid file ' . $files['name'][$i] . ', must be a valid file less than ' . bytes_to_readable($max_upload_size));
		}
		$filename = uniqueId() . '.' . $ext;
		$path = UPLOAD_PATH . '/' . $filename;
		if (!move_uploaded_file($files['tmp_name'][$i], $path)) {
			upload_error('Server error, failed to move file');
		}
		$result['file']['size'] = $files['size'][$i];
		$result['file']['name'] = $files['name'][$i];
		$result['links']['original'] = upload_file_uri($filename);
		$result['info'] = $_POST['attachment-info'][$i];
		$status['upload'][] = $result;
	}
	$status['done'] = 1;
	// $status['files'] = print_r($_FILES, true);
	// $status['post'] = print_r($_POST, true);

	upload_output($status);
}

function upload_error($msg) {
	$status = array();
	$status['error']['message'] = $msg;
	$status['error']['format'] = 'json';
	upload_output($status);
}

function upload_output($status) {
	echo "<script language='javascript' type='text/javascript'>try { parent.uploadComplete('" . $_POST['upload-form-name'] . "', '"
		. json_encode($status) . "'); } catch (e) { alert(e.message); }</script>";
	exit;
}

function upload_file_uri($filename, $override_uri = '') {
	$prefix = strlen($override_uri) > 0 ? $override_uri : UPLOAD_URI;
	return $prefix . '/' . $filename;
}

function ini_max_upload_size() {
	$post_size = ini_get('post_max_size');
	$upload_size = ini_get('upload_max_filesize');
	if (!$post_size) $post_size = '8M';
	if (!$upload_size) $upload_size = '2M';
	return min(ini_bytes_from_string($post_size), ini_bytes_from_string($upload_size));
}

function ini_bytes_from_string($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	switch ($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

function bytes_to_readable($bytes) {
	if ($bytes <= 0) {
		return '0 Byte';
	}
	$convention = 1000; // [1000 -> 10^x | 1024 -> 2^x]
	$s = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
	$e = floor(log($bytes, $convention));
	return round($bytes / pow($convention, $e), 2) . ' ' . $s[$e];
}

function uniqueId($length = 40) {
	return md5(randomString($length) . microtime());
}

function randomString($length) {
	$pool = "ABCDEFGHIJKMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz";
	$sid = "";
	for ($index = 0; $index < $length; $index++) {
		$sid .= substr($pool, rand() % (strlen($pool)), 1);
	}
	return $sid;
}

function saveForm() {
	$savedData = "Saved title='" . $_POST['title'] . "'\n\n";
	if (strlen($_POST['attachments']) > 0) {
		$att = json_decode($_POST['attachments']);
		if ($att && is_array($att->{'upload'}) && count($att->{'upload'}) > 0) {
			for ($i = 0; $i < count($att->{'upload'}); $i++) {
				$savedData .= "Saved [" . ($i + 1) . "] file='" . $att->{'upload'}[$i]->{'links'}->{'original'} . "', name='"
					. $att->{'upload'}[$i]->{'file'}->{'name'} . "', description='" . $att->{'upload'}[$i]->{'info'} . "'\n";
			}
		}
	}
	echo $savedData;
}

switch ($_GET['func']) {
	case 'uploadFiles':
		uploadFiles();
		break;
	case 'saveForm':
		saveForm();
		break;
	default:
		break;
}
?>
