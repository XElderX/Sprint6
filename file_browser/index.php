<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
table {
    font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}
table td, table th {
  border: 1px solid #ddd;
  padding: 8px;
}
table tr:nth-child(even){background-color: #f2f2f2;}
table tr:hover {background-color: #ddd;}
table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #556B2F;
  color: white;
}

h5{
    font-size: 24px;
}
span{
    font-size: 24px;
}
    </style>

</head>
<body>
<table>
<tr>
    <th>Type</th>
    <th>Name</th>
    <th>Actions</th>
  </tr>
  <?php
  
showFiles();
  ?>

<?php
echo '<span> <b> Currently in </b>' . $_SERVER['REQUEST_URI'] .' <b>dir</b> </span>';


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
 
    foreach ($files_within as $key => $_file) {
        $uriLink = str_replace(" ", "%20", $_file);
        $pathLink = str_replace(" ", "%20", $path);
        $hrefPath = "<a href=?path=";
        $deleteButton = "<form method='POST' action=''>
        <input type='hidden' name='_fileDelete' value='$_file'>
        <input type='submit' name='delete' value='Delete'>
        </form>";
    
             is_dir($directory . $_file)
             ? (!isset($_GET["path"]) 
             ? print "<tr><td>" . "Directory" . "</td>" . "<td>" . $hrefPath . $uriLink . "/>" . $_file . "</td><td>$deleteButton</td></tr>"
             : print "<tr><td>Directory</td><td>" . $hrefPath . $pathLink . $uriLink . "/>" . $_file . "</td><td>$deleteButton</td></tr>")
             : print ("<tr><td>File</td><td>" .  $_file . "</td><td>$deleteButton</td></tr>");
        } 
}
?>
</table>
<form method="post" action=""> 
<input type="hidden" name="back" value="Back">
<input type="submit" name="submit" value="Go Back">  
</form>
<form method="post" action=""> 
<input type="text" name="newFile" placeholder="Enter file/dir name" value="">
<input type="submit" name="create" value="Submit">  
</form>

<?php
if(isset($_POST['back'])){
    header("Location:" . (dirname($_SERVER['REQUEST_URI'])). '/' );
   }
   else if (isset($_POST['create'])){
       $_newFile = $_POST['newFile'];
       if(str_contains($_newFile , ".")){
           $path= modifiedPath ();
        
        if (!file_exists($path . $_newFile )){
            if(is_dir($path)){
                print $path . $_newFile;
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
?>
<?php
if(isset($_POST['delete'])){
    $fileToDelete = $_POST['_fileDelete'];
    $path= modifiedPath ();

    if($fileToDelete!=="index.php"){
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
        print  (" <p style='color:red; font-size:32px; font-weight:bold'> Are you crazy??? index.php is forbidden to delete!!! </p>");
    }

}

?>
</body>
</html>







