<?php
$fileToZip="License.txt";
$fileToZip1="CreateZipFileMac.inc.php";
$fileToZip2="CreateZipFile.inc.php";

$directoryToZip="./"; // This will zip all the file(s) in this present working directory

$outputDir="/"; //Replace "/" with the name of the desired output directory.
$zipName="test.zip";

include_once("CreateZipFile.inc.php");
$createZipFile=new CreateZipFile;

/*
// Code to Zip a single file
$createZipFile->addDirectory($outputDir);
$fileContents=file_get_contents($fileToZip);
$createZipFile->addFile($fileContents, $outputDir.$fileToZip);
*/

//Code toZip a directory and all its files/subdirectories
$createZipFile->zipDirectory($directoryToZip,$outputDir);

$rand=md5(microtime().rand(0,999999));
$zipName=$rand."_".$zipName;
$fd=fopen($zipName, "wb");
$out=fwrite($fd,$createZipFile->getZippedfile());
fclose($fd);
$createZipFile->forceDownload($zipName);
@unlink($zipName);
?>