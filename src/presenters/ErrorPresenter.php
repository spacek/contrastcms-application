<?php

use Nette\Diagnostics\Debugger;



/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

    public function startup()
    {

		parent::startup();

		$this->lang = $this->_getLanguage();

		if(!in_array($this->lang, array_keys($this->enabledLanguages), true)) {
			$this->lang = "en_US";
		}

		$this->template->language = $this->lang;
    }

    /**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = TRUE;
			$this->terminate();

		} elseif ($exception instanceof Nette\Application\BadRequestException) {
			$code = $exception->getCode();

			// get path

            $redirect = $this->context->getService("redirectRepository")->find()->where("url = ?", $_SERVER["REQUEST_URI"])->where("lang = ?", $this->lang)->fetch();
            if($redirect) {
                $this->redirectUrl($redirect->new, 301); exit;
            }


			// load template 403.latte or 404.latte or ... 4xx.latte
			$this->setView(in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx');

			if(!in_array($this->lang, $this->enabledLanguages, true)) {
			    $this->lang = "en_US";
            }

			// log to access.log
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		} else {
			$this->setView('500'); // load template 500.latte
			Debugger::log($exception, Debugger::ERROR); // and log exception
		}
	}

}
