<?php
	require_once(APP_DIR .'helpers/storysearch.php');

class AdminModel extends Model {

	public function isAdmin($userID)
	{
		//Check if the ID is an admin ID

		try
		{
			$statement = "SELECT * FROM user 
							WHERE UserId = :ID AND AdminFlag = 1";

			$rowCount = $this->fetchRowCount($statement, array("ID" => $userID));

			if($rowCount >= 1)
				return true;
			else
				return false;
		}
		catch(PDOException $e)
		{ 
			return $e->getMessage();
		}
	}

	public function searchStoriesPendingApproval($storySearch, $adminID, $howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts string to search for a story
		//Checks if user has marked story as inappropriate and if user has recommended story (add these to story viewmodel class)
		//returns an array of Story class that relate to the search string

		try 
		{
			$searchObject = new StorySearch();
			$story = $searchObject->SearchQuery($storySearch, $userID, $howMany, $page, $approved = FALSE, $active = TRUE); 

			return $story;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function searchStoriesRejected($storySearch, $userID, $howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts string to search for a story
		//Checks if user has makrked story as inappropriate and if user has recommended story (add these to story viewmodel class)
		//returns an array of Story class that relate to the search string

		try 
		{
			$searchObject = new StorySearch();

			$story = $searchObject->SearchQuery($storySearch, $userID, $howMany, $page, $approved = FALSE, $active = TRUE); 

			return $story;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}	
	}

	public function getStoryById($storyId)
	{
		try
		{
			$statement = "SELECT *
							FROM story s
							LEFT JOIN user u
							ON s.User_UserId = u.UserId							
							LEFT JOIN story_has_picture shp
							ON shp.Story_StoryId = s.StoryId
							LEFT JOIN picture p
							ON p.PictureId = shp.PictureId
							WHERE StoryId = :StoryId";

			$parameters = array(":StoryId" => $storyId);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $storyList[0];

		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function getStoryListPendingApproval($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many results to return, what page of results your on
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of stories that a user has submited but hasn't been apprved yet.
		//Should not contain any published stories
		//returns an array of Story class

		try
		{
			$statement = "SELECT *, 
								(SELECT COUNT( * )
									FROM story s1
									LEFT JOIN user u1 ON s1.User_UserId = u1.UserId
									WHERE s1.Published = TRUE 
									AND s1.StoryId NOT
									IN (

									SELECT Story_StoryId
									FROM admin_approve_story aas1
									WHERE aas1.Active =
									TRUE
									)
								) AS totalStories
							FROM story s
							LEFT JOIN user u
							ON s.User_UserId = u.UserId
							WHERE s.Published = TRUE 
							AND s.StoryId 
							NOT IN (SELECT Story_StoryId 
									FROM admin_approve_story aas
									WHERE aas.Active = TRUE ) 
							LIMIT :Start, :HowMany";

			$start = $this->getStartValue($howMany, $page);

			$parameters = array(
				":Start"=>$start, 
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $storyList;

		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}		
	}

	public function getStoryListRejected($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of stories that have been rejected by admin
		//Should not contain any published stories
		//Should have the admin user details an reason for being rejected
		//returns an array of Story class

		try
		{
			$statement = "SELECT *,
								(SELECT COUNT(*) FROM story s LEFT JOIN admin_approve_story aas ON s.storyID=aas.Story_StoryId 
									LEFT JOIN user u ON s.User_UserId = u.UserId WHERE aas.Approved = 0) AS totalStories
							FROM story s 
							LEFT JOIN admin_approve_story aas 
							ON s.StoryId=aas.Story_StoryId 
							LEFT JOIN user u 
							ON s.User_UserId = u.UserId 
							WHERE aas.Approved = 0 
							LIMIT :Start, :HowMany";

			$start = $this->getStartValue($howMany, $page);

			$parameters = array(
				":Start"=>$start, 
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function getStoryListFlaggedInappropriate($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of stories that have been marked as inappropriate by users
		//Order the list by how many inappropriate flags there are
		//returns an array of Story class

		try
		{
			$start = $this->getStartValue($howMany, $page);

			$statement = "SELECT *, COUNT(urs.User_UserId) AS NumberOfFlagged,
								(SELECT COUNT(*) FROM (SELECT s1.StoryId
									FROM story s1
									LEFT JOIN user_recommend_story urs1 ON s1.StoryId = urs1.Story_StoryId
									WHERE urs1.Opinion =0
									GROUP BY s1.StoryId) tmptable
								) AS totalStories
							FROM story s 
							LEFT JOIN user_recommend_story urs 
							ON s.StoryId=urs.Story_StoryId 
							LEFT JOIN user u 
							ON s.User_UserId=u.UserId 
							WHERE urs.Opinion = 0 
							GROUP BY s.StoryId 
							ORDER BY NumberOfFlagged DESC 
							LIMIT :Start, :HowMany";

			$parameters = array(
				":Start"=>$start, 
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}		
	}

	public function getStoryListPublished($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of stories that have been rejected by admin
		//Should not contain any published stories
		//Should have the admin user details an reason for being rejected
		//returns an array of Story class

		try
		{
			$statement = "SELECT *,
								(SELECT COUNT(*) FROM story s LEFT JOIN admin_approve_story aas ON s.StoryID=aas.Story_StoryId 
									LEFT JOIN user u ON s.User_UserId = u.UserId WHERE aas.Approved = 1) AS totalStories
							FROM story s 
							LEFT JOIN admin_approve_story aas 
							ON s.StoryID=aas.Story_StoryId 
							LEFT JOIN user u 
							ON s.User_UserId = u.UserId 
							WHERE aas.Approved = 1 
							LIMIT :Start, :HowMany";

			$start = $this->getStartValue($howMany, $page);

			$parameters = array(
				":Start"=>$start, 
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function rejectStory($adminID, $storyID, $reason)
	{
		//Accepts the adminID, the story id and the reason why it was rejected
		//returns bool whether it was saved succesfully or not

		try
		{
			$this->fetch(
				"UPDATE admin_approve_story 
				SET Active = 0
			 	WHERE Story_StoryId = :StoryID AND Active = 1", 
			 	array(":StoryID"=>$storyID)
			 	);//Set the active record for the story to deactive;

			$statement = "INSERT INTO admin_approve_story (User_UserId, Story_StoryId, Reason, Approved, Pending)
			  				VALUES(:AdminID, :StoryID, :Reason, FALSE, FALSE)
							ON DUPLICATE KEY
							UPDATE Reason=:NewReason, Approved=0, Active=1";

			$parameters = array(
					":AdminID" => $adminID,
					":StoryID" => $storyID,
					":Reason" => $reason,
					":NewReason" => $reason
					);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();			
		}
	}

	public function approveStory($adminID, $storyID, $reason)
	{
		//Accepts the adminID and the story id
		//returns bool whether it was saved succesfully or not
		
		try
		{
			$this->fetch(
				"UPDATE admin_approve_story 
				SET Active = 0
			 	WHERE Story_StoryId = :StoryID AND Active = 1", 
			 	array(":StoryID"=>$storyID)
			 	);//Set the active record for the story to deactive;

			$statement = "INSERT INTO admin_approve_story (User_UserId, Story_StoryId, Reason, Approved, Pending)
			  				VALUES(:AdminID, :StoryID, :Reason, TRUE, FALSE)
							ON DUPLICATE KEY
							UPDATE Reason=:NewReason, Approved=1, Active=1";

			$parameters = array(
					":AdminID" => $adminID,
					":StoryID" => $storyID,
					":Reason" => $reason,
					":NewReason" => $reason
					);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{

			return $e->getMessage();

		}
	}

	public function getCommentListFlaggedInappropriate($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of comments that is not rejected and have been marked as inappropriate by users
		//Order the list by how many inappropriate flags there are
		//returns an array of Comment class
		
		try
		{
			$statement = "SELECT c.Content AS Content, s.StoryTitle, c.CommentId, COUNT( * ) AS TotalFlagNumber, (

							SELECT COUNT( * ) 
								FROM (

								SELECT * 
								FROM user_inappropriateflag_comment
								GROUP BY Comment_CommentId
								) tmptable
							) AS TotalComments
							FROM user_inappropriateflag_comment uic
							INNER JOIN comment c ON c.CommentId = uic.Comment_CommentId
							INNER JOIN story s ON s.StoryId = c.Story_StoryId
							GROUP BY uic.Comment_CommentId
							ORDER BY TotalFlagNumber DESC 
							LIMIT :Start, :HowMany";
			
			$start = $this->getStartValue($howMany, $page);
			$parameters = array(
				":Start"=>$start,
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/CommentViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function getCommentById($commentId)
	{
		try
		{
			$statement = "SELECT *
							FROM comment c
							INNER JOIN user u
							ON c.User_UserId = u.UserId
							WHERE c.CommentId = :CommentId";

			$parameters = array(":CommentId" => $commentId);

			$commentList = $this->fetchIntoClass($statement, $parameters, "shared/CommentViewModel");

			return $commentList[0];

		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function getCommentListRejected($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of comments that have been marked as inappropriate by users
		//Order the list by how many inappropriate flags there are
		//returns an array of Comment class
		
		try
		{			
			$statement = "SELECT c.CommentId, c.Story_StoryId, c.User_UserId, c.Content, c.PublishFlag, c.DateUpdated,
								s.StoryTitle, (
								SELECT COUNT( * )
								FROM admin_reject_comment
								WHERE Active = 1 AND Rejected = 1
								) AS TotalComments
							FROM admin_reject_comment arc 
							INNER JOIN comment c
							ON c.CommentId = arc.Comment_CommentId 
							INNER JOIN story s 
							ON c.Story_StoryId = s.StoryId 
							INNER JOIN user u 
							ON c.User_UserId=u.UserId
							WHERE arc.Active = 1 AND arc.Rejected = 1
							ORDER BY c.CommentId 
							LIMIT :Start, :HowMany";
			
			$start = $this->getStartValue($howMany, $page);
			$parameters = array(
				":Start"=>$start,
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/CommentViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function getCommentListPublished($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of comments that have been marked as inappropriate by users
		//Order the list by how many inappropriate flags there are
		//returns an array of Comment class
		
		try
		{			
			$statement = "SELECT c.CommentId, c.Story_StoryId, c.User_UserId, c.Content, c.PublishFlag, c.DateUpdated,
								s.StoryTitle, (
								SELECT COUNT( * )
								FROM admin_reject_comment
								WHERE Active = 1 AND Rejected = 0
								) AS TotalComments
							FROM admin_reject_comment arc 
							LEFT JOIN comment c
							ON c.CommentId = arc.Comment_CommentId 
							LEFT JOIN story s 
							ON c.Story_StoryId = s.StoryId 
							LEFT JOIN user u 
							ON c.User_UserId=u.UserId
							WHERE arc.Active = 1 AND arc.Rejected = 0
							ORDER BY c.CommentId 
							LIMIT :Start, :HowMany";
			
			$start = $this->getStartValue($howMany, $page);
			$parameters = array(
				":Start"=>$start,
				":HowMany"=>$howMany
				);

			$storyList = $this->fetchIntoClass($statement, $parameters, "shared/CommentViewModel");

			return $storyList;
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function rejectCommentAsAdmin($adminID, $commentID, $reason)
	{
		//Accepts the adminID, the comment id and the reason why it was rejected
		//Allows admin users to hide comments if they feel they are innappropriate
		//returns bool whether it was saved succesfully or not

		try
		{
			$this->fetch(
				"UPDATE admin_reject_comment SET Active = 0 WHERE Active = 1 AND Comment_CommentId = :CommentID", 
				array(':CommentID' => $commentID)
				);

			$statement = "INSERT INTO admin_reject_comment (Comment_CommentId, User_UserId, Rejected, Reason) 
							VALUES (:CommentID, :AdminID, 1, :Reason) 
							ON DUPLICATE KEY 
							UPDATE Reason = :NewReason, Rejected = 1, Active = 1";

			$parameters = array(					 
					":CommentID" => $commentID, 
					":AdminID" => $adminID,
					":Reason" => $reason,
					":NewReason" => $reason
					);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function approveCommentAsAdmin($adminID, $commentID, $reason)
	{
		//Accepts the adminID and the comment id
		//Allows admin users to remove their rejected status placed on comments
		//returns bool whether it was saved succesfully or not

		try
		{
			$this->fetch(
				"UPDATE admin_reject_comment SET Active = 0 WHERE Active = 1 AND Comment_CommentId = :CommentID", 
				array(':CommentID' => $commentID)
				);

			$statement = "INSERT INTO admin_reject_comment (Comment_CommentId, User_UserId, Rejected, Reason) 
							VALUES (:CommentID, :AdminID, 0, :Reason) 
							ON DUPLICATE KEY 
							UPDATE Reason = :NewReason, Rejected = 0, Active = 1";

			$parameters = array(					 
					":CommentID" => $commentID, 
					":AdminID" => $adminID,
					":Reason" => $reason,
					":NewReason" => $reason
					);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getUserByID($userID)
	{
		try
		{
			$statement = "SELECT *,	pp.PictureId as ProfilePictureId,

							bp.PictureId as BackgroundPictureId,
							
							u.ActionStatement

							FROM user

							LEFT JOIN useractionstatement u    ON user.UserId = u.user_UserId AND u.Active = TRUE
							LEFT JOIN securityquestionanswer s ON user.UserId = s.user_UserId

							LEFT JOIN picture pp 
							ON user.UserId = pp.User_UserId AND (pp.Picturetype_PictureTypeId = 1) AND (pp.Active = TRUE)

							LEFT JOIN picture bp 
							ON user.UserId = bp.User_UserId AND (bp.Picturetype_PictureTypeId = 2) AND (bp.Active = TRUE)

							WHERE user.UserId = :UserId";

			$user = $this->fetchIntoClass($statement, array(":UserId" => $userID), "shared/UserViewModel");

			if(isset($user[0]))
			{
				return $user[0];
			}

			return null;
		}
		catch(Exception $ex)
		{
			throw $ex;
		}
	}

	public function getListUsers($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of users
		//returns an array of User class

		try
		{
			$statement = "SELECT *,
							(SELECT COUNT(*) FROM user) AS totalUsers
							FROM user
							ORDER BY UserId ASC
							LIMIT :start, :howmany";

			$start = $this-> getStartValue($howMany, $page);

			$parameters = array(
				":start" => $start,
				":howmany" => $howMany
				);

			$userList = $this->fetchIntoClass($statement, $parameters, "shared/UserViewModel");

			return $userList;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function deActivateUser($adminID, $userID, $reason)
	{
		//Accepts a User class for $user and a User class for $admin
		//Sets the active flag to false in user profile
		//Uses admin details to say who deactivated the account
		
		try
		{
			$this->fetch(
				"UPDATE admin_actionon_user SET Active = 0 WHERE User_UserId=:UserID AND Active = 1", 
				array('UserID' => $userID)
				);

			$this->fetch(
				"UPDATE user SET Active = 0 WHERE UserId=:UserID", 
				array('UserID' => $userID)
				);

			$statement = "INSERT INTO admin_actionon_user (Admin_UserId, User_UserId, Action, Reason)
							VALUES(:AdminID, :UserID, 0, :Reason)
							ON DUPLICATE KEY 
							UPDATE Action = 0, Active = 1, Reason = :NewReason";

			$parameters = array( 
					":AdminID" => $adminID,
					":UserID" => $userID,
					":Reason" => $reason,
					":NewReason" => $reason
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}


	public function activateUser($adminID, $userID, $reason)
	{
		//Accepts a User class for $user and a User class for $admin
		//Sets the active flag to false in user profile
		//Uses admin details to say who deactivated the account
		
		try
		{
			$this->fetch(
				"UPDATE admin_actionon_user SET Active = 0 WHERE User_UserId=:UserID AND Active = 1", 
				array('UserID' => $userID)
				);

			$this->fetch(
				"UPDATE user SET Active = 1 WHERE UserId=:UserID", 
				array('UserID' => $userID)
				);

			$statement = "INSERT INTO admin_actionon_user (Admin_UserId, User_UserId, Action, Reason) 
							VALUES(:AdminID, :UserID, 1, :Reason)
							ON DUPLICATE KEY 
							UPDATE Action = 1, Active = 1, Reason = :NewReason";

			$parameters = array( 
					":AdminID" => $adminID,
					":UserID" => $userID,
					":Reason" => $reason,
					":NewReason" => $reason
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListUsersActive($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of users that have been disabled with reason
		//returns an array of User class

		try
		{
			$statement = "SELECT * ,
								(SELECT COUNT(*) FROM user WHERE Active = 1) AS totalUsers
							FROM user 
							WHERE Active = 1 
							ORDER BY UserId ASC 
							LIMIT :Start, :HowMany";

			$start = $this->getStartValue($howMany, $page);

			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$userList = $this->fetchIntoClass($statement, $parameters, "shared/UserViewModel");

			return $userList;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListUsersDisabled($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of users that have been disabled with reason
		//returns an array of User class

		try
		{
			$statement = "SELECT * ,
								(SELECT COUNT(*) FROM user WHERE Active = 0) AS totalUsers
							FROM user 
							WHERE Active = 0 
							ORDER BY UserId ASC 
							LIMIT :Start, :HowMany";

			$start = $this->getStartValue($howMany, $page);

			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$userList = $this->fetchIntoClass($statement, $parameters, "shared/UserViewModel");

			return $userList;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
 
	public function getListUsersOderedByMostInappropriateFlags($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts how many, page
		//for example, if how many = 10 and page = 2, you would take results 11 to 20
		//Gets a list of users ordered by how many inapropriate flags they have issued
		//returns an array of User class

		try
		{

			 $statement = "SELECT * , COUNT( * ) AS TotalInappropriate, (

								SELECT COUNT( * ) 
								FROM (

										SELECT COUNT( * ) AS TotalInappropriate
										FROM user AS u
										LEFT JOIN story AS s ON u.UserId = s.User_UserId
										LEFT JOIN user_recommend_story AS urs ON urs.Story_StoryId = s.StoryId
										WHERE urs.Opinion = 
										FALSE 
										GROUP BY UserId
									)tmpTable
								) AS TotalRecords
							FROM user AS u
							LEFT JOIN story AS s ON u.UserId = s.User_UserId
							LEFT JOIN user_recommend_story AS urs ON urs.Story_StoryId = s.StoryId
							WHERE urs.Opinion = 
							FALSE 
							GROUP BY UserId		 				
			 				LIMIT :Start, :HowMany";
			
			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$userList = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

			return $userList;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function setUserAsAdmin($userId)
	{
		try
		{
			$statement = "UPDATE user SET AdminFlag = 1 WHERE UserId = :UserId";

			return $this->fetch($statement, array(":UserId" => $userId));
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function setUserAsRegular($userId)
	{
		try
		{
			$statement = "UPDATE user SET AdminFlag = 0 WHERE UserId = :UserId";

			return $this->fetch($statement, array(":UserId" => $userId));
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListActiveQuestionaireQuestions($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "SELECT * , (
								SELECT COUNT(*) FROM question WHERE Active = 1
							) AS TotalQuestions
							FROM question
							WHERE Active = 1
							LIMIT :Start, :HowMany";

			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$questionList = $this->fetchIntoObject($statement, $parameters);

			if(isset($questionList))
			{
				return $questionList;
			}

			return null;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListDeactiveQuestionaireQuestions($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "SELECT * , (
								SELECT COUNT(*) FROM question WHERE Active = 0
							) AS TotalQuestions
							FROM question
							WHERE Active = 0
							LIMIT :Start, :HowMany";

			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$questionList = $this->fetchIntoObject($statement, $parameters);

			if(isset($questionList))
			{
				return $questionList;
			}

			return null;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListQuestionaireAnswers($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "SELECT * , a.NameE AS AnswerE, a.NameF AS AnswerF, 
							q.NameE AS QuestionE, q.NameE AS QuestionF, (
								SELECT COUNT(*) 
								FROM answer
								LEFT JOIN answer_for_question
								ON AnswerId = Answer_AnswerId
								LEFT JOIN question q
								ON QuestionId = Question_QuestionId
								WHERE answer.Active = 1
							) AS TotalAnswers
							FROM answer a
							LEFT JOIN answer_for_question afq
							ON a.AnswerId = afq.Answer_AnswerId
							LEFT JOIN question q
							ON q.QuestionId = afq.Question_QuestionId
							WHERE a.Active = 1
							ORDER BY afq.Question_QuestionId
							LIMIT :Start, :HowMany";

			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$answerList = $this->fetchIntoObject($statement, $parameters);

			if(isset($answerList))
			{
				return $answerList;
			}

			return null;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListDeactiveQuestionaireAnswers($howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "SELECT * , a.NameE AS AnswerE, a.NameF AS AnswerF, 
							q.NameE AS QuestionE, q.NameE AS QuestionF, (
								SELECT COUNT(*) 
								FROM answer
								LEFT JOIN answer_for_question
								ON AnswerId = Answer_AnswerId
								LEFT JOIN question q
								ON QuestionId = Question_QuestionId
								WHERE answer.Active = 0
							) AS TotalAnswers
							FROM answer a
							LEFT JOIN answer_for_question afq
							ON a.AnswerId = afq.Answer_AnswerId
							LEFT JOIN question q
							ON q.QuestionId = afq.Question_QuestionId
							WHERE a.Active = 0
							ORDER BY afq.Question_QuestionId
							LIMIT :Start, :HowMany";

			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);

			$answerList = $this->fetchIntoObject($statement, $parameters);

			if(isset($answerList))
			{
				return $answerList;
			}

			return null;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function DeactiveQuestionaireAnswer($answerId)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "UPDATE answer SET Active = 0 WHERE AnswerId = :AnswerId AND Active = 1";

			return $this->fetch($statement, array(":AnswerId" => $answerId));
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function DeactiveQuestionaireQuestion($questionId)
	{
		//Gets a list of all the current questionaire questions
		//This will include a list of possible answers

		try
		{
			$statement = "UPDATE question SET Active = 0 WHERE QuestionId = :QuestionId AND Active = 1";

			return $this->fetch($statement, array(":QuestionId" => $questionId));
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getAnswerById($answerId)
	{
		//Accepts a answer id
		//returns answer detail of that id

		try
		{
			$statement = "SELECT * FROM answer WHERE AnswerId = :AnswerId";

			$answers = $this->fetchIntoClass($statement, array(":AnswerId" => $answerId), "shared/StoryAnswerViewModel");

			return $answers[0];
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getAnswersByQuestionId($questionId)
	{
		//Accepts a answer id
		//returns answer detail of that id

		try
		{
			$statement = "SELECT * 
							FROM answer_for_question AS afq
							LEFT JOIN answer
							ON afq.Answer_AnswerId = answer.AnswerId
							WHERE afq.Question_QuestionId = :QuestionId";

			$answerList = $this->fetchIntoClass($statement, array(":QuestionId" => $questionId), "shared/StoryAnswerViewModel");

			return $answerList;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function addAnswer($answerE, $answerF)
	{
		//Accepts a question answer id, and english answer, a french answer
		//returns bool if saved succesfully

		try
		{
			$statement = "INSERT INTO answer (NameE, NameF) 
							VALUES(:AnswerE, :AnswerF)";

			$parameters = array(
				":AnswerE" => $answerE,
				":AnswerF" => $answerF
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function updateAnswer($answerID, $answerE, $answerF)
	{
		//Accepts a question answer id, and english answer, a french answer
		//returns bool if saved succesfully

		try
		{
			$statement = "UPDATE answer
							SET NameE = :AnswerE, NameF = :AnswerF
							WHERE AnswerId = :AnswerID";

			$parameters = array(
				":AnswerE" => $answerE,
				":AnswerF" => $answerF,
				":AnswerID" => $answerID
				);
			
			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function addQuestionAnswerById($questionID, $answerID)
	{
		//Accepts a question id, and english answer, a french answer
		//returns bool if saved succesfully

		try
		{
			return $this->fetch(
				"INSERT INTO answer_for_question (Question_QuestionId, Answer_AnswerId) VALUES(:QuestionID, :AnswerID)",
			 	array(":QuestionID" => $questionID, ":AnswerID" => $answerID)
			 	);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
	
	public function addQuestionAnswer($questionID, $answerE, $answerF)
	{
		//Accepts a question id, and english answer, a french answer
		//returns bool if saved succesfully

		try
		{
			$statement = "SELECT * 
							FROM answer 
							WHERE NameE = :NameE AND NameF = :NameF";

			$rowCount = $this->fetchRowCount($statement, array("NameE" => $answerE, "NameF" => $answerF));

			if($rowCount < 1)
			{
				$this->addAnswer($answerE, $answerF);
				$answerID = $this->lastInsertId();				
			}
			else
			{
				$tmpAnswer = $this->fetchIntoObject($statement, array("NameE" => $answerE, "NameF" => $answerF));
				$answerID = $tmpAnswer[0]->AnswerId;
			}			
			return $this->addQuestionAnswerById($questionID, $answerID);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getQuestionByQuestionId($questionId)
	{
		//Accepts a answer id
		//returns answer detail of that id

		try
		{
			$statement = "SELECT * FROM question WHERE QuestionId = :QuestionId";

			$questions = $this->fetchIntoClass($statement, array(":QuestionId" => $questionId), "shared/StoryQuestionViewModel");

			return $questions[0];
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getQuestionsByAnswerId($answerId)
	{
		//Accepts a answer id
		//returns answer detail of that id

		try
		{
			$statement = "SELECT * FROM question
								LEFT JOIN answer_for_question AS afq
								ON question.QuestionId = afq.Question_QuestionId
								WHERE afq.Answer_AnswerId = :AnswerId";

			$questions = $this->fetchIntoClass($statement, array(":AnswerId" => $answerId), "shared/StoryQuestionViewModel");

			return $questions;
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function updateQuestion($questionID, $questionE, $questionF)
	{
		//Accepts a question id, and english question, a french question
		//returns bool if saved succesfully

		try
		{
			$statement = "UPDATE question 
							SET NameE = :QuestionE, NameF = :QuestionF 
							WHERE QuestionId = :QuestionId";

			$parameters = array(
				":QuestionE" => $questionE,
				":QuestionF" => $questionF,
				":QuestionId" => $questionID
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
	
	public function addQuestion($questionE, $questionF)
	{
		//Accepts a english questionE, a french questionF
		//returns bool if saved succesfully

		try
		{
			$statement = "INSERT INTO question (NameE, NameF) 
							VALUES (:QuestionE, :QuestionF)";

			$parameters = array(
				":QuestionE" => $questionE,
				":QuestionF" => $questionF
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getDropdownListItem($tableName, $itemId)
	{
		try
		{
			$statement = "";

			switch(true)
			{
				case strtolower($tableName) == "languagetype":
					$statement = "SELECT *, LanguageId AS Id FROM languagetype WHERE LanguageId = :Id";
					break;
				case strtolower($tableName) == "gendertype":
					$statement = "SELECT *, GenderTypeId AS Id FROM gendertype WHERE GenderTypeId = :Id";
					break;
				case strtolower($tableName) == "achievementleveltype":
					$statement = "SELECT *, LevelId AS Id FROM achievementleveltype WHERE LevelId = :Id";
					break;
				case strtolower($tableName) == "securityquestion":
					$statement = "SELECT *, SecurityQuestionId AS Id FROM securityquestion WHERE SecurityQuestionId = :Id";
					break;
				case strtolower($tableName) == "picturetype":
					$statement = "SELECT *, PictureTypeId AS Id FROM picturetype WHERE PictureTypeId = :Id";
					break;
				case strtolower($tableName) == "profileprivacytype":
					$statement = "SELECT *, PrivacyTypeId AS Id FROM profileprivacytype WHERE PrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "storyprivacytype":
					$statement = "SELECT *, StoryPrivacyTypeId AS Id FROM storyprivacytype WHERE StoryPrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "actions_taken_type":
					$statement = "SELECT *, ActionsTakenTypeId AS Id FROM actions_taken_type WHERE ActionsTakenTypeId = :Id";
					break;
				default:
					return $tableName." is not a proper table name";
			}			

			$items = $this->fetchIntoClass($statement, array(":Id" => $itemId), "shared/DropdownItemViewModel");
			return $items[0];
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function getListDropdowns($tableName, $howMany = self::HOWMANY, $page = self::PAGE)
	{
		//Accepts admin id
		//returns list of dropdowns and their values

		try
		{
			$statement = "";

			switch(true)
			{
				case strtolower($tableName) == "languagetype":
					$statement = "SELECT *, LanguageId AS Id, (SELECT COUNT(*) FROM languagetype WHERE Active = 1) AS TotalNumber FROM languagetype WHERE Active = 1";
					break;
				case strtolower($tableName) == "gendertype":
					$statement = "SELECT *, GenderTypeId AS Id, (SELECT COUNT(*) FROM gendertype WHERE Active = 1) AS TotalNumber FROM gendertype WHERE Active = 1";
					break;
				case strtolower($tableName) == "achievementleveltype":
					$statement = "SELECT *, LevelId AS Id, (SELECT COUNT(*) FROM achievementleveltype WHERE Active = 1) AS TotalNumber FROM achievementleveltype WHERE Active = 1";
					break;
				case strtolower($tableName) == "securityquestion":
					$statement = "SELECT *, SecurityQuestionId AS Id, (SELECT COUNT(*) FROM securityquestion WHERE Active = 1) AS TotalNumber FROM securityquestion WHERE Active = 1";
					break;
				case strtolower($tableName) == "picturetype":
					$statement = "SELECT *, PictureTypeId AS Id, (SELECT COUNT(*) FROM picturetype WHERE Active = 1) AS TotalNumber FROM picturetype WHERE Active = 1";
					break;
				case strtolower($tableName) == "profileprivacytype":
					$statement = "SELECT *, PrivacyTypeId AS Id, (SELECT COUNT(*) FROM profileprivacytype WHERE Active = 1) AS TotalNumber FROM profileprivacytype WHERE Active = 1";
					break;
				case strtolower($tableName) == "storyprivacytype":
					$statement = "SELECT *, StoryPrivacyTypeId AS Id, (SELECT COUNT(*) FROM storyprivacytype WHERE Active = 1) AS TotalNumber FROM storyprivacytype WHERE Active = 1";
					break;
				case strtolower($tableName) == "actions_taken_type":
					$statement = "SELECT *, ActionsTakenTypeId AS Id, (SELECT COUNT(*) FROM actions_taken_type WHERE Active = 1) AS TotalNumber FROM actions_taken_type WHERE Active = 1";
					break;
				default:
					return $tableName." is not a proper table name";
			}	

			$statement .= " LIMIT :Start, :HowMany";

			$start = $this-> getStartValue($howMany, $page);
			
			$parameters = array( 
					":Start" => $start,
					":HowMany" => $howMany
				);		

			return $this->fetchIntoClass($statement, $parameters, "shared/DropdownItemViewModel");
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
	public function addDropdownItem($tableName, $dropdownValueE, $dropdownValueF)
	{
		//Accepts a english dropdownValueE, a french dropdownValueF
		//returns bool if saved succesfully

		try
		{
			$statement = "";

			switch(true)
			{
				case strtolower($tableName) == "languagetype":
					$statement = "INSERT INTO languagetype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "gendertype":
					$statement = "INSERT INTO gendertype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "achievementleveltype":
					$statement = "INSERT INTO achievementleveltype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "securityquestion":
					$statement = "INSERT INTO securityquestion (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "picturetype":
					$statement = "INSERT INTO picturetype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "profileprivacytype":
					$statement = "INSERT INTO profileprivacytype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "storyprivacytype":
					$statement = "INSERT INTO storyprivacytype (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				case strtolower($tableName) == "actions_taken_type":
					$statement = "INSERT INTO actions_taken_type (NameE, NameF) VALUES (:NameE, :NameF)";
					break;
				default:
					return $tableName." is not a proper table name";
			}

			$parameters = array(
				":NameE" => $dropdownValueE,
				":NameF" => $dropdownValueF
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
	public function updateDropdownValue($tableName, $id, $dropdownValueE, $dropdownValueF)
	{
		//Accepts a english dropdownValueE, a french dropdownValueF
		//returns bool if saved succesfully

		try
		{
			$statement = "";

			switch(true)
			{
				case strtolower($tableName) == "languagetype":
					$statement = "UPDATE languagetype SET NameE = :NameE, NameF = :NameF  WHERE LanguageId = :Id";
					break;
				case strtolower($tableName) == "gendertype":
					$statement = "UPDATE gendertype SET NameE = :NameE, NameF = :NameF  WHERE GenderTypeId = :Id";
					break;
				case strtolower($tableName) == "achievementleveltype":
					$statement = "UPDATE achievementleveltype SET NameE = :NameE, NameF = :NameF  WHERE LevelId = :Id";
					break;
				case strtolower($tableName) == "securityquestion":
					$statement = "UPDATE securityquestion SET NameE = :NameE, NameF = :NameF  WHERE SecurityQuestionId = :Id";
					break;
				case strtolower($tableName) == "picturetype":
					$statement = "UPDATE picturetype SET NameE = :NameE, NameF = :NameF  WHERE PictureTypeId = :Id";
					break;
				case strtolower($tableName) == "profileprivacytype":
					$statement = "UPDATE profileprivacytype SET NameE = :NameE, NameF = :NameF  WHERE PrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "storyprivacytype":
					$statement = "UPDATE storyprivacytype SET NameE = :NameE, NameF = :NameF  WHERE StoryPrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "actions_taken_type":
					$statement = "UPDATE actions_taken_type SET NameE = :NameE, NameF = :NameF  WHERE ActionsTakenTypeId = :Id";
					break;
				default:
					return $tableName." is not a proper table name";
			}

			$parameters = array(
				":NameE" => $dropdownValueE,
				":NameF" => $dropdownValueF,
				":Id" => $id
				);

			return $this->fetch($statement, $parameters);
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	public function DeactiveDropdownListItem($tableName, $itemId)
	{
		try
		{
			$statement = "";

			switch(true)
			{
				case strtolower($tableName) == "languagetype":
					$statement = "UPDATE languagetype SET Active = 0 WHERE LanguageId = :Id";
					break;
				case strtolower($tableName) == "gendertype":
					$statement = "UPDATE gendertype SET Active = 0 WHERE GenderTypeId = :Id";
					break;
				case strtolower($tableName) == "achievementleveltype":
					$statement = "UPDATE achievementleveltype SET Active = 0 WHERE LevelId = :Id";
					break;
				case strtolower($tableName) == "securityquestion":
					$statement = "UPDATE securityquestion SET Active = 0 WHERE SecurityQuestionId = :Id";
					break;
				case strtolower($tableName) == "picturetype":
					$statement = "UPDATE picturetype SET Active = 0 WHERE PictureTypeId = :Id";
					break;
				case strtolower($tableName) == "profileprivacytype":
					$statement = "UPDATE profileprivacytype SET Active = 0 WHERE PrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "storyprivacytype":
					$statement = "UPDATE storyprivacytype SET Active = 0 WHERE StoryPrivacyTypeId = :Id";
					break;
				case strtolower($tableName) == "actions_taken_type":
					$statement = "UPDATE actions_taken_type SET Active = 0 WHERE ActionsTakenTypeId = :Id";
					break;
				default:
					return $tableName." is not a proper table name";
			}			

			return $this->fetch($statement, array(":Id" => $itemId));
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}
}

?>
