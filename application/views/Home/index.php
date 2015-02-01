<?php 

echo gettext("welcome to the site");
echo gettext("welcometothesite");

echo "<br />";

echo gettext("testkey");
?>
    

<div class="container">

    <h1>Campfire 150 | Home</h1>
    
    <?php
        $validationResult = $viewModel->getValidationResult();
        
        include(APP_DIR . 'views/shared/displayErrors.php');
    ?>

    <div class="row">

        <div class="col-md-6"> 
            <form action="<?php echo BASE_URL; ?>account/login" method="post">
                <div class="form-group">
                    <label for="exampleInputEmail1">Email address</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> Remember Me
                    </label>
                </div>

                <button type="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
    </div>
</div>
    