<?php
session_start();
//logout
(isset($_POST['logout']) && $_POST['logout'] == 'Logout')
    ?  session_destroy() && session_start()
    : null;
//login user
$note = '';
(isset($_POST['login']) && $_POST['username'] !== "" && $_POST['password'] !== '')
    ? ((($_POST['username'] == "Guest" && $_POST['password'] == "iamaguest")) || ((($_POST['username'] == "Admin" && $_POST['password'] == "iamaadmin"))) //loggin details
        ? $_SESSION['logged_in'] = true  && $_SESSION['username'] = $_POST['username']
        : $note = 'Invalid username or password')
    : null;
// file download logic
if (isset($_POST['download'])) {
    $file = modifiedPath() . $_POST['_download'];
    $fileToDownloadEscaped = str_replace("&nbsp;", " ", htmlentities($file, 0, 'utf-8'));
    ob_clean();
    ob_start();
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . basename($fileToDownloadEscaped));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fileToDownloadEscaped)); 
    ob_end_flush();
    readfile($fileToDownloadEscaped);
    exit;
}
//upload
if (isset($_FILES['image'])) {

    $errors = array();
    $file_name = $_FILES['image']['name'];
    $file_size = $_FILES['image']['size'];
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_type = $_FILES['image']['type'];
    if ($file_size > 41943039) {
        $errors[] = 'File size must be smaller than 40 MB';
    }
    if (empty($errors) == true) {
        move_uploaded_file($file_tmp,  modifiedPath() . $file_name);
        $notification = "<p class='eventNote'> Your file successfully uploaded!</p>";
        reload();
    } else {
        $notification = "<p class='eventNote'>" . $errors . "</p>";
        reload();
    }
}
if ((isset($_POST['create'])) || (isset($_POST['delete']))) {
    reload();
}


function addDeleteBack()
{
    if (isset($_POST['back'])) {
        (($_SERVER['REQUEST_URI'] == "/sprint6/file_browser/") || ($_SERVER['REQUEST_URI'] == "/file_browser/"))
            ? print "<p class='eventNote'> Nowhere to go!!! </p>"
            : header("Location:" . (dirname($_SERVER['REQUEST_URI'])) . '/');
    } else if (isset($_POST['create'])) {
        $_newFile = $_POST['newFile'];
        if (($_newFile !== "") && ($_newFile[0] !== " ") && ($_newFile[0] !== ".")) {
            if (str_contains($_newFile, ".")) {
                $path = modifiedPath();
                (!file_exists($path . $_newFile))
                    ? ((is_dir($path))
                        ? fopen($path . $_newFile, "w") and print "<p class='eventNote'>File with the name <span style = 'color:yellow'> $_newFile </span> has been created </p>"
                        : null)
                    : print "<p class='eventNote' style='color:red;  font-weight:bold'> File with that file name is already exist!!!</p>";
            } else {
                $path = modifiedPath();
                (!file_exists($path . $_newFile))
                    ? ((is_dir($path))
                        ? mkdir($path . $_newFile, 0777, TRUE) and print "<p class='eventNote'>directory with the name <span style = 'color:yellow'> $_newFile </span> has been created </p>"
                        : null)
                    : print "<p class='eventNote' style='color:red; font-weight:bold'>Directory with that directory name is already exist!!!</p>";
            }
        }
    }
    if (isset($_POST['delete'])) {
        $fileToDelete = $_POST['_fileDelete'];
        $path = modifiedPath();
        if (file_exists($path . $fileToDelete)) {
            ($fileToDelete !== "index.php" && $fileToDelete !== "style.css")
                ? ((is_file($path . $fileToDelete))
                    ? ((!unlink($path . $fileToDelete))
                        ? print(" <p class='eventNote'> style='color:red; font-weight:bold'> <span style = 'color:yellow'> $fileToDelete </span> cannot be deleted due to an error </p>")
                        : print("<p class='eventNote'> $fileToDelete has been deleted </p>"))
                    : ((isDirEmpty(($path . $fileToDelete)))
                        ? rmdir($path . $fileToDelete) and print("<p class='eventNote'>  <span style = 'color:yellow'> $fileToDelete </span> has been deleted </p>")
                        : print("<p class='eventNote' style='color:red; font-weight:bold'> <span style = 'color:yellow'> $fileToDelete </span> cannot be deleted due to an error: check if the directory is empty?</p>")))
                : print(" <p class='eventNote' style='color:red; font-weight:bold'> Oops!!! <span style = 'color:yellow'> $fileToDelete </span> is forbidden to delete!!! </p>");
        }
    }
}
function reload()
{
    header('refresh:2') and header('location: ' . $_SERVER['REQUEST_URI']) and die;
}
function modifiedPath()
{
    $createFileInDir =  $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];
    $tempFileDir = str_replace("?path=", "", $createFileInDir);
    $modifiedFileDir = str_replace("%20", " ", $tempFileDir);
    return $modifiedFileDir;
}
function isDirEmpty($dirname)
{
    if (!is_dir($dirname)) return false;
    foreach (scandir($dirname) as $file) {
        if (!in_array($file, array('.', '..', '.svn', '.git')))
            return false;
    }
    return true;
}

function showFiles()
{

    isset($_GET['path'])
        ? $path = $_GET["path"]
        : $path =  '';
    $directory =  './' . $path;
    $files_within = scandir($directory);
    $files_within = array_diff($files_within, array('.', '..'));
    $files_within = array_values($files_within);

    foreach ($files_within as $_file) {

        $uriLink = str_replace(" ", "%20", $_file);
        $pathLink = str_replace(" ", "%20", $path);
        $hrefPath = "<a href=?path=";
        $deleteButton = "<form class='delete' method='POST' action=''>
        <input type='hidden' name='_fileDelete' value='$_file'>
        <input type='submit' name='delete' value='Delete'>
        </form>";
        $downloadButton = "<form class='download' action='' method='POST'>
        <input type='hidden' name='_download' value='$_file'/>
        <input type='submit' name='download' value='Download'>
        </form>";

        is_dir($directory . $_file)
            ? (!isset($_GET["path"])
                ? print "<tr><td >Directory</td><td>" . $hrefPath . $uriLink . "/>" . $_file . "</td><td>$deleteButton</td></tr>"
                : print "<tr><td >Directory</td><td>" . $hrefPath . $pathLink . $uriLink . "/>" . $_file . "</td><td>$deleteButton</td></tr>")
            : print("<tr><td >File</td><td>" .  $_file . "</td><td><div class='action_container'>" . $deleteButton .  $downloadButton  . "</div></td></tr>");
    }
}
function generateForm()
{

    print "<div class='forms'><div><form class='back' method='post' action=''> 
        <input type='hidden' name='back' value='Back'>
        <input type='submit' name='submit' value='Go Back'>  
        </form></div><div>
        <form class='create'method='post' action=''> 
        <input id='field' type='text' name='newFile' placeholder='Enter file/dir name' value=''>
        <input type='submit' name='create' value='Create'>  
        </form></div></div>";

    addDeleteBack();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File_browser</title>
</head>

<body>
    <?php isset($_SESSION['logged_in']) and ($_SESSION['logged_in'] == true)
        ? print "<div class='main'><header><div class='container'><h3> Welcome, " . $_SESSION['username'] . "</h3>"
        : null;
    ?>
    <form class='logout' method='post' action='' <?php isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true
                                                        ? print("style = 'display: block'")
                                                        : print("style = 'display: none'") ?>>
        <input type='submit' name='logout' value='Logout'>
    </form>
    </div>
    <div class="container2">
        <?php isset($_SESSION['logged_in']) and ($_SESSION['logged_in'] == true)
            ? print "
    <form  action = '' method = 'POST' enctype = 'multipart/form-data'>
    <input type = 'file'  id='upload' name = 'image' />
    <input type = 'submit' class='greenBtn'/>
    </form>"
            : null;
        print '</header>';
        ?>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
            print "
    <div class='dirLine' > <span><b> Currently in </b>" . substr_replace(modifiedPath(), '', -1) . " " . "<b>directory</b></span></div>";
            print("
    <section>
    <table><tr><th>Type</th><th>Name</th><th>Actions</th></tr></table><div class='tableContainer'><table>");
            showFiles();
            print "
    </table></div>";
            generateForm();
            (isset($notification))
            ? print $notification
            : null;
            print "
    </section>";
        }
        ?>
        <div class='logIn'>
            <form class='login' action="" method="post" <?php isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true
                                                            ? print("style = 'display: none'")
                                                            : print("style = 'display: block'") ?>>
                <h4>You have to log in to see content</h4>
                <h6><?php echo $note; ?></h6>
                <input type="text" name="username" placeholder="Please enter username" required autofocus></br>
                <input type="password" name="password" placeholder="Please enter password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
</body>

</html>