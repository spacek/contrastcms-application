<?php

class EshopPresenter extends BasePresenter
{
	/** @persistent */
	public $sort_by = "id";

	/** @persistent */
	public $sort = "desc";

	public function startup()
	{
		parent::startup();
		$this->template->categories = $this->context->getService("crudRepository")->getTable("categories")->where("id", [1]);
		$this->template->sort_by = $this->sort_by;
		$this->template->sort = $this->sort;
		$this->template->isB2b = false;
	}

	public function actionDefault($category = 1)
	{
		$selection = $this->getBasicProductsSelection()->where("(category_id = ? OR subcategory_id = ?)", [$category, $category]);
		$this->template->bodyClass = "has-submenu";

		// Pagination
		$vp = new \FrontendPaginator();
		$vp->loadState($this->request->getParameters());
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 12;
		$paginator->setPage($this->request->getParameter("vp-page"));
		$paginator->itemCount = $selection->count();
		$this->template->products = $selection->order("`" . $this->sort_by . "` " . strtoupper($this->sort))->limit($paginator->itemsPerPage, $paginator->offset);

		$this->addComponent($vp, "vp");

		$this->template->category = $this->context->getService("crudRepository")->getTable("categories")->where("id = ?", $category)->fetch();
		$this->template->productCount = $paginator->itemCount;
	}

	public function actionSearch($q = null)
	{
		$selection = $this->getBasicProductsSelection()->where("name LIKE ?", '%' . $q . '%');
		$this->template->bodyClass = "has-submenu";

		// Pagination
		$vp = new \FrontendPaginator();
		$vp->loadState($this->request->getParameters());
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 12;
		$paginator->setPage($this->request->getParameter("vp-page"));
		$paginator->itemCount = $selection->count();
		$this->template->products = $selection->order("`" . $this->sort_by . "` " . strtoupper($this->sort))->limit($paginator->itemsPerPage, $paginator->offset);

		$this->addComponent($vp, "vp");
		$this->template->productCount = $paginator->itemCount;
		$category = new stdClass();
		$category->name = "Search results";
		$this->template->category = $category;


		$this->setView("default");
	}

	public function actionDetail($id)
	{
		$this->template->product = $product = $this->getBasicProductsSelection()->where("id = ?", $id)->fetch();
		$this->template->previousProduct = $this->getBasicProductsSelection()->where("id < ?", $id)->order("id DESC")->fetch();
		$this->template->nextProduct = $this->getBasicProductsSelection()->where("id > ?", $id)->order("id ASC")->fetch();
		$this->template->variants = $this->context->getService("crudRepository")->getTable("eshop_variants")->where("parent_id = ?", $id);
		$this->template->category = $this->context->getService("crudRepository")->getTable("categories")->where("id = ?", $product->category_id)->order("id DESC")->fetch();
		$this->template->subcategory = $this->context->getService("crudRepository")->getTable("categories")->where("id = ?", $product->subcategory_id)->order("id DESC")->fetch();
		$this["addToCart"]->setValues([
			"product_id" => $id
		]);
	}

	public function actionCart()
	{

	}

	public function actionDecreaseQty($product)
	{

		/** @var \Contrast\ShoppingCart */
		$shoppingCart = $this->context->getService("shoppingCart");

		$shoppingCart->decreaseQty($product);
		$this->redirect("Eshop:cart");
	}

	public function actionIncreaseQty($product)
	{
		/** @var \Contrast\ShoppingCart */
		$shoppingCart = $this->context->getService("shoppingCart");

		$shoppingCart->increaseQty($product);
		$this->redirect("Eshop:cart");
	}

	public function actionRemoveFromCart($id)
	{

		/** @var \Contrast\ShoppingCart */
		$shoppingCart = $this->context->getService("shoppingCart");
		$shoppingCart->remove($id);

		if ($this->isAjax()) {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($shoppingCart->getResponseObject()));
		}

		$this->flashMessage("Zboží odebráno z košíku.");
		$this->redirect("Eshop:cart");
	}

	public function actionSignIn()
	{

	}

	public function actionSignUp()
	{

	}

	public function actionCheckout()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this["checkoutForm"]->setDefaults((array)$this->getUser()->getIdentity()->data + ["email_confirmation" => $this->getUser()->getIdentity()->data["email"]]);
		}
	}

	public function createComponentCheckoutForm()
	{
		$form = new \Nette\Application\UI\Form();

		$form->addText("name", "Jméno")->setRequired("Zadejte prosím jméno");
		$form->addText("phone", "Telefonní číslo")->setRequired("Zadejte prosím telefonní číslo");
		$form->addEmail("email", "E-mail")->setRequired("Zadejte prosím e-mail")->addRule($form::EMAIL, "Zadejte platnou e-mailovou adresu");
		$form->addText("street", "Ulice")->setRequired("Zadejte prosím ulici");
		$form->addText("city", "Město")->setRequired("Zadejte prosím město");
		$form->addText("zip", "PSČ")->setRequired("Zadejte prosím PSČ");

		$form->addText("shipping_name", "Jméno")->setRequired(false);
		$form->addText("shipping_street", "Ulice")->setRequired(false);
		$form->addText("shipping_city", "Město")->setRequired(false);
		$form->addText("shipping_zip", "PSČ")->setRequired(false);

		$form->addText("sale_code", "Slevový kód")->setRequired(false);

		$form->addCheckbox("terms", "Souhlasím s VOP")->setRequired("Pro pokračování je potřeba souhlasit s podmínkami.");
		$form->addCheckbox("news", "Přeji si dostávat newsletter");

		$form->onSuccess[] = [$this, "processCheckoutForm"];

		return $form;
	}

	public function processCheckoutForm(\Nette\Application\UI\Form $form, \Nette\ArrayHash $values)
	{

		unset($values->terms);
		unset($values->email_confirmation);
		$order = $this->context->getService("crudRepository")->getTable("orders")->insert((array)$values + ["items" => serialize($this->context->getService("shoppingCart")->getItems()), "confirmed" => 0]);
		$this->redirect("Eshop:summary", $order->id);
		exit;
	}

	public function actionSummary($id)
	{
		$this->template->order = $values = $this->context->getService("crudRepository")->getTable("orders")->where("id = ?", $id)->fetch();

		$items = unserialize($values->items);

		$total = 0;
		$totalVat = 0;

		foreach ($items as $item) {
			$total += $item['total_price'];
			$totalVat += $item['total_price_vat'];
		}

		$this->template->total = $total;
		$this->template->totalWithoutVat = $totalVat;
	}

	public function actionConfirmOrder($id)
	{

		$values = $this->context->getService("crudRepository")->getTable("orders")->where("id = ?", $id)->fetch();

		// Send email to customer
		$this->_sendEmailConfirmation($values);

		$this->context->getService("shoppingCart")->clean();
		$this->redirect("Eshop:default#thankyou");
	}


	public function actionSignOut()
	{
		$user = $this->getUser();
		$user->logout(true);

		$this->context->getService("shoppingCart")->clean();

		$this->redirect('Homepage:');
	}

	public function _sendEmailConfirmation($order)
	{
		// Send email

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . "/../templates/order_confirmation.latte");

		$mail = new Nette\Mail\Message();
		$mail->addTo($order->email);
		$mail->addCc("jiri@spacek.org");
		$mail->setFrom("noreply@chocotopia.cz", "E-shop Chocotopia");
		$mail->setSubject("Potvrzení objednávky v e-shopu Choctopia.cz");
		$mail->setHtmlBody($template);

		$mailer = new Nette\Mail\SendmailMailer();
		$mailer->send($mail);
	}

	public function _sendEmailRegistrationConfirmation($email)
	{
		// Send email

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . "/../templates/registration_confirmation.latte");

		$mail = new Nette\Mail\Message();
		$mail->addTo($email);
		$mail->addCc("info@manatech.cz");
		$mail->addBcc("jiri@spacek.org");
		$mail->setFrom("noreply@manatech.cz", "Manatech.cz");
		$mail->setSubject("Potvrzení registrace v e-shopu manatech.cz");
		$mail->setHtmlBody($template);

		$mailer = new Nette\Mail\SendmailMailer();
		$mailer->send($mail);
	}

	public function actionProfileBusiness()
	{
		$this->template->bodyClass = "has-submenu";
	}

	public function actionProfileCustomer()
	{
		$this->template->bodyClass = "has-submenu";
	}

	public function actionImportCategories()
	{
		$data = file_get_contents("./temp/data.txt");
		$data = explode("\n", $data);
		$data = array_map(function ($item) {
			return explode("\t", $item);
		}, $data);
		$categories = array_map(function ($item) {
			return $item[6];
		}, $data);

		$categories = array_unique($categories);

		$categories = array_map(function ($item) use ($data) {
			$array = [
				"name" => $item
			];
			$name = $item;
			$array['categories'] = array_unique(array_map(function ($item) use ($name) {
				if ($item[6] == $name) {
					return $item[7];
				}
			}, $data));

			return $array;

		}, $categories);

		$i = 0;
		$rCategories = [];
		foreach ($categories as $item) {

			if (!$item["name"]) {
				continue;
			}

			$i++;
			$pid = $this->context->getService("crudRepository")->getTable("categories")->insert([
				"name" => $item["name"],
				"lang" => "cs_CZ",
				"parent" => 0
			]);

			$rCategories[$pid->id] = $item["name"];

			foreach ($item["categories"] as $subCatItem) {

				if (!$subCatItem) {
					continue;
				}

				$i++;

				$spid = $this->context->getService("crudRepository")->getTable("categories")->insert([
					"parent" => $pid,
					"name" => $subCatItem,
					"lang" => "cs_CZ"
				]);

				$rCategories[$spid->id] = $subCatItem;

			}
		}

		$pid = 0;
		foreach ($data as $product) {
			if (!file_exists("./temp/photos/" . $product[0] . ".jpg")) {
				continue;
			}

			$i++;

			$price = 0;

			$insertData = [
				"id" => $i,
				"ean" => $product[0],
				"name_cz" => $product[1],
				"name_en" => $product[2],
				"unit" => $product[3],
				"weight" => $product[4],
				"price" => $price,
				"product_qty" => $product[5],
				"category_id" => array_search($product[6], $rCategories),
				"subcategory_id" => array_search($product[7], $rCategories),
				"file_id" => $i
			];

			$this->context->getService("crudRepository")->getTable("eshop")->insert($insertData);

			$this->context->getService("crudRepository")->getTable("file")->insert([
				"id" => $i,
				"type" => "image",
				"filename" => $insertData["ean"] . ".jpg"
			]);
		}

		die;
	}

	public function actionImportPrices()
	{

		if (($handle = fopen("./temp/ceny.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
				//var_dump($data[0], $data[12]);
				$this->context->getService("crudRepository")->getTable("eshop")->where("ean = ?", $data[0])->update([
					"price" => $data[12],
					"price_b2b_8" => floatval($data[1]),
					"price_b2b_2" => floatval($data[2]),
					"price_b2b_1" => floatval($data[3]),
					"price_b2b_10" => floatval($data[4]),
					"price_b2b_9" => floatval($data[5]),
					"price_b2b_3" => floatval($data[6]),
					"price_b2b_6" => floatval($data[7]),
					"price_b2b_7" => floatval($data[8]),
					"price_b2b_4" => floatval($data[9]),
					"price_b2b_5" => floatval($data[10]),
					"price_b2b_11" => floatval($data[11]),
				]);
			}
			fclose($handle);
		}

		die;
	}

	// Components

	public function createComponentCheckoutSteps()
	{
		$component = new CheckoutSteps();

		$step = 1;
		try {
			if ($this->isLinkCurrent("Eshop:cart")) {
				$step = 1;
			}

			if ($this->isLinkCurrent("Eshop:checkout")) {
				$step = 2;
			}

			if ($this->isLinkCurrent("Eshop:shipping")) {
				$step = 3;
			}

			if ($this->isLinkCurrent("Eshop:summary")) {
				$step = 4;
			}
		} catch (\Nette\Application\UI\InvalidLinkException $e) {
		}


		$component->setCurrentStep($step);
		return $component;
	}

}