<?php

namespace ContrastCms\Application;

use Nette\Http\FileUpload;
use Nette\Utils\Image;

class FileRepository extends Repository
{

	const PATH = "./data/";
	const PUBLIC_PATH = "/data/";

	public function storeFile(FileUpload $fileInput, $type = "image", $folder = "")
	{

		$name = date("Y-m-d-h-i-s") . "-" . $fileInput->getSanitizedName();
		$fileInput->move(self::PATH . $folder . $name);

		// Store

		$id = $this->insert(array(
			'filename' => $name,
			'type' => $type
		));

		if ($id) {
			return $id;
		}

		return 0;
	}

	public function storeFileFromURL($url, $type = "image", $ext = "jpg")
	{

		$name = date("Y-m-d-h-i-s") . "-" . md5($url) . "." . $ext;
		$file = fopen(self::PATH . $name, "w+");
		fwrite($file, file_get_contents($url));
		fclose($file);


		// Store

		$id = $this->insert(array(
			'filename' => $name,
			'type' => $type
		));

		if ($id) {
			return $id;
		}

		return 0;
	}


	public function getFilename($id)
	{
		$row = $this->findById($id);

		if (!$row) {
			return "";
		}

		$row = $row->fetch();
		if ($row) {
			return $row->filename;
		}
		return "";
	}


	public function getFileType($id)
	{
		$row = $this->findById($id)->fetch();
		return $row->type ?? "file";
	}

	public function getFilenameResized($id, $width = null, $height = null, $cropPosition = "center")
	{
		$row = $this->findById($id);

		if (!$row) {
			return "";
		}

		$row = $row->fetch();

		if (!$row) {
			return "";
		}

		// Create prefix

		if ($width == null && $height == null) {
			return $row->filename;
		}

		$prefix = "";
		if ($width !== null) {
			$prefix .= "w" . $width;
		}

		if ($height !== null) {
			$prefix .= "h" . $height;
		}

		$prefix .= "_";

		if (file_exists("./data/" . $prefix . $row->filename)) {
			return $prefix . $row->filename;
		}

		// Create thumb and save it
		$newFile = $prefix . $row->filename;

		if (!file_exists("./data/" . $row->filename)) {
			return "";
		}

		try {
			$image = Image::fromFile("./data/" . $row->filename);
			if (isset($width) && $width > 0 && isset($height) && $height > 0) {
				if ($cropPosition == "center") {
					$image->resize($width, $height, $image::EXACT);
				} elseif ($cropPosition == "top") {
					$image->resize($width, $height, $image::EXACT);
				}

			} else {
				$image->resize($width, $height, $image::FIT);
			}

			$image->sharpen();
			$image->save("./data/" . $newFile, 100);
		} catch (\Exception $e) {
			error_log($e->getMessage());
		}


		return $newFile;
	}


	public function storeFileFromString($string, $type = "image", $ext = "jpg")
	{

		$name = date("Y-m-d-h-i-s") . "-" . md5($string) . "." . $ext;
		$file = fopen(self::PATH . $name, "w+");
		fwrite($file, $string);
		fclose($file);

		$id = $this->insert(array(
			'filename' => $name,
			'type' => $type
		));

		if ($id) {
			return $id;
		}

		return 0;
	}

	public function storeImage(FileUpload $fileInput, $type = "image")
	{

		$name = date("Y-m-d-h-i-s") . "-" . $fileInput->getSanitizedName();
		$fileInput->move(self::PATH . $name);

		// Store

		$id = $this->insert(array(
			'filename' => $name,
			'type' => $type
		));

		if ($id) {
			return $id;
		}

		return 0;
	}

	public function getImages()
	{
		return $this->findBy(array("type" => "image"), "id DESC");
	}
}