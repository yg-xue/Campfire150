<?php
/**
* 
*/
class StoryViewModel extends ViewModel
{
	//Story properties same as in database
	public $StoryId;
	public $User_UserId;
	public $UserId;

	//UserProfileViewModel
	public $UserProfile;

	public $DatePosted;	
	public $DateCreated;
	public $DateUpdated;		

	public $StoryTitle;
	public $Content;
	public $Active;
	public $LatestChange;
	public $Published;
	public $StoryPrivacyType_StoryPrivacyTypeId;

	public $Images;

	public $Comments;

	public $Tags;
	public $TagIds;

	public $PictureUrl;

	public $QuestionAnswers;//StoryQuestionViewModel

	function __construct()
	{
		$errors["StoryPrivacyType_StoryPrivacyTypeId"] = array(
			'required' =>
				array(
					'Message' => gettext('The privacy field is required!'),
					'Properties' => array()
				)
		);

		$errors["StoryTitle"] = array(
			'required' =>
				array(
					'Message' => gettext('The title field is required!'),
					'Properties' => array()
				)
		);

		$errors["Tags"] = array(
			'required' =>
				array(
					'Message' => gettext('The tags field is required!'),
					'Properties' => array()
				)
		);

		$errors["Content"] = array(
			'required' =>
				array(
					'Message' => gettext('The content field is required!'),
					'Properties' => array()
				)
		);

		//Pass validation to the View Model
		parent::__construct($errors);
	}
}
?>