
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File_browser</title>
    <?php
session_start();
//logout
(isset($_POST['logout']) && $_POST['logout'] == 'Logout')
?  session_destroy() && session_start() 
: null;
//login user
$note= '';
(isset($_POST['login']) && $_POST['username'] !== "" && $_POST['password'] !=='') 
? ((($_POST['username'] == "Guest" && $_POST['password'] == "iamaguest")) || ((($_POST['username'] == "Admin" && $_POST['password'] == "iamaadmin")))//loggin details
? $_SESSION['logged_in'] = true  && $_SESSION['username'] = $_POST['username']
: $note = 'Invalid username or password')
: null;

?>
</head>
<body>
    <div class="main">
    <header>
        <div class="container">
        <?php
      isset($_SESSION['logged_in']) and ($_SESSION['logged_in'] == true)
     ? print  "<h3> Welcome, " . $_SESSION['username'] . "</h3>"
     : null;
      ?>
    
     <form class='logout' method='post' action='' <?php isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true
         ? print("style = 'display: block'")
         : print("style = 'display: none'") ?>
         > 
         <input type='submit' name='logout' value='Logout'>  
        </form>  
</div>
<div class = "container2">
        <form  action = "" method = "POST" enctype = "multipart/form-data">
        <input type = "file"  id="upload" name = "image" />
        <input type = "submit" class="greenBtn"/>
    </form>
    <?php 
    if(isset($_FILES['image'])){
        $errors= array();
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        if($file_size > 41943039) {
            $errors[]='File size must be smaller than 40 MB'; 
        }
        if(empty($errors)==true) {
            move_uploaded_file($file_tmp,  modifiedPath () . $file_name);
            print "<p> Your file successfully uploaded!</p>";
        }
        else{
            print_r($errors);
        }
    }
 
    ?>
    
    </div><div class="dirLine" > <span><b> Currently in </b> <?php print modifiedPath () ?>  <b>directory</b></span> 
</div>
    </header>

        <?php  
             
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
           print  ("<section><table>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Actions</th>
              </tr>");
            showFiles();
            print "</table>";
            generateForm();
            print "</section>";
        } ?>
    
    <div class='logIn'>    
        <form class='login' action="" method="post"
        <?php isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true
         ? print("style = 'display: none'")
         : print("style = 'display: block'") ?>
      >
      <h4>You have to log in to see content</h4>
      <h6><?php echo $note; ?></h6>
        <input type="text" name="username" placeholder="Please enter username" required autofocus></br>
        <input type="password" name="password" placeholder="Please enter password" required>
        <button type="submit" name="login">Login</button>
    </form>
    </div>
<?php

function modifiedPath (){
    $createFileInDir =  $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];
    $tempFileDir = str_replace("?path=", "", $createFileInDir);
    $modifiedFileDir = str_replace("%20", " ", $tempFileDir);
    return $modifiedFileDir;
}

function showFiles() {
    isset($_GET['path']) 
    ? $path =$_GET["path"]
    : $path =  '';
    $directory =  './' . $path;
    $files_within = scandir($directory);
    $files_within = array_diff($files_within,array('.','..'));
    $files_within = array_values($files_within);
    
    foreach ($files_within as $_file) {
        $uriLink = str_replace(" ", "%20", $_file);
        $pathLink = str_replace(" ", "%20", $path);
        $hrefPath = "<a href=?path=";
        $deleteButton = "<td><form class='delete' method='POST' action=''>
        <input type='hidden' name='_fileDelete' value='$_file'>
        <input type='submit' name='delete' value='Delete'>
        </form></td>";
    
             is_dir($directory . $_file)
             ? (!isset($_GET["path"]) 
             ? print "<tr><td>Directory</td><td>" . $hrefPath . $uriLink . "/>" . $_file . "</td>$deleteButton</tr>"
             : print "<tr><td>Directory</td><td>" . $hrefPath . $pathLink . $uriLink . "/>" . $_file . "</td>$deleteButton</tr>")
             : print ("<tr><td>File</td><td>" .  $_file . "</td>$deleteButton</tr>");
        }   
    }
function generateForm() {
print "
<div class='forms'>
<div>
<form class='back' method='post' action=''> 
<input type='hidden' name='back' value='Back'>
<input type='submit' name='submit' value='Go Back'>  
</form></div><div>
<form class='create'method='post' action=''> 
<input id='field' type='text' name='newFile' placeholder='Enter file/dir name' value=''>
<input type='submit' name='create' value='Create'>  
</form></div></div>";

addDeleteBack();
}
function addDeleteBack() {

if(isset($_POST['back'])){
    header("Location:" . (dirname($_SERVER['REQUEST_URI'])). '/' );
   }
   else if (isset($_POST['create'])){
       $_newFile = $_POST['newFile'];
       if(str_contains($_newFile , ".")){
           $path= modifiedPath ();
        
        if (!file_exists($path . $_newFile )){
            if(is_dir($path)){
                // print $path . $_newFile;
               fopen($path . $_newFile, "w");
               header("Refresh:0");
            }
        }
        else{
            print "<br>" . "<p style='color:red; font-size:32px; font-weight:bold'> File with same file name is already exist!!!</p>";
        }
       }
       else{
        $path = modifiedPath ();

           if (!file_exists($path . $_newFile )){
           if(is_dir($path)){
           mkdir($path . $_newFile, 0777,TRUE);
           header("Refresh:0");
           }
        }
        else{
            print "<br>" . "<p style='color:red; font-size:32px; font-weight:bold'>Directory with same directory name is already exist!!!</p>";
        }
       }
   }
if(isset($_POST['delete'])){
    $fileToDelete = $_POST['_fileDelete'];
    $path= modifiedPath ();

    if($fileToDelete!=="index.php" && $fileToDelete!=="style.css"){
        if (is_file($path . $fileToDelete)) {
            if (!unlink($path . $fileToDelete)) {
                header("Refresh:0");
                print ("$path . $fileToDelete cannot be deleted due to an error");
            }
            else {
                print ("$path . $fileToDelete has been deleted");
                header("Refresh:2");
            }
        }
        else{
            if (!rmdir($path . $fileToDelete)) {
                print ("$path . $fileToDelete <p style='color:red; font-size:32px; font-weight:bold'>cannot be deleted due to an error: check if the directory is empty?</p>");
                header("Refresh:10");
            }
            else {
                echo ("$path . $fileToDelete has been deleted");
                header("Refresh:2");
            }
        }

    }
    else{
        print  (" <p style='color:red; font-size:32px; font-weight:bold'> Are you crazy??? Neither index.php nor style.css is forbidden to delete!!! </p>");
    }
}
}
?>
</div>
</body>
</html>






