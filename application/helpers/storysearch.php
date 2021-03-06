<?php
/**
* 
*/
/**
* 
*/
class StorySearch extends Model
{
	public function SearchQuery($storySearch, $userID, $howMany = self::HOWMANY, $page = self::PAGE, $approved = TRUE, $active = TRUE)
	{
		$statement = $this->BuildQuery($storySearch);

		$start = $this-> getStartValue($howMany, $page);	

		$parameters = array(":storySearchTag" => "%" . $storySearch . "%", 
							":storySearch2Tag" => $storySearch, 
							":userid" => $userID, 
							":ActiveStory" => $active, 
							":ApprovedStory" => $approved, 
							":start" => $start, 
							":howmany" => $howMany
							);
		
		$story = $this->fetchIntoClass($statement, $parameters, "shared/StoryViewModel");

		return $story;
	}

	private function BuildQuery($storySearch)
	{
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    	$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
		$storySearch = str_replace($search, $replace, strtolower($storySearch));

		$statement = "SELECT
					s.StoryId, s.User_UserId AS UserId, s.StoryTitle, s.Content, s.DatePosted, 

					urs.Opinion, shp.PictureId, p.PictureId, u.Active, u.FirstName, u.LastName,

					f.Active AS FollowingUser,

					up.PictureId as UserProfilePicureId,
					
					(
						SELECT COUNT(1)
						FROM user_recommend_story 

						INNER JOIN user
						ON (user.UserId = user_recommend_story.User_UserId) AND (user.Active = TRUE) AND (user.ProfilePrivacyType_PrivacyTypeId = 1)
						
						WHERE user_recommend_story.Story_StoryId = s.StoryId
					    AND user_recommend_story.Active = TRUE
					    AND user_recommend_story.Opinion = FALSE
					) AS totalFlags,
					(
						SELECT COUNT(1)
						FROM user_recommend_story 
						WHERE user_recommend_story.Story_StoryId = s.StoryId
					    AND user_recommend_story.Active = TRUE
					    AND user_recommend_story.Opinion = TRUE
					) AS totalRecommends,

					(

					((Lower(s.StoryTitle) LIKE '%$storySearch%') * 4)";

					foreach (explode(" ", $storySearch) as $word) {
						$statement .= "+((Lower(s.StoryTitle) LIKE '% $word %') * 2)";
					}

					for( $i = 0; $i <= strlen($storySearch) + 2; $i++ ) {
						$storySearch = substr($storySearch, 0, -1);
						
						$statement .= "+((Lower(s.StoryTitle) LIKE '$storySearch%'))";
					} 

					$statement .= "
						+
					    (SELECT COUNT(1)
					        FROM story_has_tag sht
					        INNER JOIN tag t
					        ON t.TagId = sht.Tag_TagId
					        WHERE Lower(t.Tag) LIKE :storySearchTag
					        AND sht.Story_StoryId = s.StoryId
					        )
					    +
					    ((SELECT COUNT(1)
					        FROM story_has_tag sht
					        INNER JOIN tag t
					        ON t.TagId = sht.Tag_TagId
					        WHERE Lower(t.Tag) LIKE :storySearch2Tag
					        AND sht.Story_StoryId = s.StoryId
						) * 2)
					)
					    
					AS hits,

					(
						SELECT COUNT(1)
						FROM comment c
						WHERE c.Story_StoryId = s.StoryId
					    AND c.Active = TRUE
					    AND c.PublishFlag = TRUE
					) AS totalComments
					 
					FROM story s

					INNER JOIN user u
					ON (u.UserId = s.User_UserId) AND (u.Active = TRUE)
					LEFT JOIN admin_approve_story aps
					ON (aps.Story_StoryId = s.StoryId) AND (aps.Active = TRUE)

					LEFT JOIN user_recommend_story urs
					ON (urs.Story_StoryId = s.StoryId) AND (urs.User_UserId = :userid) AND (urs.Active = TRUE)

					LEFT JOIN story_has_picture shp
					ON (shp.Story_StoryId = s.StoryId) AND (shp.Active = TRUE)
					LEFT JOIN picture p
					ON (p.PictureId = shp.PictureId) AND (p.Active = TRUE)

					LEFT JOIN following f
					ON (f.User_FollowerId = s.User_UserId) AND (f.Active = TRUE)

					LEFT JOIN picture up
					ON (up.User_UserId = s.User_UserId) AND (up.Active = TRUE) AND (up.Picturetype_PictureTypeId = 1)

					WHERE StoryPrivacyType_StoryPrivacyTypeId = 1
					AND s.Active = :ActiveStory
					AND s.Published = TRUE
					AND aps.Active = TRUE
					AND aps.Approved = :ApprovedStory
					AND u.Active = TRUE
					AND u.ProfilePrivacyType_PrivacyTypeId = 1
					GROUP BY s.StoryId
					HAVING hits > 0					
					ORDER BY hits DESC
					LIMIT :start,:howmany";	

		return $statement;	
			
	}
}

?>