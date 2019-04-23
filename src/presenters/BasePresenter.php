<?php

use Nette\Application\UI;

abstract class BasePresenter extends UI\Presenter
{
	public $configParams = array();
	public $contrastOptions = array();
	public $menu = "";
	public $categories = [];
	public $bottom_menu = "";
	public $breadcrumbs = "";
	public $hostName = "";
	public $lang;

	public $enabledLanguages = [];
	public $roles = [];

	public function startup()
	{
		parent::startup();

		$this->configParams = $this->context->getParameters();

		$defaultRoles = [
			3 => 'Administrátor',
			4 => 'Editor'
		];

		$this->template->systemImage = $this->configParams["application"]["systemImage"] ?? "img-29.jpg";
		$this->template->systemName = $this->configParams["application"]["systemName"] ?? "Contrast CMS";
		$this->template->systemNameColor = $this->configParams["application"]["systemNameColor"] ?? "#ffffff";
		$this->template->systemClaim = $this->configParams["application"]["systemClaim"] ?? "Mangaing your website have never been easier.";
		$this->roles = $this->configParams["application"]["roles"] ?? $defaultRoles;

		$this->enabledLanguages = [
			"cs_CZ" => "Česky"
		];

		if(isset($this->configParams["application"]["enable_english"]) && $this->configParams["application"]["enable_english"]) {
			$this->enabledLanguages['en_US'] = 'English';
		}

		$this->template->language = $this->lang = $this->getParameter("lang", 'cs_CZ');

		// Load default variables / options
		$options = $this->context->getService("optionRepository");
		$applicationOptions = $options->findAll()->where(array(
			'lang' => $this->lang
		));

		foreach ($applicationOptions as $option) {
			$this->contrastOptions[$option->key] = $option->value;
		}

		// Sitename
		$this->template->siteName = "Contrast CMS 4.5";
		if (isset($this->contrastOptions['site_name']) && trim($this->contrastOptions['site_name']) != "") {
			$this->template->siteName = $this->contrastOptions['site_name'];
		}

		// Keywords
		$this->template->defaultKeywords = "";
		if (isset($this->contrastOptions['default_keywords']) && trim($this->contrastOptions['default_keywords']) != "") {
			$this->template->defaultKeywords = $this->contrastOptions['default_keywords'];
		}

		// Description
		$this->template->defaultDescription = "";
		if (isset($this->contrastOptions['default_description']) && trim($this->contrastOptions['default_description']) != "") {
			$this->template->defaultDescription = $this->contrastOptions['default_description'];
		}

		$this->template->facebook = "";
		if (isset($this->contrastOptions['facebook']) && trim($this->contrastOptions['facebook']) != "") {
			$this->template->facebook = $this->contrastOptions['facebook'];
		}

		$this->template->twitter = "";
		if (isset($this->contrastOptions['twitter']) && trim($this->contrastOptions['twitter']) != "") {
			$this->template->twitter = $this->contrastOptions['twitter'];
		}

		// Version
		$this->template->version = "1";
		if (isset($this->contrastOptions['version']) && trim($this->contrastOptions['version']) != "") {
			$this->template->version = $this->contrastOptions['version'];
		}

		$this->template->enabledLanguages = $this->enabledLanguages;
		$this->template->attachmentsProvider = $this->context->getService("postAttachmentRepository");
		$this->template->files = $this->context->getService("fileRepository");

		$this->template->addFilter("htmlTruncate", '\ContrastCms\Application\StringHelper::htmlTruncate');
		$this->template->addFilter("getYoutubeId", function ($string) {
			if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $string, $match)) {
				$video_id = $match[1];
				return $video_id;
			}

			return false;
		});
	}

	public function getSettingsItem($keyName)
	{

		$value = "";
		if (isset($this->contrastOptions[$keyName]) && trim($this->contrastOptions[$keyName]) != "") {
			$value = $this->contrastOptions[$keyName];
		}

		return $value;
	}

	public function getDataFromSettings($key, $strict = false)
	{
		if (isset($this->contrastOptions[$key]) && trim($this->contrastOptions[$key]) != "") {
			return $this->contrastOptions[$key];
		} else {
			if ($strict) {
				return false;
			} else {
				return "";
			}
		}
	}
}