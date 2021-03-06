<?php

    //You have access to the shared/StoryViewModel.php
    
    //You can access everything from this variable:
    //uncomment to view structure in browser
    //debugit($storyViewModel);

?>

<div class="container" style="padding-top: 50px; padding-bottom: 50px;">
    <div class="row">

    	<h1>Edit Story</h1>

        <div class="panel panel-default">
            <div class="panel-heading" style="font-size:1.4em; background-color:#3498db; color:white;">
                <?php echo gettext("Edit Stay"); ?>
            </div>
                <div class="panel-body">
                    <div class="col-md-6"> 

                        <form action="<?php echo BASE_URL; ?>story/edit" method="post" id="loginForm">

                            <?php include(APP_DIR . 'views/shared/messages.php'); ?>

                            <div class="form-group">
                                <label for="StoryTitle"><?php echo gettext("Title"); ?></label>
                                <input type="text" class="form-control" id="StoryTitle" name="StoryTitle" placeholder="<?php echo gettext("Enter Title"); ?>" value="<?php echo $storyViewModel->StoryTitle; ?>">
                            </div>
                            <div class="form-group">
                                <label for="StoryPrivacyType_StoryPrivacyTypeId"><?php echo gettext("Story Privacy"); ?></label>
                                <select class="form-control" name="StoryPrivacyType_StoryPrivacyTypeId">
                                    <?php 
                                        foreach ($privacyDropdownValues as $dropdownValue) {
                                            echo "<option " . ($storyViewModel->StoryPrivacyType_StoryPrivacyTypeId == $dropdownValue->Value ? 'selected=selected' : "") . " value='" . $dropdownValue->Value . "'>"; 
                                                echo $dropdownValue->Name;
                                            echo "</option>";
                                        } 
                                    ?>
                                </select>
                            </div>
							
							<div class="form-group">
                                <label for="Content"><?php echo gettext("Content"); ?></label>
                                <textarea class="form-control tinyMCE" id="Content" name="Content"><?php echo $storyViewModel->Content; ?></textarea>
                            </div>                              

                            <button type="submit" class="btn btn-default"><?php echo gettext("Submit"); ?></button>
                        </form>

                    </div>
            </div>
        </div>
    </div>
</div>