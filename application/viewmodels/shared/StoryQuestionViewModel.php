<?php
/**
* 
*/
class StoryQuestionViewModel extends ViewModel
{
	public $QuestionId; //Id of item
	public $NameE; // English Name
	public $NameF; // French Name
	public $DateCreated;
	public $DateUpdated;
	public $Active;

	function __construct()
	{		
		// $errors["QuestionId"] = array(
		// 	'required' =>
		// 		array(
		// 			'Message' => gettext('The table field is required!'),
		// 			'Properties' => array()
		// 		)
		// );
		$errors["NameE"] = array(
			'required' =>
				array(
					'Message' => gettext('The English Name field is required.'),
					'Properties' => array()
				)
		);

		$errors["NameF"] = array(
			'required' =>
				array(
					'Message' => gettext('The French Name field is required.'),
					'Properties' => array()
				)
		);

		//Pass validation to the View Model
		parent::__construct($errors);
	}
}
?>