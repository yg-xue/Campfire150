<?php

class Admin extends Controller {

	function __construct()
	{
		//Will limit all these function to admin level of privlidge
		// if(!$this->isAdmin())
		// {
		// 	$this->redirect("");
		// }
	}
	
	//Main action for controller, equivelent to: www.site.com/controller/
	function index()
	{
		//Loads a model from corresponding model folder
		$model = $this->loadModel('AdminModel');

		//Loads a view model from corresponding viewmodel folder
		//$viewModel = $this->loadModel('SomeViewModel');

		//Loads a view from corresponding view folder
		$template = $this->loadView('index');

		//Adds a variable or object to that can be accessed in the view
		//$template->set('viewModel', $viewModel);

		//Renders the view. true indicates to load the layout
		$template->render(true);

		//Execute code if a post back
		if($this->isPost())
		{
			//Can be used to redirect to another controller
			//Can add query values ?id=1
			//$this->redirect("controller/action");

			//Check if request is ajax
			//$this->isAjax()
		}
		else
		{
			//Execute this code if NOT a post back
		}
	}

	function AjaxUserList()
	{
		// [start] => 0
		 //    [length] => 10
		 //    [search] => Array
		 //        (
		 //            [value] => 
		 //            [regex] => false
		 //        )
		$userList;
		$howMany = $_POST["length"]; //How many results to return
		$page = $_POST["draw"]; //What page number in results
		$adminID = isset($_SESSION["userID"]) ? $_SESSION["userID"] : '';

		if(!empty($_POST["search"]["value"]))
		{
			$accountModel = $this->loadModel('Account/AccountModel');
			
			//Perform a search
			$userList = $accountModel->searchForUser($userSearch, $howMany, $page);
		}
		else
		{
			$adminModel = $this->loadModel('AdminModel');

			$userList = $adminModel->getListUsersDisabled($adminID, $howMany, $page);
		}

		//Process user list into array like below:	

		$output = array(
	        "draw" => intval($_POST["draw"]),
	        "recordsTotal" => 50,
	        "recordsFiltered" =>50,
	        "data" => array(
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	)
	    );
		echo json_encode($output);
	}

	function AjaxUserListDisabled()
	{
		// [start] => 0
	 //    [length] => 10
	 //    [search] => Array
	 //        (
	 //            [value] => 
	 //            [regex] => false
	 //        )
		$userList;
		$howMany = $_POST["length"]; //How many results to return
		$page = $_POST["draw"]; //What page number in results
		$adminID = isset($_SESSION["userID"]) ? $_SESSION["userID"] : '';

		if(!empty($_POST["search"]["value"]))
		{
			$accountModel = $this->loadModel('Account/AccountModel');
			
			//Perform a search
			$userList = $accountModel->searchForUser($userSearch, $howMany, $page);
		}
		else
		{
			$adminModel = $this->loadModel('AdminModel');

			$userList = $adminModel->getListUsersDisabled($adminID, $howMany, $page);
		}

		//Process user list into array like below:		

		$output = array(
	        "draw" => intval($_POST["draw"]),
	        "recordsTotal" => 50,
	        "recordsFiltered" =>50,
	        "data" => array(
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	)
	    );
		echo json_encode($output);
	}

	function AjaxUserListInappropriate()
	{
		// [start] => 0
		 //    [length] => 10
		 //    [search] => Array
		 //        (
		 //            [value] => 
		 //            [regex] => false
		 //        )
		$userList;
		$howMany = $_POST["length"]; //How many results to return
		$page = $_POST["draw"]; //What page number in results
		$adminID = isset($_SESSION["userID"]) ? $_SESSION["userID"] : '';

		if(!empty($_POST["search"]["value"]))
		{
			$accountModel = $this->loadModel('Account/AccountModel');
			
			//Perform a search
			$userList = $accountModel->searchForUser($userSearch, $howMany, $page);
		}
		else
		{
			$adminModel = $this->loadModel('AdminModel');

			$userList = $adminModel->getListUsersDisabled($adminID, $howMany, $page);
		}

		//Process user list into array like below:	

		$output = array(
	        "draw" => intval($_POST["draw"]),
	        "recordsTotal" => 50,
	        "recordsFiltered" =>50,
	        "data" => array(
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	array("Airi",
				      "Satou",
				      "Accountant",
				      "Tokyo",
				      "28th Nov 08",
				      "$162,700"),
	        	)
	    );
		echo json_encode($output);
	}

	function insert()
	{
		//Loads a model from corresponding model folder
		$model = $this->loadModel('SomeModel');

		//Loads a view model from corresponding viewmodel folder
		$viewModel = $this->loadModel('SomeViewModel');

		//Loads a view from corresponding view folder
		$template = $this->loadView('insert');
		//Adds a variable or object to that can be accessed in the view
		$template->set('viewModel', $viewModel);
		//Renders the view. true indicates to load the layout
		$template->render(true);

		//Execute code if a post back
		if($this->isPost())
		{
			//Can be used to redirect to another controller
			//Can add query values ?id=1
			//$this->redirect("controller/action");

			//Check if request is ajax
			//$this->isAjax()
		}
		else
		{
			//Execute this code if NOT a post back
		}
	}

	function update()
	{
		//Loads a model from corresponding model folder
		$model = $this->loadModel('SomeModel');

		//Loads a view model from corresponding viewmodel folder
		$viewModel = $this->loadModel('SomeViewModel');

		//Loads a view from corresponding view folder
		$template = $this->loadView('update');
		//Adds a variable or object to that can be accessed in the view
		$template->set('viewModel', $viewModel);
		//Renders the view. true indicates to load the layout
		$template->render(true);

		//Execute code if a post back
		if($this->isPost())
		{
			//Can be used to redirect to another controller
			//Can add query values ?id=1
			//$this->redirect("controller/action");

			//Check if request is ajax
			//$this->isAjax()
		}
		else
		{
			//Execute this code if NOT a post back
		}
	}

	function delete()
	{
		//Loads a model from corresponding model folder
		$model = $this->loadModel('SomeModel');

		//Loads a view model from corresponding viewmodel folder
		$viewModel = $this->loadModel('SomeViewModel');

		//Loads a view from corresponding view folder
		$template = $this->loadView('delete');
		//Adds a variable or object to that can be accessed in the view
		$template->set('viewModel', $viewModel);
		//Renders the view. true indicates to load the layout
		$template->render(true);

		//Execute code if a post back
		if($this->isPost())
		{
			//Can be used to redirect to another controller
			//Can add query values ?id=1
			//$this->redirect("controller/action");

			//Check if request is ajax
			//$this->isAjax()
		}
		else
		{
			//Execute this code if NOT a post back
		}
	}    
}

?>