<!DOCTYPE html>
<html>
    <head>
        <?php
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
			require("../app/db.php");
            $dir = "";
            $username = "";
            $user_id = -1;
            $serverMSG = "";
			$createUser = false;

            if ($_SERVER["REQUEST_METHOD"] === 'POST') {
				if (isset($_POST["register"])) {
					$password =  password_hash($_POST["password"], PASSWORD_BCRYPT);
                    $user_data = '{"directories":[], "settings":{}}';
                    $user = mysqli_query($connection,"select * from `Users` where username = '" . $_POST["username"] . "';");
                    if (mysqli_num_rows($user) == 0) {
						$username = $_POST["username"];
                        mysqli_query($connection,"insert into `Users` (username,password,email,user_data) values ('" .
                                     $_POST["username"] . "','" . $password . "','" . $_POST["email"] . "','" . $user_data . "');");
                        $createUser = true;

                        if ($createUser) {
                            mkdir(getcwd()."/".strtolower($_POST["username"]), 0775);
                            $text = "<h1>Welcome to Data Hexagon</h1>";
                            touch(getcwd()."/".strtolower($_POST["username"]) . "/Welcome.htm");
                            $textFile = fopen(getcwd()."/".strtolower($_POST["username"]) . "/Welcome.htm", "w");
                            fwrite($textFile, $text);
                            fclose($textFile);
                        }
                    }  else {    // else the user already exists:
                       $serverMSG = "Unfortunately the username entered is not available; please try another.";
                    }
				}

				if (isset($_POST["login"])) {
                    $user = mysqli_query($connection,"select * from `Users` where username = '" . $_POST["username"] . "';");
                    $user = mysqli_fetch_array($user);
                    if (password_verify($_POST["password"], $user["password"])) {
                        $username = $_POST["username"];
                        $password = $_POST["password"];
                        $user_id = $user["id"];
                    } else {
                        $serverMSG = "Incorrect Username Or Password.";
                    }
				}

            } else {
				if (count(explode("/", $_SERVER['REQUEST_URI'])) > 3) {
					$dir = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI'])-1);
					$shares = mysqli_query($connection, "select * from `Shares` where directory = '" . $dir . "';");
					if (mysqli_num_rows($shares) > 0) {
						$share = mysqli_fetch_array($shares);
						if ($share["public"] == 1) {
							$username = "anonymous";
							$password = "anonymous";
						} else {
							header('Location: /');
						}
					} else {
						header('Location: /');
					}

				}
			}
			mysqli_close($connection);
        ?>
        <title>Home | DataHexagon</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name='theme-color' content='rgb(40, 40, 40)'>
		<meta name="description" content="A web based desktop environment, file storage and sharing service." >
      	<meta name="msapplication-TileColor" content="#282828">
        <meta name="msapplication-TileImage" content="/app/data/gradient-shadow-hexagon-white-192.png">
		<meta name="application-name" content="DataHexagon">
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
	    <meta content="utf-8" http-equiv="encoding">
        <meta property="og:image" content="/app/data/gradient-shadow-hexagon-white-192.png" / >
        <meta property="og:image:width" content="192" />
        <meta property="og:image:height" content="192" />
        <link rel="icon" sizes="192x192" href="/app/data/gradient-shadow-hexagon-white-192.png">
        <link rel="stylesheet" href="/app/dist/css/app.css" type="text/css" >
        <link rel="stylesheet" href="/app/lib/codemirror/codemirror.css" type="text/css" >
        <link rel="manifest" href="/manifest.json">
    </head>
    <body>
        <header>

        </header>
        <?php if ($username != "") { ?>
        <form class='fileManagement' action="?" method="POST" enctype="multipart/form-data">
            <aside class="root">
            	<nav>
                	<a class="sh" title="Launch Applets"></a>
                	<a class="up" title="Up One Level"></a>
                	<a class="sort"  title="Change Settings"></a>
                	<a class="create" title="Create and Upload"></a>
<!--                    <a class="view" href="javascript:void(0);" onclick="app.showMenu('launcher');" title="Launch Applets"></a>-->
                </nav>
            </aside>
            <aside class="startMenu" style="display: none;">
            	<nav>
            		<ul>
            		    <li class="search">Search</li>
                        <li class="userProfile">Sign In</li>
                        <li class="create">Create</li>
                        <li class="upload">Upload</li>
                        <li class="messaging">Messaging</li>
                        <li class="notifications">Notifications</li>
                    </ul>
				</nav>
            </aside>
             <aside class="creation" style="display: none;">
            	<nav>
            		<a class="upload" href="javascript:void(0);" title="Upload File"><span>Upload</span>
                   		<div class="uploadInput">
                    		<input class="upload" type="file" multiple="multiple" onclick="app.showMenu('none')" onchange="app.startUpload(this)">
                    	</div>
                   </a>
                    <a class="touch" href="javascript:void(0);" onclick="app.showMenu('file');" title="New File"><span>New File</span></a>
                    <a class="mkdir" href="javascript:void(0);" onclick="app.openPane('mkdir', 'New Folder', {});" title="New Folder"><span>New Folder</span></a>
				</nav>
            </aside>
            <aside class="sort" style="display: none;">
            	<nav>
            		<a class="logout" title="Log Out" href="/"></a>
            		<a class="type" title="Sort by File Type" onclick="app.changeSortMode('type');"  href="javascript:void(0);"></a>
            		<a class="name" title="Sort Alphabetically" onclick="app.changeSortMode('name');"  href="javascript:void(0);"></a>
            		<a class="date" title="Sort by Creation Date" onclick="app.changeSortMode('date');"  href="javascript:void(0);"></a>
            		<a class="full3d" title="Change Thumbnail Size" onclick="app.toggleThumbSize();"  href="javascript:void(0);"></a>
            		<a class="grid" title="2D Grid View" onclick="app.switchView('grid');"  href="javascript:void(0);"></a>
            		<a class="theme" title="Toggle UI Theme" onclick="app.toggleTheme();"  href="javascript:void(0);"></a>
            		<a class="hexagon" title="Virtual Reality Mode (Coming Soon)" onclick="app.switchView('hexagon');"  href="javascript:void(0);"></a>
				</nav>
            </aside>
            <aside class="file" style="display: none;">
            	<nav>
            		<a class="text" onclick="app.openPane('touch', 'New Document.tx', {});" title="New Document" href="javascript:void(0);"></a>
            		<a class="image" onclick="app.openPane('touch', 'New Image.pic', {});" title="New Image"  href="javascript:void(0);"></a>
            		<a class="object" onclick="app.openPane('touch', 'New 3D Model.json', {});" title="New Model"  href="javascript:void(0);"></a>
				</nav>
            </aside>
            <section class="content" id="fileview">

            </section>
            <section class="content" id="paneview">

            </section>
            <section class="launcher" style="display: none;">

            </section>
            <input type="hidden" name="login" value="1" />
            <input name="username" type="hidden" value="<?php echo $username; ?>" />
            <input name="password" type='hidden' value="<?php echo $password; ?>" />
        </form>
        <div class="lightbox" style="display: none;" onclick="app.showMenu('none')"></div>
        <?php } else { ?>
        <div class="formContainer">
            <h1>Data Hexagon</h1>
            <h2 class="title">Log in to upload, create and edit your documents or files.</h2>
    	<?php   if (strlen($serverMSG) > 0) {
                echo "<h2 class='server'>$serverMSG</h2>";
            }
		?>
            <input type='button' value='Sign In' id='show-signin'>
            <input type='button' value='Register' id='show-register'>
             <form action = "?" method="POST" id='signin-form' class='hidden'>
              	<input type="hidden" name="login" value="1" />
                 <div class='auth_field'>
                     <span class='label'>User Name:</span><input name="username" autofocus type="text" />
                 </div>
                 <div class='auth_field'>
                     <span class='label'>Password:</span><input name="password" type='password' value="" />
                     <input type='submit' value='Go'/>
                 </div>
			</form>
            <form action = "?" method="POST" id='register-form' class='hidden'>
               <input type="hidden" name="register" value="1" />
                <div class='auth_field'>
                    <span class='label'>Email:</span> <input name="email" type='text' value="" />
                </div>
                <div class='auth_field'>
                    <span class='label'>User Name:</span> <input name="username" type="text" />
                </div>
                <div class='auth_field'>
                    <span class='label'>Password:</span> <input name="password" type='password' value="" />
                    <input type='submit' value='Go'/>
                </div>
            </form>
		</div>
        <?php } ?>
        <script src="/app/lib/three.js"></script>
<!--
        <script src="/app/lib/three.js-extensions/sources/RenderManager.js"></script>
        <script src="/app/lib/three.js-extensions/sources/Shaders.js"></script>
        <script src="/app/lib/three.js-extensions/sources/Utils.js"></script>
-->
        <script src="/app/lib/socket.io/socket.io.js"></script>
        <script src="/app/dist/js/ui.js"></script>
        <script src="/app/dist/js/app.js"></script>
        <script src="/app/dist/js/main.js"></script>
        <script src="/app/dist/js/world.js"></script>
        <script src="/app/dist/js/applet.js"></script>
		<script src="/app/lib/codemirror/codemirror.js" ></script>
		<script src="/app/lib/codemirror/css.js" ></script>
		<script src="/app/lib/codemirror/javascript.js" ></script>
		<script src="/app/lib/codemirror/markdown.js" ></script>
		<script src="/app/lib/codemirror/xml.js" ></script>
		<script src="/app/lib/codemirror/clike.js" ></script>
         <?php if ($username != "") { ?>
        <script>
            app.user.name = "<?php echo $username; ?>";
            app.user.id = <?php echo $user_id; ?>;
            document.addEventListener("DOMContentLoaded", function () {
            <?php if ($username == "anonymous") { ?>
                    openFolder("<?php echo $dir; ?>");
            <?php } else { ?>
                    openFolder("/"+app.user.name.toLowerCase());
            <?php } ?>
			});
        </script>
		<?php } ?>
    </body>
</html>
