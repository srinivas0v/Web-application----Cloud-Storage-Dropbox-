
<html>
<head>
	<title> DropBox App </title>
	</style>

<link rel="stylesheet" href="dropboxStyle.css"/>
<script type='text/javascript'>
function clean(){
document.getElementById('list').innerHTML = " "
}
 </script>
</head>
<body>
	<div class="header">
  <h1>Dropbox Application</h1>
</div>
<?php
/**
 * DropPHP Demo
 *
 * http://fabi.me/en/php-projects/dropphp-dropbox-api-client/
 *
 * @author     srinivas venkatesh<srinivas0v1993@gmail.com>
 * @copyright  srinivas venkatesh 2017
 * @version    1.1
 * @license    See license.txt
 *
 */

global $dropbox;
require_once 'demo-lib.php';
demo_init(); // this just enables nicer output

set_time_limit( 0 );

require_once 'DropboxClient.php';
$dropbox = new DropboxClient( array(
	'app_key' => "m2l95sadu55gbmr",     
	'app_secret' => "4bjqoi6s4i1qvvj", 
	'app_full_access' => false,
) );
/**
 * Dropbox will redirect the user here
 * @var string $return_url
 */
$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";

// first, try to load existing access token
$bearer_token = demo_token_load( "bearer" );

if ( $bearer_token ) {
	$dropbox->SetBearerToken( $bearer_token );
	//echo "loaded bearer token: " . json_encode( $bearer_token, JSON_PRETTY_PRINT ) . "\n";
} elseif ( ! empty( $_GET['auth_redirect'] ) ) // are we coming from dropbox's auth page?
{
	// get & store bearer token
	$bearer_token = $dropbox->GetBearerToken( null, $return_url );
	demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
	// redirect user to Dropbox auth page
	$auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
	die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}
//******************************************************************************************************************

function upload(){
	global $dropbox;
	$upload_dir = "C:/xampp/htdocs/project8/";
	$upload_img = $upload_dir . basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	$imgType = pathinfo($upload_img,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
    	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    	if($check !== false) {
        	// echo "File is an image - " . $check["mime"] . ".";
        	$uploadOk = 1;
    	} else {
        	echo "File is not an image.";
        	$uploadOk = 0;
    	}
	}
	if ($_FILES["fileToUpload"]["size"] > 500000) {
    	echo "Sorry, your file is too large.";
    	$uploadOk = 0;
	}
// Allow certain file formats
	if($imgType != "jpg" && $imgType != "png" && $imgType != "jpeg"&& $imgType != "gif" ) {
    	echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    	$uploadOk = 0;
	}
// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
    	echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
	} else {
    	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $upload_img)) {
    		// echo $upload_img;
    		// echo "problem";
    		$dropbox->UploadFile($upload_img);
			// echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    	} else {
        	echo "Sorry, there was an error uploading your file.";
    	}
	}
	
}


function download(){
	global $dropbox;
	$dir = "C:/xampp/htdocs/";
    //$target_file = $dir . basename($_GET['download']);
	$dropbox->DownloadFile($_GET['display_download_path']);
	//$msg = "downloaded ".$_GET['display_download'];
	//echo "downloaded ".$_GET['display_download'];
}


function delete()
{
    global $dropbox;
    $dropbox->Delete($_GET['delete']);
	//header('Location: album.php');
	//$msg = "deleted ".$_GET['delete'];
	
}

?>

<form action="album.php" method="post" enctype="multipart/form-data">
	<h2>Upload File to Dropbox</h2>
    <input type="file" name="fileToUpload" id="fileToUpload" >
    <input type="submit" value="Upload Image" name="submit">
</form>
<h2>List of files in dropbox</h2><br>
<div id='list'><ul>
<?php
function display()
{
global $dropbox;
$files = $dropbox->GetFiles( "", false );
// print_r($files);
// echo "<h2>List of files in dropbox</h2><br>";
 //echo "<script type='text/javascript'>clean() </script>";
$values = " ";
foreach($files as $key => $value)
{
	
	$img_name =$value->name;
	$img_link = $dropbox->GetLink( $value ); 
	$img_data = base64_encode( $dropbox->GetThumbnail( $value->path ) );
	$img_path = $value->path;

	$values = $values . "<li>Name: ".$img_name."</li><br>";

	$values = $values . "<li><a href='album.php?display_download_link=".$img_link."&display_download_name=".$img_name."&display_download_path=".$img_path."'>". $img_link ."</a></li><br><br>";
	$values = $values . "<button class='button '> <a href='album.php?delete=".$img_path."'>Delete</a></button><br><br><br><br><br><br>";
	
	// echo "<li>Name: ".$img_name."</li><br>";
	// echo "<li><a href='album.php?display_download_link=".$img_link."&display_download_name=".$img_name."&display_download_path=".$img_path."'>" . $img_link ."</a></li><br><br>"; 

	// echo "<button class='button '> <a href='album.php?delete=".$img_path."'>Delete</a></button><br><br><br><br><br><br>"; 
	
}
echo $values;
//print_r($values);
// echo "<script type='text/javascript'>document.getElementById('list').innerHTML = " . $values . "}";
}
display();
?>
</ul></div>
<?php
if(isset($_POST["submit"])) {
	upload();
	echo "<script type='text/javascript'>clean() </script>";
	display();
 }

if (isset($_GET['display_download_link'])) {
		if (isset($_GET['display_download_name'])) {
			$link = $_GET['display_download_link'];
			$name = $_GET['display_download_name'];	
			$path = $_GET['display_download_path'];	
				//echo $path;
			$fpath = "file:///".getcwd() . $path;
			//echo $fpath;
			$fullpath = str_replace('\\', '/', $fpath);
			//echo $fullpath;

				//echo dirname(_FILE_);
			echo "<h2>Image:</h2>";
			$image_data = base64_encode( $dropbox->GetThumbnail( $_GET['display_download_path'],$size = 'l' ) );
			

			echo "<img src=\"data:image/jpeg;base64,$image_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" />";
  			
		}
  	}

 if (isset($_GET['delete'])) {
  	delete();
  	echo "deleted ".$_GET['delete']."<br>";
  	echo "<script type='text/javascript'>clean() </script>";
  	display();
  	}

?>

<?php
if(!empty($msg)){
	echo "<div>"
	.$msg.
	"</div><br>";
}
?>
</body>
</html>